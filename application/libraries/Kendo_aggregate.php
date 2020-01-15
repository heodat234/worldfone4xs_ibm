<?php

/********************************************************
 * * *    Kendo Query Convert to Aggregate Class    * * *
 * * *    				Version 1.2    				* * *
 * * *    	Author: dung.huynh@southtelecom.vn    	* * *
 ********************************************************/

/**
 * CLASS KENDO_AGGREGATE
 * @uses: Set instance of Class. 
 * @example: var a = new Query(model); a.kendoQuery = queryObjectKendo
 * @return: (array) aggregate pipelines cho vao ham aggregate
 * NEW: Add type datetime
 */

Class Kendo_aggregate {

    private $_model         = array();
    private $_kendo_query   = array();

    private $_aggregate     = array();

    private $_default       = array(
        "sort"  => array("_id" => -1),
        "skip"  => 0,
        "limit" => 1000,
    );

	private $OPERATORS      = array(
        'eq'    => '$eq',
        'gt'    => '$gt',
        'gte'   => '$gte',
        'lt'    => '$lt',
        'lte'   => '$lte',
        'neq'   => '$ne',
        'in'    => '$in',
        'nin'   => '$nin'
    );

    private $SPECIAL = array(
        'isnull'        => array('$eq' => null),
        'isnotnull'     => array('$ne' => null),
        'isnotempty'    => array('$exists' => true, '$ne' => ""),
        'isempty'       => ''// Special critiria
    );

    function __construct($model = array())
	{
        $this->_model = $model;
    }

    function set_default($key , $value)
    {
        $this->_default[ $key ] = $value;
    }

    function set_kendo_query($kendo_query) 
    {
        $this->_kendo_query = $kendo_query;
        return $this;
    }

    function get_total_aggregate() {
        $aggregate = $this->_aggregate;
        $aggregate[] = array('$count' => 'total');
        return $aggregate;
    }

    function get_data_aggregate() {
        $aggregate = $this->_aggregate;
        $this->clear();
        return $aggregate;
    }

    function clear()
    {
        $this->_model       = array();
        $this->_kendo_query = array();
        $this->_aggregate   = array();
        return $this;
    }

    function adding()
    {
        $args = func_get_args();
        foreach($args as $aggregate) 
        {
            if(is_array($aggregate) && $aggregate)
                $this->_aggregate[] = $aggregate;
        }
        return $this;
    }

    function matching($match = array()) 
    {
        if($match)
            $this->_aggregate[] = array('$match' => $match);
        return $this;
    }

    function selecting($selects = array()) 
    {
        $project = array();
        if($selects) 
        {
            foreach($selects as $field) {
                $project[$field] = 1;
            }
        } 
        else 
        {
            $model = $this->_model;
            if( $model )
            {
                foreach($model as $field => $property) {
                    if(!isset($property["type"])) continue;
                    switch ($property["type"]) {
                        case 'datetime':
                            $project[$field] = array('$dateToString' => ["format" => "%Y-%m-%dT%H:%M:%S.%LZ","date" => '$'.$field]);
                            break;
                        
                        default:
                            $project[$field] = 1;
                            break;
                    }
                }
            }
        }
        
        if($project) 
            $this->_aggregate[] = array('$project' => $project);
        return $this;
    }

    function filtering()
    {
        if(!empty($this->_kendo_query["filter"])) {
            $match = $this->filter_convert($this->_kendo_query["filter"], $this->_model);
            if($match) {
                $this->_aggregate[] = array('$match' => $match);
            }
        }
        return $this;
    }

    function paging() {
        if( isset($this->_kendo_query["take"], $this->_kendo_query["skip"]) ){
            // By kendo query
            $this->_aggregate[] = array( '$skip' => (int) $this->_kendo_query["skip"] );
            $this->_aggregate[] = array( '$limit' => (int) $this->_kendo_query["take"] );
        } else {
            // Default paging
            $this->_aggregate[] = array( '$skip' => (int) $this->_default["skip"]);
            $this->_aggregate[] = array( '$limit' => (int) $this->_default["limit"]);
        }
        return $this;
    }

    function sorting() {
        if(!empty($this->_kendo_query["sort"])) {
            $aggSorts = array();
            if(isset($this->_kendo_query["sort"]["field"], $this->_kendo_query["sort"]["dir"])) {
                $sort = $this->_kendo_query["sort"];
                switch ($sort["dir"]) {
                    case "asc": 
                        $aggSorts[$sort["field"]] = 1;
                        break;
                    case "desc": default:
                        $aggSorts[$sort["field"]] = -1;
                        break;
                }
            } else {
                for($i = 0, $length = count($this->_kendo_query["sort"]); $i < $length; $i++) {
                    $sort = $this->_kendo_query["sort"][$i];
                    if( isset($sort["field"], $sort["dir"]))
                        switch ($sort["dir"]) {
                            case "asc": 
                                $aggSorts[$sort["field"]] = 1;
                                break;
                            case "desc": default:
                                $aggSorts[$sort["field"]] = -1;
                                break;
                        }
                }
            }
            $this->_aggregate[] = array('$sort' => $aggSorts);
        } else {
            // Default sorting
            if($this->_default["sort"])
            {
                $this->_aggregate[] = array('$sort' => $this->_default["sort"]);
            }
        }
        return $this;
    }

    /**
     * HAM DE QUY CONVERT FILTER TO AGGREGATE
     * @param: (object) filter : Object filter cua kendo
     * @param: (object) model : Model cua bang du lieu hien tai (Mongoose)
     * @return: (object) match: Object cho vao $match cua aggregate
     */
    function filter_convert($filter, $model) {
        if(isset($filter["filters"]) && $filter["filters"]) {
            // TRUONG HOP TAP HOP FILTER
            $logic = isset($filter["logic"]) ? $filter["logic"] : "and";
            $wheres = array();
            $aggMatches = array();
            for($i = 0, $filters = $filter["filters"], $length = count($filters); $i < $length;  $i++) {
                $subfilter = $filters[$i];
                $subWhere = $this->filter_convert($subfilter, $model);
                if($subWhere) 
                {
                    $wheres[] = $subWhere;
                }
            }
            if($wheres) 
            {
                $aggMatches['$'.$logic] = $wheres;
            }
            return $aggMatches;
        } else {
            // TRUONG HOP FILTER DON
            // construct where
            $where = array();

            if(isset($filter["field"], $filter["operator"])) {
                $field = $filter["field"];

                if( isset($this->SPECIAL[$filter["operator"]]) ) {
                    // TRUONG HOP FILTER DAC BIET (null, empty)
                    if($filter["operator"] == "isempty") {
                        $condition_1 = array();
                        $condition_2 = array();
                        $condition_1[$field] = array('$exists' => false);
                        $condition_2[$field] = '';
                        $where = array();
                        $where['$or'] = [$condition_1, $condition_2];
                    } else $where[$field] = $this->SPECIAL[$filter["operator"]];

                } else {
                    $value = isset($filter["value"]) ? $filter["value"] : "";
                    // Check type
                    $type = "string";
                    if(isset($model[$field], $model[$field]["type"]))
                    {
                        $type = $model[$field]["type"];
                    }
                    else 
                    {
                        if(is_string($value)) {
                            $type = "string";
                        } elseif(is_numeric($value)) {
                            if(is_double($value)) {
                                $type = "double";
                            } else {
                                $type = "int";
                            }
                        } elseif(is_bool($value)) {
                            $type = "boolean";
                        } elseif(is_array($value)) {
                            $type = "array";
                        }
                    }

                    switch ($type) {
                        // String filter
                        case "string": default:
                            $mode = '';
                            if(!empty($filter["ignoreCase"])) 
                                $mode = 'i';
                            switch ($filter["operator"]) {
                                case 'eq':
                                    $where[$field] = array('$eq' => $value);
                                    break;
                                case "neq":
                                    $where[$field] = array('$ne' => $value);
                                    break;
                                case 'contains':
                                    $where[$field] = array('$regex' => $value, '$options' => $mode);
                                    break;
                                case 'doesnotcontain':
                                    $where[$field] = array('$regex' => '^((?!' . $value . ').)*$', '$options' => $mode);
                                    break;
                                case 'startswith':
                                    $where[$field] = array('$regex' => '^' . $value, '$options' => $mode);
                                    break;
                                case 'endswith':
                                    $where[$field] = array('$regex' => $value . '$', '$options' => $mode);
                                    break;
                                case 'in':
                                    $where[$field] = array('$in' => $value);
                                    break;
                                default:
                                    $mongoOperation = '$' . $filter["operator"];
                                    $where[$field] = array($mongoOperation => $value);
                                    break;
                            }
                            break;
                            
                        // Boolean filter
                        case "boolean":
                            $mongoOperation = $this->OPERATORS[$filter["operator"]];
                            $where[$field] = array();
                            $where[$field][$mongoOperation] = (boolean) ($value == "true");
                            break;

                        // Timestamp filter
                        case "timestamp":
                            $mongoOperation = $this->OPERATORS[$filter["operator"]];
                            $where[$field] = array();
                            $timeString = preg_replace('/\([^)]*\)/', '', $value);
                            if(!$timeString) throw new Exception("Maybe time value format wrong");
                            // Oanh them ngay 22/10/19
                            if (strpos($value, 'Z') === false) {
                                $where[$field][$mongoOperation] = strtotime($timeString);
                            }
                            else {
                                $where[$field][$mongoOperation] = strtotime($timeString) - date('Z');
                            }
                            break;

                        // Datetime filter
                        case "datetime":
                            $mongoOperation = $this->OPERATORS[$filter["operator"]];
                            $where[$field] = array();
                            $timeString = preg_replace('/\([^)]*\)/', '', $value);
                            if(!$timeString) throw new Exception("Maybe time value format wrong");
                            $where[$field][$mongoOperation] = new MongoDB\BSON\UTCDateTime(strtotime($timeString) * 1000);
                            break;

                        // Int filter
                        case "int":
                            $mongoOperation = $this->OPERATORS[$filter["operator"]];
                            $where[$field] = array();
                            $where[$field][$mongoOperation] = (int) $value;
                            break;

                        // Double filter
                        case "double":
                            $mongoOperation = $this->OPERATORS[$filter["operator"]];
                            $where[$field] = array();
                            $where[$field][$mongoOperation] = (double) $value;
                            break;

                        // Object Id filter
                        case "ObjectId":
                            $mongoOperation = $this->OPERATORS[$filter["operator"]];
                            $where[$field] = array();
                            $where[$field][$mongoOperation] = new MongoDB\BSON\ObjectId($value);
                            break;

                        // Array filter
                        case "array": case "arrayObject": case "arrayPhone":
                            if( isset($this->OPERATORS[$filter["operator"]]) ) {
                                $mongoOperation = $this->OPERATORS[$filter["operator"]];
                            } else $mongoOperation = '$in';
                            $where[$field] = array();
                            $where[$field][$mongoOperation] = (array) $value;
                            break;
                    } 
                }
            }
            return $where;
        }
    }
}