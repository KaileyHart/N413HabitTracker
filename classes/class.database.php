<?php


class Database {

    private $user;
    private $password;
    private $database;
    private $hostname;
    private $conn;

    function __construct()
    {
        $this->user = "root";
        $this->password = ""; 
        $this->database = "photo_share";
        $this->hostname = "localhost";
    }

    function getSQL($sql) {
        $this->conn = mysqli_connect($this->hostname, $this->user, $this->password, $this->database);

        if(!$this->conn) {
            return "connection failed. " . $this->conn->connect_error;
        }

        $result = $this->conn->query($sql);

        $results = array();

        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            return $results;
        }
    }
}
?>