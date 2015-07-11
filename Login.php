<?php

/**
 * Description of database
 *
 * @author Johana Aleman
 */
class Login {
    /**
     * @var object $db_connection The database connection
     */
    private $db_connection = null;
    private $userId = null;
        
    public function __construct()
    {    
        // if user just submitted a login form
        if (isset($_POST["login"])) {
            $this->login($_POST['userId'], $_POST['userPassword']);
        }
    }
    
    /**
     * Checks if database connection is opened. 
     */
    private function databaseConnection()
    {
        // if connection already exists
        if ($this->db_connection != null) {
            return true;
        } else {
            try {
                // Generate a database connection, using the PDO connector
               $this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
                return true;
            } catch (PDOException $e) {
                echo "Database connection problem" . $e->getMessage();
            }
        }
        return false;
    }
          
    /**
     * Search into database for the userId specified as parameter
     */
    private function getUserData($userId)
    {
        // if database connection opened
        if ($this->databaseConnection()) {
            // database query, getting all the info of the selected user
            $query_user = $this->db_connection->prepare('SELECT * FROM users WHERE userId = :userId');
            $query_user->bindValue(':userId', $userId, PDO::PARAM_STR);
            $query_user->execute();
            return $query_user->fetchObject();
        } else {
            return false;
        }
    }
    
    /**
     * handles the Addition of new user
     */
    public function AddNewUser($userId, $userFirstName, $userLastName, $userPassword, $userPasswordRepeat)
    {
        $userId  = trim($userId);
       if (empty($userId)) {
            echo "UserId field is empty";
        } if (empty($userFirstName)) {
            echo "User First name field is empty";
        }if (empty($userLastName)) {
            echo "User Last name field is empty";
        }elseif (empty($userPassword) || empty($userPasswordRepeat)) {
            echo "Password field is empty";
        } elseif ($userPassword !== $userPasswordRepeat) {
            echo "Passwords are not the same";
        } elseif (strlen($userPassword) < 6) {
            echo "Password has a minimum length of 6 characters";
        } elseif (strlen($userId) < 2) {
            echo "Username cannot be shorter than 2";
        } elseif (!preg_match('/^[a-z\d]{2}$/i', $userId)) {
            echo "Only a-Z and numbers are allowed";
        } else if ($this->databaseConnection()) {
            // check if username already exists
            $query_check_user_name = $this->db_connection->prepare('SELECT userId FROM users WHERE userId=:userId');
            $query_check_user_name->bindValue(':userId', $userId, PDO::PARAM_STR);
            $query_check_user_name->execute();
            $result = $query_check_user_name->fetchAll();
            // check if usernId exist in the database
            if (count($result) > 0) {
                for ($i = 0; $i < count($result); $i++) {
                    echo "UserId is already taken.";
                }
            } else {
                $userPasswordMd5 = md5($userPassword);
                // write new users data into database
                $query_new_user_insert = $this->db_connection->prepare('INSERT INTO users (userId, userFirstName, userLastName, userPassword, userDateCreated) VALUES (:userId, :userFirstName, :userLastName, :userPassword, now())');
                $query_new_user_insert->bindValue(':userId', $userId, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':userFirstName', $userFirstName, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':userLastName', $userLastName, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':userPassword', $userPasswordMd5, PDO::PARAM_STR);
                $query_new_user_insert->execute();
                if (!$query_new_user_insert) {
                    echo "Registration failed. Please go back and try again";
                }
                else {
                    echo "Registered!";
                }
            }
        }
    }
    
     /**
     * Logs in 
     */
    private function login($userId, $userPassword)
    {
        if (empty($userId)) {
            echo "Username field is empty";
        } else if (empty($userPassword)) {
            echo "Password field is empty";
        } else {
            $result_row = $this->getUserData(trim($userId));
            $userPasswordMd5 = md5($userPassword);
            } 
            // if this user not exists and password doesnt match
            if (! isset($result_row->userId) || $userPasswordMd5 != $result_row->userPassword) {
                echo "Login failed";
            }
        }
    
}
