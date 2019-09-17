<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/********************************************************
 * * *    			   Mongo Private    			* * *
 * * *    				Version 1.0    				* * *
 * * *    	Author: dung.huynh@southtelecom.vn    	* * *
 ********************************************************/

/**
 * CLASS Mongo_private
 * @use: Set instance of Class. 
 * @example: var a = new Query(model); a.kendoQuery = queryObjectKendo
 * @return: (array) aggregate pipelines cho vao ham aggregate
 */

Class Mongo_private {
	/**
     * DB object
     *
     * @var	object
     */
    protected $_mongo;
    protected $mongo_connect;

    /**
     * Private variable to hold MongoDB database configs
     *
     * @var	array
     */
    protected $_mongo_config;

    public $wheres = array();

    public $sorts  = array();

    public $selects = array();

    /**
     * Construct
     */

    function __construct()
	{	
		$this->_build_config();
        $this->_connect();
    }

    function get($collection)
    {
    	$cursor = $this->_mongo->{$collection}->find($this->wheres, $this->selects)->sort($this->sorts);
    	$data = array();
    	foreach ($cursor as $value) {
            if( isset($value["_id"]) ) {
                $value["id"] = $value["_id"]->{'$id'};
                unset($value["_id"]);
            }
    		$data[] = $value;
    	}
        $this->_clear();
    	return $data;
    }

    function getOne($collection)
    {
    	$doc = $this->_mongo->{$collection}->findOne($this->wheres, $this->selects);
        if($doc) 
        {
            if( isset($doc["_id"]) ) {
                $doc["id"] = $doc["_id"]->{'$id'};
                unset($doc["_id"]);
            }
        }
        $this->_clear();
    	return $doc;
    }

    function insert($collection, $data)
    {
    	$result = $this->_mongo->{$collection}->insert($data, array('w' => $this->_mongo_config['w'], 'j'=>$this->_mongo_config['j']));
    	return !empty($result["ok"]) ? TRUE : FALSE;
    }

    function update($collection, $update)
    {
    	if( isset($update['$set']) ) 
        {
            unset($update['$set']["_id"]);
        }
    	$result = $this->_mongo->{$collection}->update($this->wheres, $update, array('w' => $this->_mongo_config['w'], 'j'=>$this->_mongo_config['j']));
        $this->_clear();
    	return !empty($result["ok"]) ? TRUE : FALSE;
    }

    function remove($collection)
    {
    	$result = $this->_mongo->{$collection}->remove($this->wheres, array('w' => $this->_mongo_config['w'], 'j'=>$this->_mongo_config['j']));
        $this->_clear();
    	return !empty($result["ok"]) ? TRUE : FALSE;
    }

    function where($wheres = array()) 
    {
        $this->wheres = array_merge($this->wheres, $wheres);
        return $this;
    }

    function where_id($id)
    {
        $this->wheres = array("_id" => new MongoId($id));
        return $this;
    }

    function order_by($sort = array())
    {
        $this->sorts = $sort;
        return $this;
    }

    function select($includes = array(), $excludes = array()) {
        if (!empty($includes)) {
            foreach ($includes as $col) {
                $this->selects[$col] = 1;
            }
        }
        if (!empty($excludes)) {
            foreach ($excludes as $col) {
                $this->selects[$col] = 0;
            }
        }
        return ($this);
    }

    private function _clear()
    {
        $this->wheres = array();
        $this->sorts  = array();
        $this->selects  = array();
    }

    function aggregate_pipeline($collection, $pipeline = array())
    {
        
        if($this->_mongo_config['version'] > 3.4) 
        {
            $cursor = $this->_mongo->{$collection}->aggregate($pipeline, array('cursor' => new stdClass()));
            $data = isset($cursor["cursor"], $cursor["cursor"]["firstBatch"]) ? $cursor["cursor"]["firstBatch"] : [];
        }
        else
        {
            $cursor = $this->_mongo->{$collection}->aggregate($pipeline);
            $data = !empty($cursor["ok"]) ? $cursor["result"] : [];
        }
        foreach ($data as &$doc) {
            if(isset($doc["_id"])) 
            {
                $doc["id"] = (string) $doc["_id"];
                unset($doc["_id"]);
            }
        }
        return $data;
    }

    /**
     * private method to prepare all mongodb related configs
     *
     * @param	object	CI instance
     * @return	boolean
     */
    private function _build_config()
    {
    	//mongo PECL driver loaded ??
        if ( ! class_exists('Mongo') && ! class_exists('MongoClient'))
        {
            show_error("The MongoDB PECL extension has not been installed or enabled", 500);
        }

        // Configuration & other initializations
        $CI =& get_instance();

        $CI->config->load('_mongo');

        if(!empty($CI->config->item("_mongo_version")))
        {
            $this->_mongo_config['version'] = $CI->config->item("_mongo_version");
        }
        else
        {
            throw new Exception("MongoDB config missing, check _mongo_version value.");
        }

        if(!empty($CI->config->item("_mongo_location")))
        {
            $this->_mongo_config['location'] = $CI->config->item("_mongo_location");
        }
        else
        {
            throw new Exception("MongoDB config missing, check _mongo_location value.");
        }

        if(!empty($CI->config->item("_mongo_port")))
        {
            $this->_mongo_config['port'] = $CI->config->item("_mongo_port");
        }
        else
        {
            throw new Exception("MongoDB config missing, check _mongo_port value.");
        }

        if(!empty($CI->config->item("_mongo_db")))
        {
            $this->_mongo_config['db'] = $CI->config->item("_mongo_db");
        }
        else
        {
            throw new Exception("MongoDB config missing, check _mongo_db value.");
        }

        $this->_mongo_config['username'] = $CI->config->item("_mongo_user");

        $this->_mongo_config['password'] = $CI->config->item("_mongo_password");

        if(!empty($CI->config->item("_mongo_write_concerns")))
        {
            $this->_mongo_config['w'] = $CI->config->item("_mongo_write_concerns");
        }
        else
        {
            throw new Exception("MongoDB config missing, check _mongo_write_concerns value.");
        }

        if(!empty($CI->config->item("_mongo_write_journal")))
        {
            $this->_mongo_config['j'] = $CI->config->item("_mongo_write_journal");
        }
        else
        {
            throw new Exception("MongoDB config missing, check _mongo_write_journal value.");
        }
    }

    private function _connect()
    {
        // Initialize storage mechanism (connection)
        //prepare mongodb connection string
        $dns = "mongodb://{$this->_mongo_config['location']}:{$this->_mongo_config['port']}/{$this->_mongo_config['db']}?authSource=admin";

        //perform connection.
        $authenticate = array();
        if($this->_mongo_config['username']) {
            $authenticate = array('username'=>$this->_mongo_config['username'], 'password'=>$this->_mongo_config['password']);
        }
        $this->mongo_connect = new MongoClient($dns, $authenticate);
        if(empty($this->mongo_connect) || ! $this->mongo_connect)
        {
            throw new Exception('Connect unsuccessful!');
        }
        //when connected successfully, selected the database.
        $this->_mongo = $this->mongo_connect->selectDB($this->_mongo_config['db']);
        $this->_mongo = $this->mongo_connect->{$this->_mongo_config['db']};
    }
    
    public function distinct($collection = "", $key="" ){
        if (empty($collection)) {
            exit("In order to retreive documents from MongoDB, a collection name must be passed");
        }

        $documents = $this->_mongo->{$collection}->distinct($key, $this->wheres);

        // Clear
        $this->_clear();

        return $documents;
    }
}