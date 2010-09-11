<?php

require_once 'basic.php';
require_once ($localBasePath.'/include/Error.class.php');

//
// Database connection class
//
 
class DB {

    private $connection;
    private $username;
    private $password;
    private $dsn;

    private static $instance = null;

    public function get()
    {
      if(self::$instance == null)
      {   
         $c = __CLASS__;
         self::$instance = new $c;
      }

      return self::$instance;
    }

    public function __construct() {             
        global $strDB_DSN, $strDB_Username, $strDB_Password;
        
        $this->dsn = $strDB_DSN;
        $this->username = $strDB_Username;
        $this->password = $strDB_Password;
        $this->connection = NULL;
    }

/* Generic one. If we want to have only one we should add null as default and check adns === null etc
    public function __construct($adns, $ausername, $apassword) {             
        $this->dsn = $adns;
        $this->username = $ausername;
        $this->password = $apassword;
        $this->connection = NULL;
    }
*/

    public function setConnection($conn) {
        $this->connection = $conn;
    }

    public function getConnection() {
        if (!$this->connection) $this->connect();
        return $this->connection;
    }
    
    public function quote($a) {
        return $this->connection->quote($a);
    }

    
    /**
     * Make a connection with a database using PDO Object.
     *
     */
    public function connect() {
        try {
            //echo $this->dsn .'  '. $this->username .'  '. $this->password; //debug pwd
            $pdoConnect = new PDO($this->dsn, $this->username, $this->password);
            $this->connection = $pdoConnect;
            return $pdoConnect;
        } catch (PDOException $e) {
            die("Error connecting database: Connection::connect(): ".$e->getMessage());
        }
    }
        
    
    /**
     * Execute a DML
     *
     * @param String $query
     */
    public function executeDML($query) {
        if (!$this->getConnection()->query($query)) {
            throw new Error($this->getConnection()->errorInfo());
        } else {
            return true;
        }
    }
    /**
     * Execute a query
     *
     * @param String $query
     * @return PDO ResultSet Object
     */
    public function executeQuery($query) {
        $rs = null;
        if ($stmt = $this->getConnection()->prepare($query)) {
            if ($this->executePreparedStatement($stmt, $rs)) {
                return $rs;
            }
        } else {
            throw new Error($this->getConnection()->errorInfo());
        }
    }

    /**
     * Execute a prepared statement 
     * it is used in executeQuery method
     *
     * @param PDOStatement Object $stmt
     * @param Array $row
     * @return boolean
     */
    private function executePreparedStatement($stmt, & $row = null) {
        $boReturn = false;
        if ($stmt->execute()) {
            if ($row = $stmt->fetchAll()) {
                $boReturn = true;
            } else {
                $boReturn = false;
            }
        } else {
            $boReturn = false;
        }
        return $boReturn;
    }

    /**
     * Init a PDO Transaction
     */
    public function beginTransaction() {
        if (!$this->getConnection()->beginTransaction()) {
            throw new Error($this->getConnection()->errorInfo());
        }
    }
    /**
     * Commit a transaction
     *
     */
    public function commit() {
        if (!$this->getConnection()->commit()) {
            throw new Error($this->getConnection()->errorInfo());
        }
    }
    /**
     * Rollback a transaction
     *
     */
    public function rollback() {
        if (!$this->getConnection()->rollback()) {
            throw new Error($this->getConnection()->errorInfo());
        }
    }
}

/*
//Memcached singleton 
//Let's not use it right now.
class MC extends Memcache 
{
    const LOCK_TIMEOUT = 30;
    const LOCK_RETRY = 100; //value in microsec
    
    private static $instance = null;    
    public $usemc = true; //Just a public variable to keep the status if we are using caching or not
    
    public static function getInstance() //I canot call it just get =( because of Memcached::get
    {
      if(self::$instance == null)
      {            
         self::$instance = new MC();
         if(!self::$instance->connect ('localhost'))
         {
             ///### In reality we don't have to stop the execution, just ignoring memchached...
             ///    Wait.... oin case of writing the objects would be dirty if I ignore it ! 
             error('Problem with memchached');
             die();
         }         
      }
      return self::$instance;
    }
    
    /*
     * ### Note by Chris: All the locking mechanisms will not work well in case of multiple instance of memchache
     * using the CAS mechanism is a way to deal with some race condictions
     * Look at:
     *         http://www.quora.com/What-is-the-best-way-to-implement-a-mutex-on-top-of-memcached
     *         http://www.quora.com/What-are-the-most-common-sources-of-memcached-cache-inconsistency
     *         http://php.net/manual/en/memcached.cas.php (use CAS)
     * Regarding namespacing:
     *         http://groups.google.com/group/memcached/browse_thread/thread/233a2fd1c1630572 (interesting idea parent key)
     *         http://blog.de-zwart.net/2010-03/memcache-namespacing/
     *         
     *General reference:
     *         http://code.google.com/p/memcached/wiki/FAQ
     *Interesting ideas:
     *         http://www.webdevrefinery.com/forums/topic/2345-memcached-wrapper/
     *         
     * Keep looking at REDIS and MongoDB as way to replace MySQL (at least on not critical features)
     *
    
    function getLock( $k ) //Semi active waiting lock on a resource
    {
        while ( !self::getInstance()->add("LOCK:$k", 1, false, LOCK_TIMEOUT ) ) { usleep(LOCK_RETRY); }
    }
    
    function releaseLock( $k )
    {
        self::getInstance()->delete("LOCK:$k");
    }
}*/
?>