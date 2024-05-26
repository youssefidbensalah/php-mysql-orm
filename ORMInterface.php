<?php

interface ORMInterface {
    public function create($object);
    public function update($object);
    public function find($id);
    public function findAll();
    public function delete($id);
    public function deleteAll();
    public function updateSchema();
}
