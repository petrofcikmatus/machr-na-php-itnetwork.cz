<?php

class Database {

    private static $instance = null;

    /** @var PDO $this */
    private $connection;

    private static $credentials = array(
        "type" => "mysql",
        "host" => "localhost",
        "port" => 3306,
        "name" => "default",
        "user" => "root",
        "pass" => "root",
    );

    private static $settings = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    );

    /**
     * @param array $credentials
     */
    public static function setCredentials(array $credentials = array()) {
        foreach ($credentials as $key => $value) {
            self::$credentials[$key] = $value;
        }
    }

    /**
     * @param array $settings
     */
    public static function setSettings(array $settings = array()) {
        foreach ($settings as $key => $value) {
            self::$settings[$key] = $value;
        }
    }

    /**
     * @return Database
     */
    public static function getInstance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Database constructor.
     */
    private function __construct() {
        try {
            $this->connection = new PDO(
                self::$credentials["type"] . ":host=" . self::$credentials["host"] . ";port=" . self::$credentials["port"] . ";dbname=" . self::$credentials["name"],
                self::$credentials["user"],
                self::$credentials["pass"],
                self::$settings
            );
        } catch (PDOException $e) {
            throw new Exception("Application error: Unable to connect database.", 0, $e);
        } catch (Exception $e) {
            throw new Exception("Application error: Unknown error.", 0, $e);
        }
    }

    private function __clone() {
        // I do nothing :'(
    }

    /**
     * @param $query
     * @param array $parameters
     * @return int
     */
    public function query($query, array $parameters = array()) {
        $return = $this->connection->prepare($query);
        $return->execute($parameters);
        return $return->rowCount();
    }

    /**
     * @param $query
     * @param array $parameters
     * @return mixed
     */
    public function queryOne($query, array $parameters = array()) {
        $return = $this->connection->prepare($query);
        $return->execute($parameters);
        $row = $return->fetch(PDO::FETCH_NUM);
        return $row[0];
    }

    /**
     * @param $query
     * @param array $parameters
     * @return mixed
     */
    public function queryRow($query, array $parameters = array()) {
        $return = $this->connection->prepare($query);
        $return->execute($parameters);
        return $return->fetch(PDO::FETCH_OBJ);
    }

    /**
     * @param $query
     * @param array $parameters
     * @return array
     */
    public function queryAll($query, array $parameters = array()) {
        $return = $this->connection->prepare($query);
        $return->execute($parameters);
        return $return->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @param null $name
     * @return string
     */
    public function getLastId($name = null) {
        return $this->connection->lastInsertId($name);
    }

    /**
     * @param $string
     * @return string
     */
    public function quote($string) {
        return $this->connection->quote($string);
    }
}
