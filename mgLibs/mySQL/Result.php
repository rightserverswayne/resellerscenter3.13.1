<?php

namespace MGModule\ResellersCenter\mgLibs\MySQL;
use MGModule\ResellersCenter as main;


/**
 * MySQL Results Class
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Result{
    /**
     *
     * @var PDOStatement 
     */
    private $result;
    
    /**
     * Constructor 
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param PDOStatement $result
     * @param int $id
     */
    function __construct($result,$id = null) {

        $this->result = $result;
        $this->id = $id;
    }
    
    /**
     * Fetch one record
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return array
     */
    function fetch()
    {
        return $this->result->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Fetch All Records
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return array
     */
    function fetchAll()
    {
        return $this->result->fetchAll(\PDO::FETCH_ASSOC);
    }
        
    /**
     * Fetch One Column From First Record
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $name
     * @return array
     */
    function fetchColumn($name = null)
    {
        $data = $this->result->fetch(\PDO::FETCH_BOTH);
        
        if($name)
        {
            return $data[$name];
        }
        else
        {
            return $data[0];
        }
    }
    
    /**
     * Get ID Last Inserted Record 
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return int
     */
    function getID()
    {
        return $this->id;
    }
    
}