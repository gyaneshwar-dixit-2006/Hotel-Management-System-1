<?php

class CustomerHandler extends CustomerDAO
{
    public function __construct()
    {
    }
    
    private $executionFeedback;

    public function getExecutionFeedback()
    {
        return $this->executionFeedback;
    }

    public function setExecutionFeedback($executionFeedback)
    {
        $this->executionFeedback = $executionFeedback;
    }

    public function getAllCustomer()
    {
        $all = $this->getAll();
        return is_array($all) ? $all : [];
    }

    public function getSingleRow($email)
    {
        $row = $this->getByEmail($email);
        return (!empty($row) && is_array($row)) ? $row : [];
    }

    public function getCustomerObj($email)
    {
        $c = new Customer();
        $k = $this->getByEmail($email);
        if (!empty($k) && is_array($k)) {
            foreach ($k as $v) {
                $c->setId($v->getId());
                $c->setEmail($v->getEmail());
                $c->setPassword($v->getPassword());
                $c->setPhone($v->getPhone());
                $c->setFullName($v->getFullName());
                $c->setIsAdmin($v->getIsAdmin());
            }
        }
        return $c;
    }

    public function getCustomerObjByCid($id)
    {
        $c = new Customer();
        $k = $this->getByCid($id);
        if (!empty($k) && is_array($k)) {
            foreach ($k as $v) {
                $c->setId($v->getId());
                $c->setEmail($v->getEmail());
                $c->setPassword($v->getPassword());
                $c->setPhone($v->getPhone());
                $c->setFullName($v->getFullName());
                $c->setIsAdmin($v->getIsAdmin());
            }
        }
        return $c;
    }

    public function getUsername($_email)
    {
        $_fullName = null;
        $rows = $this->getSingleRow($_email);
        if (!empty($rows)) {
            foreach ($rows as $obj) {
                if (method_exists($obj, 'getFullName')) {
                    $_fullName = $obj->getFullName();
                }
            }
        }
        if (!empty($_fullName)) {
            return $_fullName;
        } else {
            $positionOfAt = strpos($_email, "@");
            return $positionOfAt !== false ? substr($_email, 0, $positionOfAt) : $_email;
        }
    }

    public function insertCustomer(Customer $customer)
    {
        if ($this->isCustomerExists($customer->getEmail()) == 0) {
            if ($this->insert($customer)) {
                $this->setExecutionFeedback("You have successfully registered! You can now login.");
            } else {
                $this->setExecutionFeedback(Util::DB_SERVER_ERROR);
            }
        } else {
            $this->setExecutionFeedback("Email already registered.");
        }
    }

    public function updateCustomer(Customer $customer)
    {
        $c_data = [$customer->getId(), $customer->getFullName(), $customer->getPhone(), $customer->getEmail(), $customer->getPassword()];
        $c_data_string = implode(", ", $c_data);
        if(Util::has_reserved_words($c_data_string)) {
            $this->setExecutionFeedback("Something is not right.");
        } else {
            if ($this->isCustomerExists($customer->getEmail()) == 1) {
                if ($this->update($customer)) {
                    $this->setExecutionFeedback("You have successfully updated your profile!");
                } else {
                    $this->setExecutionFeedback(Util::DB_SERVER_ERROR);
                }
            } else {
                $this->setExecutionFeedback("This email is not registered.");
            }
        }
    }

    public function deleteCustomer(Customer $customer)
    {
        if ($this->isCustomerExists($customer->getEmail()) == 1) {
            if ($this->delete($customer)) {
                $this->setExecutionFeedback("You have successfully deleted your profile!");
            } else {
                $this->setExecutionFeedback(Util::DB_SERVER_ERROR);
            }
        } else {
            $this->setExecutionFeedback("This email is not registered.");
        }
    }

    public function isPasswordMatchWithEmail($password, Customer $customer)
    {
        $rows = $this->getByEmail($customer->getEmail());
        if (empty($rows)) {
            return false;
        }
        $cust = $rows[0];
        return password_verify($password, $cust->getPassword());
    }

    public function totalCustomersCount()
    {
        return count($this->getAllCustomer());
    }

    public function doesCustomerExists($email) {
        return $this->isCustomerExists($email);
    }

    public function handleIsAdmin($email) {
        return $this->isAdminCount($email);
    }
}
