<?php

namespace App\Entities;

class User
{
    private $_id;
    private $_username;
    private $_password;
    private $_role;

    public function __construct($id, $username, $password, $role)
    {
        $this->_id = $id;
        $this->_username = $username;
        $this->_password = $password;
        $this->_role = $role;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function getRole()
    {
        return $this->_role;
    }

    // Setters
    public function setId($id)
    {
        $this->_id = $id;
    }

    public function setUsername($username)
    {
        $this->_username = $username;
    }

    public function setPassword($password)
    {
        $this->_password = $password;
    }

    public function setRole($role)
    {
        $this->_role = $role;
    }
}
