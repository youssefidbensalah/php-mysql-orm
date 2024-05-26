<?php

require_once 'vendor/autoload.php';


class Database {
    public $connection;

    public function __construct() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $dbHost = $_ENV['DB_HOST'];
        $dbName = $_ENV['DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        $dbPass = $_ENV['DB_PASS'];

        $this->connection = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    

    public function query($sql, $params = null) {
        $stmt = $this->connection->prepare($sql);
        if ($params !== null) {
            $types = str_repeat('s', count($params)); // Assuming all params are strings for simplicity
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        // Check if the query is a SELECT statement
        if (stripos($sql, 'SELECT') === 0) {
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        } else {
            // For non-select statements, return the number of affected rows
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows;
        }
    }
    

    public function createTable($tableName, $fields) {
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (";
        foreach ($fields as $field => $type) {
            $sql .= "$field $type, ";
        }
        $sql = rtrim($sql, ", ") . ")";
        // $sql = rtrim($sql, ", ") . ") ENGINE=MariaDB";
        $this->query($sql);
    }

    public function alterTable($tableName, $fields) {
        foreach ($fields as $field => $type) {
            $sql = "ALTER TABLE $tableName ADD $field $type";
            $this->query($sql);
        }
    }

    public function changeColumn($tableName, $oldColumn, $newColumn, $type) {
        $sql = "ALTER TABLE $tableName CHANGE $oldColumn $newColumn $type";
        $this->query($sql);
    }

    public function modifyColumn($tableName, $column, $type) {
        $sql = "ALTER TABLE $tableName MODIFY $column $type";
        $this->query($sql);
    }

    public function renameColumn($tableName, $oldColumn, $newColumn) {
        $sql = "ALTER TABLE $tableName RENAME COLUMN $oldColumn TO $newColumn";
        $this->query($sql);
    }

    public function dropColumn($tableName, $column) {
        $sql = "ALTER TABLE $tableName DROP COLUMN $column";
        $this->query($sql);
    }

    public function getTableStructure($tableName) {
        $result = $this->query("SHOW CREATE TABLE $tableName");
        $row = $result->fetch_assoc();
        return $row['Create Table'];
    }
}
