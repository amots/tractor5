<?php

class db {
    /*     * * Declare instance ** */

    private static $instance = NULL;
    /**
     *
     * the constructor is set to private so
     * so nobody can create a new instance using new
     *
     */
    static $defFile = __SITE_PATH . '/private/db_config.php';

    private function __construct() {
    
    }
    public static function getDbName() {
        require_once self::$defFile;
        return DBconfig::basename;
    }
    /**
     *
     * Return DB instance or create intitial connection
     *
     * @return object (PDO)
     *
     * @access public
     *
     */
    public static function getInstance() {
        
        if (!self::$instance) {
            
            require_once self::$defFile;
            $dsn = 'mysql:host=' . DBconfig::host . ';dbname=' . DBconfig::basename . ';port=' . DBconfig::port . ';connect_timeout=15';
            $user = DBconfig::user;
            $password = DBconfig::password;

            $opt = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_EMULATE_PREPARES => true,
            ];
            try {
                self::$instance = new PDO($dsn, $user, $password, $opt);
            } catch (PDOException $err) {
                if ($registry->debug) {
                    Debug::dump($err->getMessage(), "connection failed in " . util::getCaller());
                }
                return NULL;
            }
            self::$instance->exec("SET NAMES 'utf8'");
        }
        return self::$instance;
    }

    /**
     *
     * Like the constructor, we make __clone private
     * so nobody can clone the instance
     *
     */
    private function __clone() {
        
    }

}

/* * * end of class ** */
?>
