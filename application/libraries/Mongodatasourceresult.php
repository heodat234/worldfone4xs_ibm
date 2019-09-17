<?php
/*
 * Copyright © 2014 South Telecom
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * @property  WFF_Controller $WFF  Instance of super object Controller.
 */
class Mongodatasourceresult {
    
    // Private variables.  Do not change! $this->WFF->mongo_db4x
    private $WFF;

    function __construct() {
        // Set the super object to a local variable for use later
        $this->WFF = & get_instance();
        $this->WFF->load->library("mongo_db4x");
    }

    
    private $operators = array(
        'eq' => '$eq',
        'gt' => '$gt',
        'gte' => '$gte',
        'lt' => '$lt',
        'lte' => '$lte',
        'neq' => '$ne',
        /*'isnotempty'=>'$exists'*/
    );
    
    private $aggregateFunctions = array(
        'average' => '$avg',
        'min' => '$min',
        'max' => '$max',
        'count' => '$sum',
        'sum' => '$sum'
    );

    protected function propertyNames($properties) {
        $names = array();

        foreach ($properties as $key => $value) {
            if (is_string($value)) {
                $names[] = $value;
            } else {
                $names[] = $key;
            }
        }

        return $names;
    }

    private function flatten(&$all, $filter) {
        if (isset($filter->filters)) {
            $filters = $filter->filters;

            for ($index = 0; $index < count($filters); $index++) {
                $this->flatten($all, $filters[$index]);
            }
        } else {
            $all[] = $filter;
        }
    }

    private function where($properties, $filter, $all) {
        if (isset($filter->filters)) {
            foreach ($filter->filters as $subfilter) {
                $subfilter->logic = $filter->logic;
                $this->where($properties, $subfilter, $all);
            }
            return;
        }

        $field = $filter->field;

        $propertyNames = $this->propertyNames($properties);
        
        if (in_array($field, $propertyNames)) {
            $type = "string";

            if (isset($properties[$field])) {
                $type = $properties[$field]['type'];
            } else if (array_key_exists($filter->operator, $this->operators)) {
                $type = "number";
            }

            if ($type == "string") {
                if ($filter->operator === 'neq') {
                    $arrayWhere = array($field => array('$not'=>array('$eq' => $filter->value)));
                    
                } elseif ($filter->operator === 'contains') {
                    $arrayWhere = array($field => array('$regex' => new MongoRegex('/' . $filter->value . '/i')));
                } elseif ($filter->operator === 'doesnotcontain') {
                    $arrayWhere = array($field => array('$regex' => new MongoRegex('/^((?!' . $filter->value . ').)*$/i')));
                } elseif ($filter->operator === 'startswith') {
                    $arrayWhere = array($field => array('$regex' => new MongoRegex('/^' . $filter->value . '/i')));
                } elseif ($filter->operator === 'endswith') {
                    $arrayWhere = array($field => array('$regex' => new MongoRegex('/' . $filter->value . '$/i')));
                } elseif ($filter->operator === 'in') {
                    $arrayWhere = array($field => array('$in' => $filter->value));
                } elseif ($filter->operator === 'isempty') {
                    // Dung add
                    $arrayWhere = array('$or' => array(
                            array($field => array('$exists' => false)), 
                            array($field => '')
                        )
                    );
                } elseif ($filter->operator === 'isnotempty') {
                    // Dung add
                    $arrayWhere = array('$and' => array(
                            array($field => array('$exists' => true)), 
                            array($field => array('$ne' => ''))
                        )
                    );
                } else {
                    $arrayWhere = array($field => array('$eq' => $filter->value));
                }
            } elseif ($type == "mongoid") {
                if ($filter->operator === 'eq') {
                    $arrayWhere = array($field => array('$eq' => new MongoId($filter->value)));
                } else {
                    $operator = $this->operators[$filter->operator];
                    $arrayWhere = array($field => array($operator => $filter->value));
                }
            } else {
                $operator = $this->operators[$filter->operator];
                $arrayWhere = array($field => array($operator => $filter->value));
                //$arrayWhere [$field] = array($operator => $filter->value);
            }

            if (isset($filter->logic) && ($filter->logic === 'or')) {
                $this->WFF->mongo_db4x->or_where($arrayWhere);
            } else {
                $this->WFF->mongo_db4x->where($arrayWhere);
            }
            
        }
    }

    private function filter($properties, $filter) {
        $all = array();
        $this->flatten($all, $filter);
        $this->where($properties, $filter, $all);
    }

    private function total($tableName, $properties, $request) {
        if (isset($request->filter)) {
            $this->filter($properties, $request->filter);
        }

        $total = $this->WFF->mongo_db4x->count($tableName);

        if(!$total) {
            if (isset($request->filter)) {
                $this->filter($properties, $request->filter);
            }
            $total = $this->WFF->mongo_db4x->count($tableName);
        }
        return (int) ($total);
    }

    private function mergeSortDescriptors($request) {
        $sort = isset($request->sort) && count($request->sort) ? $request->sort : array();
        $groups = isset($request->group) && count($request->group) ? $request->group : array();

        return array_merge($sort, $groups);
    }

    private function sort($propertyNames, $sort) {
        $count = count($sort);

        if ($count > 0) {
            $order = array();
            for ($index = 0; $index < $count; $index ++) {
                $field = $sort[$index]->field;
                if (in_array($field, $propertyNames)) {
                    $dir = 'ASC';
                    if ($sort[$index]->dir == 'desc') {
                        $dir = 'DESC';
                    }
                    $order[$field] = $dir;
                }
            }
        }

        return $order;
    }
    
    private function calculateAggregates($table, $aggregates, $request, $propertyNames) {

    
        if (count($aggregates) > 0) {
            $count = count($aggregates);
            $aggregateGroup = array('_id'=> null);
            
            
            for ($index = 0; $index < $count; $index++) {
                $aggregate = $aggregates[$index];
                $name = $this->aggregateFunctions[$aggregate->aggregate];
                if($aggregate->aggregate==='count')
                {
                    $aggregateGroup[$aggregate->field.'___'.$aggregate->aggregate] = array ('$sum'=>1);
                }else{
                    $aggregateGroup[$aggregate->field.'___'.$aggregate->aggregate] = array ($name=> '$'.$aggregate->field);
                }
            }
            
            if (isset($request->filter)) {
                $this->filter($propertyNames, $request->filter);
            }
            
            $this->WFF->mongo_db4x->group($aggregateGroup);
            
            $result = $this->WFF->mongo_db4x->aggregate($table);
            
            if(!empty($result[0])) return $this->convertAggregateResult($result[0]);
            else {
                foreach($aggregateGroup as $property => $value) {
                    $aggregateGroup[$property] = null;
                }
                return $this->convertAggregateResult($aggregateGroup);
            };
        }
        return (object)array();
    }

    private function convertAggregateResult($propertyNames) {
        $result = array();
        foreach ($propertyNames as $property => $value) {
            $split = explode('___', $property);
            if (count($split) == 2) {
                $field = $split[0];
                $function = $split[1];
                
                if (array_key_exists($field, $result)) {
                    $result[$field][$function] = $value;
                } else {
                    $result[$field] = array($function => $value);
                }
            }
        }
        

        return $result;
    }
    
    private function createGroup($field, $value, $hasSubgroups, $aggregates, $table, $request, $propertyNames) {
        if (count($aggregates) > 0) {
            $request = $this->addFilterToRequest($field, $value, $request);
            $propertyNames = $this->addFieldToProperties($field, $propertyNames);
        }

        $groupItem = array(
            'field' => $field,
            'aggregates' => $this->calculateAggregates($table, $aggregates, $request, $propertyNames),
            'hasSubgroups' => $hasSubgroups,
            'value' => $value,
            'items' => array()
        );

        return $groupItem;
    }
    
    private function groupBy($data, $groups, $table, $request, $propertyNames) {
        if (count($groups) > 0) {
            $field = $groups[0]->field;
            $count = count($data);
            $result = array();
            $value = $data[0][$field];
            $aggregates = isset($groups[0]->aggregates) ? $groups[0]->aggregates : array();

            $hasSubgroups = count($groups) > 1;
            
            $groupItem = $this->createGroup($field, $value, $hasSubgroups, $aggregates, $table, $request, $propertyNames);
            
            for ($index = 0; $index < $count; $index++) {
                $item = $data[$index];
                if ($item[$field] != $value) {
                    if (count($groups) > 1) {
                        $groupItem["items"] = $this->groupBy($groupItem["items"], array_slice($groups, 1), $table, $request, $propertyNames);
                    }

                    $result[] = $groupItem;

                    $groupItem = $this->createGroup($field, $data[$index][$field], $hasSubgroups, $aggregates, $table, $request, $propertyNames);
                    $value = $item[$field];
                }
                $groupItem["items"][] = $item;
            }

            if (count($groups) > 1) {
                $groupItem["items"] = $this->groupBy($groupItem["items"], array_slice($groups, 1), $table, $request, $propertyNames);
            }

            $result[] = $groupItem;

            return $result;
        }
        return array();
    }
    
    private function addFilterToRequest($field, $value, $request) {
        $filter = (object)array(
            'logic' => 'and',
            'filters' => array(
                (object)array(
                    'field' => $field,
                    'operator' => 'eq',
                    'value' => $value
                ))
            );

        if (isset($request->filter)) {
            $filter->filters[] = $request->filter;
        }

        return (object) array('filter' => $filter);
    }

    private function addFieldToProperties($field, $propertyNames) {
        if (!in_array($field, $propertyNames)) {
            $propertyNames[] = $field;
        }
        return $propertyNames;
    }

    private function group($data, $groups, $table, $request, $propertyNames) {
        if (count($data) > 0) {
            return $this->groupBy($data, $groups, $table, $request, $propertyNames);
        }
        return array();
    }

    public function read($table, $properties, $request = null) {
        //$this->WFF->mongo_db4x->insert('debugs', array('request' => $request));
        $result = array();
        
        if (isset($properties['DISTINCT'])) {
            if (isset($request->filter)) {
                $this->filter($properties, $request->filter);
            }
            $distinctArray = $this->WFF->mongo_db4x->distinct($table,$properties['DISTINCT']);
            $count = count($distinctArray);
            $data = array();
            for ($index = 0; $index < $count; $index++) {
                $item = $distinctArray[$index];
                $data[] = array($properties['DISTINCT'] => $item);
            }
            $result['total'] = count($data);
            $result['data'] = $data;
        } else {
            $propertyNames = $this->propertyNames($properties);
            $result['total'] = $this->total($table, $properties, $request);
                        
            if (isset($request->filter)) {

                $this->filter($properties, $request->filter);
            }

            $sort = $this->mergeSortDescriptors($request);

            if (count($sort) > 0) {
                $this->WFF->mongo_db4x->order_by($this->sort($propertyNames, $sort));
            }

            if (isset($request->skip) && isset($request->take)) {
                $this->WFF->mongo_db4x->limit($request->take)->offset($request->skip);
            }
            $this->WFF->mongo_db4x->select($propertyNames);
            $data = $this->WFF->mongo_db4x->get($table);
            if (isset($request->group) && count($request->group) > 0) {

            $data = $this->group($data, $request->group, $table, $request, $propertyNames);
                $result['groups'] = $data;
            } else {
                 $result['data'] = $data;
            }

            if (isset($request->aggregate)) {
                $result["aggregates"] = $this->calculateAggregates($table, $request->aggregate, $request, $propertyNames);
            }
        }
       
        return $result;
    }
}
