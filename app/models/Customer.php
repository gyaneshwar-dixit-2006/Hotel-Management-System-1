<?php

class Customer
{
    private $cid;
    private $fullname;
    private $email;
    private $password;
    private $phone;
    private $isadmin;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->cid;
    }

    public function setId($id)
    {
        $this->cid = $id;
    }

    public function getFullName()
    {
        return $this->fullname;
    }

    public function setFullName($fullName)
    {
        $this->fullname = $fullName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        // Avoid double-hashing if already a bcrypt/argon2 hash
        if (is_string($password) && (strpos($password, '$2y$') === 0 || strpos($password, '$2a$') === 0 || strpos($password, '$2b$') === 0)) {
            $this->password = $password;
        } else {
            $this->password = password_hash($password, PASSWORD_BCRYPT);
        }
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getIsAdmin()
    {
        return (int) $this->isadmin;
    }

    public function setIsAdmin($isadmin)
    {
        $this->isadmin = $isadmin;
    }
}
