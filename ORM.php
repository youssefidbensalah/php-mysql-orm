<?php

require_once 'Database.php';
require_once 'ORMInterface.php';
require_once 'NotFoundException.php';


class ORM implements ORMInterface {
    private $db;
    private $tableName;
    private $fields;
    private $updatedFields;
    private $autoIncrementField;
    protected $originalData = array();

    public function __construct(Database $db, $className) {
        $this->db = $db;
        $this->tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
        $this->fields = $this->getFields($className);
        // $this->autoIncrementField = $this->getAutoIncrementField($className);
        $this->autoIncrementField = 'id';
        $this->fields[$this->autoIncrementField] = 'INT AUTO_INCREMENT PRIMARY KEY';

        $this->db->createTable($this->tableName, $this->fields);
    }

    public function create($object) {
        $fields = array();
        $values = array();
        foreach ($this->fields as $field => $type) {
            if ($field != $this->autoIncrementField) {
                $fields[] = $field;
                $values[] = "'" . $object->$field . "'";
            }
        }
        $sql = "INSERT INTO $this->tableName (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
        $this->db->query($sql);
        // $object->$this->autoIncrementField = $this->db->connection->insert_id;
        $object->{$this->autoIncrementField} = $this->db->connection->insert_id;
    }

    public function update2($object) {
        $sets = array();
        if($this->updatedFields) {
            foreach ($this->updatedFields as $field => $type) {
                $sets[] = "$field = '" . $object->$field . "'";
            }
        }

        if (!empty($sets)) { // Check if there are fields to update
            $autoIncrementValue = $object->{$this->autoIncrementField};
            $sql = "UPDATE $this->tableName SET " . implode(', ', $sets) . " WHERE $this->autoIncrementField = ?";
            $this->db->query($sql, array($autoIncrementValue));
            $this->updatedFields = array();
        } else {
            // Handle the case when there are no fields to update
            // You can either skip the update or handle it differently based on your application requirements
            echo "No fields to update." . "<br>";
        }
    }

    public function update($object)
    {
        $sets = array();
        foreach ($this->fields as $field => $type) {
            if ($field != $this->autoIncrementField) {
                if ($object->$field != $this->originalData[$field]) {
                    $sets[] = "$field = ?";
                    $this->updatedFields[$field] = $object->$field;
                }
            }
        }
        
        if (!empty($sets)) {
            $autoIncrementValue = $object->{$this->autoIncrementField};
            $sql = "UPDATE $this->tableName SET " . implode(', ', $sets) . " WHERE $this->autoIncrementField = ?";
            
            $params = array_values($this->updatedFields);
            $params[] = $autoIncrementValue;
            
            $this->db->query($sql, $params);
            $this->updatedFields = array();
        } else {
            echo "No fields to update.";
        }
    }

    public function find($id) {
        // $result = $this->db->query("SELECT * FROM $this->tableName WHERE $this->autoIncrementField = $id");
        $sql = "SELECT * FROM $this->tableName WHERE $this->autoIncrementField = ?";
        $result = $this->db->query($sql, array($id));
        if ($result->num_rows == 0) {
            // return null;
            throw new NotFoundException("Object with ID $id not found in table $this->tableName <br>");
        }
        $row = $result->fetch_assoc();
        $className = get_class($this);
        return $this->createObject($row, $className);
    }

    public function findAll() {
        $result = $this->db->query("SELECT * FROM $this->tableName");
        $objects = array();
        $className = get_class($this);
        while ($row = $result->fetch_assoc()) {
            $objects[] = $this->createObject($row,$className);
        }
        return $objects;
    }

    public function delete($id) {
        // Check if the object exists before attempting to delete
        try {
            $this->find($id);
        } catch (NotFoundException $e) {
            return "Object with ID $id not found in table $this->tableName";
        }

        $sql = "DELETE FROM $this->tableName WHERE $this->autoIncrementField = ?";
        $affectedRows = $this->db->query($sql, array($id));

        if ($affectedRows > 0) {
            return "Object deleted";
        } else {
            return "Object not deleted";
        }




        // $sql = "DELETE FROM $this->tableName WHERE $this->autoIncrementField = $id";
        // $is_deleted=$this->db->query($sql);
        // return $is_deleted?"Object deleted":"Object not deleted";
    }

    public function deleteAll() {
        $sql = "DELETE FROM $this->tableName";
        $this->db->query($sql);
    }

    public function updateSchema() {
        $this->dropRemovedColumns();
        $this->addNewColumns();
        $this->updateColumnTypes();
        $this->updateColumnNames();
    }

    private function dropRemovedColumns() {
        $currentColumns = array_keys($this->fields);
        $existingColumns = array_keys($this->getExistingColumns());

        $removedColumns = array_diff($existingColumns, $currentColumns);

        foreach ($removedColumns as $column) {
            $this->db->dropColumn($this->tableName, $column);
            unset($this->fields[$column]);
        }
    }

    private function addNewColumns() {
        $existingColumns = $this->getExistingColumns();
        $newColumns = array_diff_key($this->fields, $existingColumns);

        foreach ($newColumns as $column => $type) {
            $this->db->alterTable($this->tableName, array($column => $type));
        }
    }

    private function updateColumnTypes() {
        $existingColumns = $this->getExistingColumns();
        foreach ($existingColumns as $column => $type) {
            if (isset($this->fields[$column])) {
                $newType = $this->fields[$column];
                if ($type != $newType) {
                    $this->db->modifyColumn($this->tableName, $column, $newType);
                }
            }
        }
    }

    private function updateColumnNames() {
        $existingColumns = $this->getExistingColumns();
        foreach ($existingColumns as $column => $type) {
            if (isset($this->fields[$column])) {
                $newColumn = $column;
                if (array_search($column, array_keys($this->fields)) !== $column) {
                    $newColumn = array_search($column, array_keys($this->fields));
                    $this->db->renameColumn($this->tableName, $column, $newColumn);
                }
            }
        }
    }

    private function getFields($className) {
        $reflection = new ReflectionClass($className);
        $properties = $reflection->getProperties();
        $fields = array();
        foreach ($properties as $property) {
            $name = $property->getName();
            $type = $this->getType($property);
            $fields[$name] = $this->getSQLType($type);
        }
        return $fields;
    }

    private function getAutoIncrementField($className) {
        $reflection = new ReflectionClass($className);
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $name = $property->getName();
            $type = $this->getType($property);
            if ($type == 'integer' && $name == 'id') {
                return $name;
            }
        }
        return null;
    }

    private function createObject($row, $className) {
        $object = new $className($this->db,$className);
        foreach ($row as $field => $value) {
            $object->$field = $value;
        }
        // Set originalData with the initial values
        $this->originalData = get_object_vars($object);
        return $object;
    }

    private function getExistingColumns() {
        $result = $this->db->query("SHOW COLUMNS FROM {$this->tableName}");
        $columns = array();
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row['Type'];
        }
        return $columns;
    }

    private function getType(ReflectionProperty $property) {
        $docComment = $property->getDocComment();
        preg_match('/@var\s+([a-zA-Z]+)/', $docComment, $matches);
        
        // Check if the regex match was successful and the array key exists
        if (isset($matches[1])) {
            return $matches[1];
        } else {
            // Handle the case where the regex match did not capture the type
            return 'unknown'; // or any default value or error handling you prefer
        }
    }
    

    private function getSQLType($type) {
        switch ($type) {
            case 'string':
                return 'VARCHAR(255)';
            case 'integer':
                return 'INT AUTO_INCREMENT';
            case 'float':
                return 'DECIMAL(10, 2)';
            default:
                return 'TEXT';
        }
    }

    public function __set($name, $value) {
        if (array_key_exists($name, $this->fields)) {
            $this->updatedFields[$name] = $this->fields[$name];
        }
        $this->$name = $value;
    }

    public function verifyAutoIncrementField() {
        $tableStructure = $this->db->getTableStructure($this->tableName);
        
        // Check if the auto-increment field is correctly defined
        if (strpos($tableStructure, "PRIMARY KEY (`{$this->autoIncrementField}`)") !== false
            && strpos($tableStructure, "`{$this->autoIncrementField}` int(11) NOT NULL AUTO_INCREMENT") !== false) {
            echo "Auto-increment field is correctly set in the database table.";
        } else {
            echo "Auto-increment field is not correctly set in the database table.";
        }
    }
}
