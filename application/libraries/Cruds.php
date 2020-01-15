<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Kendo_aggregate.php");

/****************************************************************************
 * * *          CRUDS - Create Read Update Delete Multi Collection    	* * *
 * * *                  		Version 1.0                 			* * *
 * * *      		Author: dung.huynh@southtelecom.vn      			* * *
 ****************************************************************************/

/**
 * CLASS CRUDS
 * @use: Set instance of Class. 
 * @example: 
 * @return: 
 */

Class Cruds {

    private $CI;

    private $log_fields =   ["createdAt", "updatedAt", "deletedAt"];

    public $wheres      =   array();

    public $sorts       =   array();

    function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library("Mongo_db");
    }

    public function switch_db($db = "")
    {
        $this->CI->mongo_db->switch_db($db);
        return $this;
    }

    public function select_db($db = "")
    {
        $this->CI->mongo_db->switch_db($db);
        return $this;
    }

    public function reset_db()
    {
        $this->CI->mongo_db->switch_db();
        return $this;
    }

    function read($collection, $kendo_query = array(), $selects = array(), $match = array())
    {
        // Xay dung mo hinh hoa du lieu
        $model = $this->build_model($collection);

        // Kendo to aggregate
        $Kendo_aggregate = new Kendo_aggregate($model);
        $Kendo_aggregate->set_kendo_query($kendo_query)->matching($match)->filtering();
        
        $skip = isset($kendo_query["skip"]) ? $kendo_query["skip"] : 0;
        $limit = isset($kendo_query["take"]) ? $kendo_query["take"] : 0;

        // Get total
        $total_aggregate = $Kendo_aggregate->get_total_aggregate();
        $total = $this->getTotal($collection, $total_aggregate, $skip, $limit);

        // Get data
        $data_aggregate = $Kendo_aggregate->sorting()->selecting($selects)->get_data_aggregate();
        $data = $this->getData($collection, $data_aggregate, $skip, $limit);
        
        // Result
        $result = array("data" => $data, "total" => $total);
        return $result;
    }

    function getTotal($collection, $total_aggregate, $skip, $limit, $currentTotal = 0, $direction = "prev")
    {
        $total_result = $this->aggregate_pipeline($collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        $currentTotal += $total;
        if($limit > $total) {
        	$node = $this->CI->mongo_db->where(["name"=>$collection])->getOne("Linked_list");
        	if(!$node) return $currentTotal;
        	if(empty($node[$direction])) return $currentTotal;
        	$next_limit = $limit - $total;
        	return $this->getTotal($node[$direction], $total_aggregate, 0, $next_limit, $currentTotal);
        } else return $currentTotal;
    }

    function getData($collection, $data_aggregate, $skip, $limit, $currentData = array(), $direction = "prev")
    {
    	$after_paging_data_aggregate = $data_aggregate;
    	$after_paging_data_aggregate[] = array( '$skip' => $skip );
    	$after_paging_data_aggregate[] = array( '$limit' => $limit );
        $data = $this->aggregate_pipeline($collection, $after_paging_data_aggregate);
        foreach ($data as $doc) {
        	$currentData[] = $doc;
        }
        if($limit > count($data)) {
        	$node = $this->CI->mongo_db->where(["name"=>$collection])->getOne("Linked_list");
        	if(!$node) return $currentData;
        	if(empty($node[$direction])) return $currentData;
        	$next_limit = $limit - count($data);
        	return $this->getData($node[$direction], $data_aggregate, 0, $next_limit, $currentData);
        } else return $currentData;
    }

    function distinct($collection, $kendo_query = array(), $selects = array(), $match = array())
    {
        // Xay dung mo hinh hoa du lieu
        $model = $this->build_model($collection);

        // Kendo to aggregate
        $Kendo_aggregate = new Kendo_aggregate($model);
        $Kendo_aggregate->set_kendo_query($kendo_query)->matching($match)->filtering();
        // Get result
        if(!$selects) throw new Exception("selects is empty");
        if(count($selects) == 1) {
            $grouping = array(
                '$group'    => array("_id" => null, "data" => array('$addToSet' => '$'.$selects[0]))
            );
        } else {
            foreach ($selects as $index => $field) {
                $concatArr[] = '$' . $field;
                if($index + 1 < count($selects)) {
                    $concatArr[] = "|";
                }
            }
            $grouping = array(
                '$group'    => array("_id" => null, "data" => array('$addToSet' => array('$concat' => $concatArr)))
            );
        }
        $filtering = array(
            '$project'  => array("_id" => 0, "data" => 1, "total" => array('$size' => '$data'))
        );
        $data_aggregate = $Kendo_aggregate->sorting()->adding($grouping, $filtering)->get_data_aggregate();
        $data = $this->aggregate_pipeline($collection, $data_aggregate);
        $result = $data ? $data[0] : array("data" => [], "total" => 0);
        return $result;
    }

    function create($collection, $document, $object_id_to_string = TRUE)
    {
        $parse_doc = $this->_parse($collection, $document);
        $parse_doc["createdAt"] = (int) time(); 
        $inserted_data = $this->CI->mongo_db->insert($collection, $parse_doc);
        if($inserted_data && $object_id_to_string)
        {
            $inserted_data = $this->convert_document($inserted_data);
        }
        return $inserted_data;
    }

    function update($collection, $update, $options = [])
    {
        if( isset($update['$set']) )
        {
            $document = $update['$set'];
            $parse_doc = $this->_parse($collection, $document);
            $parse_doc["updatedAt"] = (int) time();
            $update['$set'] = $parse_doc;
        }
        return $this->CI->mongo_db->where($this->wheres)->update($collection, $update, $options);
    }

    function delete($collection, $permanent = true)
    {
        if($permanent) 
        {
            $result = $this->CI->mongo_db->where($this->wheres)->delete($collection);
        } 
        else
        {
            $result = $this->CI->mongo_db->where($this->wheres)->update($collection, array('$set' => array("deleted" => TRUE, "deletedAt" => (int) time())));
        }
        return $result;
    }

    function delete_all($collection, $permanent = true)
    {
        if($permanent) 
        {
            $result = $this->CI->mongo_db->where($this->wheres)->delete_all($collection);
        } 
        else
        {
            $result = $this->CI->mongo_db->where($this->wheres)->update_all($collection, array('$set' => array("deleted" => TRUE, "deletedAt" => (int) time())));
        }
        return $result;
    }

    function get($collection, $selects = array(), $object_id_to_string = TRUE)
    {
        $unselects = $selects ?  [] : $this->log_fields;
        $data = $this->CI->mongo_db->where($this->wheres)->order_by($this->sorts)->select($selects, $unselects)->get($collection);
        
        if($object_id_to_string)
        {
            foreach ($data as &$doc) {
                $doc = $this->convert_document($doc);
            }
        }
        $this->_clear();
        return $data;
    }

    function getOne($collection, $selects = array(), $convert = TRUE)
    {
        $doc = $this->CI->mongo_db->where($this->wheres)->order_by($this->sorts)->select($selects)->getOne($collection);
        
        if($convert)
        {
            $doc = $this->convert_document($doc, $collection);
        }
        $this->_clear();
        return $doc;
    }

    function aggregate_pipeline($collection, $pipeline, $object_id_to_string = TRUE)
    {
        $data = $this->CI->mongo_db->aggregate_pipeline($collection, $pipeline);
        if($object_id_to_string)
        {
            foreach ($data as &$doc) {
                $doc = $this->convert_document($doc);
            }
        }
        return $data;
    }

    function where($wheres)
    {
        $this->wheres = array_merge($this->wheres, $wheres);
        return $this;
    }

    function where_id($id)
    {
        $this->wheres = array("_id" => new MongoDB\BSON\ObjectId($id));
        return $this;
    }

    function where_object_id($field, $id)
    {
        $this->wheres[$field] = new MongoDB\BSON\ObjectId($id);
        return $this;
    }

    function order_by($sorts = array())
    {
        $this->sorts = array_merge($this->sorts, $sorts);
        return $this;
    }

    function _clear()
    {
        $this->wheres = array();
    }

    function _parse($collection, $document)
    {
        $model = $this->build_model($collection);
        foreach ($document as $field => &$value) {
            if( isset($model[$field]) && isset($model[$field]["type"]) ) {
                switch ($model[$field]["type"]) {
                    case 'string': 
                        $value = (string) $value;
                        break;

                    case 'int':
                        $value = (int) $value;
                        break;

                    case 'double':
                        $value = (double) $value;
                        break;
                    
                    case 'timestamp':
                        $value = is_string($value) ? strtotime(preg_replace('/\([^)]*\)/', '', $value)) : $value;
                        break;

                    case 'datetime':
                        $value = $this->CI->mongo_db->date(strtotime(preg_replace('/\([^)]*\)/', '', $value)));
                        break;

                    case 'boolean':
                        $value = (boolean) (is_string($value) ? ($value == "true") : $value);
                        break;

                    case 'ObjectId': 
                        $value = $value ? new MongoDB\BSON\ObjectId($value) : null;
                        break;

                    case 'array': 
                        $value = is_array($value) ? $value : [];
                        break;

                    default:
                        break;
                }
            }
            $log_fields = $this->log_fields;
            if(in_array($field, $log_fields))
                $value = (int) $value;
        }
        
        return $document;
    }

    public function build_model($collection = "")
    {
        if ( ! $collection ) 
        {
            show_error("Need collection name", 500);
        }
        // Get data model
        $this->CI->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        $file_name      = $collection . "_model";
        
        if ( !$model = $this->CI->cache->get($file_name) ) {
            $this->CI->load->library("mongo_private");
            $model_data = $this->CI->mongo_private->where(array(
                "collection" => $collection))->get("Model");

            $model = array();
            foreach($model_data as $doc) {
                if(isset($doc["field"], $doc["type"])) 
                {
                    $model[$doc["field"]] = array("type" => $doc["type"]);
                }
            }
            $time_cache     = $this->CI->config->item("wff_time_cache");
            $this->CI->cache->save($file_name, $model, $time_cache);
        }
        
        return $model;
    }

    function convert_document($doc, $collection = "")
    {
        if($doc)
        {
            if($collection) 
            {
                $model = $this->build_model($collection);
            }

            foreach ($doc as $field => &$value) {
                // Mongo special value
                if($value instanceof MongoDB\BSON\ObjectId)
                {
                    $value = $value->__toString();
                } elseif($value instanceof MongoDB\BSON\UTCDateTime) {
                    $doc[$field] = date("c", $value->toDateTime()->getTimestamp());
                }
                // Convert by model
                if(isset($model) && isset($model[$field])) 
                {
                    switch ($model[$field]["type"]) {
                        case 'timestamp':
                            $value = is_string($value) ? $value : date("c", $value);
                            break;
                        
                        default:
                            break;
                    }
                }
            }
        }
        return $doc;
    }
}