<?php

class User {
    public $id;
    public $name;
    public $email;
    public $role;

    public function show(){
        echo "User id : " . $this->id ." - ";
        echo "User name : " . $this->name ." - ";
        echo "User email : " . $this->email ." - ";
        echo "User role : " . $this->role ."<br>";
    }
}
