<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Database extends WFF_Controller {

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        if(!$this->session->userdata("issysadmin")) exit();
        $this->username = $this->config->item("session_mongo_user");
        $this->password = $this->config->item("session_mongo_password");
    }

    function mongodump($db)
    {
        $path = APPPATH . "database";
        $db_path = $path . "/" . $db;
        if(is_dir($db_path)) {
            rename($db_path, $db_path . "_" . date("d-m-Y_H-i-s", filemtime($db_path)));
        }
        if($this->username) {
            $command = "mongodump --username {$this->username} --password {$this->password} --authenticationDatabase admin --out $path --db $db";
        } else {
            $command = "mongodump --out $path --db $db";
        }
        $result = exec($command);
        echo json_encode(array("status" => 1, "result" => $result));
    }

    function mongorestore($db)
    {
        $this->backup_db($db);
        $path = APPPATH . "database";
        $db_path = $path . "/" . $db;
        if($this->username) {
            $command = "mongorestore --username {$this->username} --password {$this->password} --authenticationDatabase admin --db $db $db_path --drop";
        } else {
            $command = "mongorestore --db $db $db_path --drop";
        }
        $result = exec($command);
        echo json_encode(array("status" => 1, "message" => "Restore success $db"));
    }

    function backup_db($db)
    {
        $time = time();
        $command = 'mongo '.$this->username.':'.$this->password.'@localhost:27017 --eval \'db.copyDatabase("'.$db.'", "'.$db.'_'.$time.'")\'';
        $output = exec($command);
        return $output;
    }

    function mongorestore_collection($db)
    {
        $srcCollection = $this->input->get("srcCollection");
        $desCollection = $this->input->get("desCollection");
        if(!$srcCollection || !$desCollection) exit();
        $drop = $this->input->get("drop");
        $path = APPPATH . "database";
        $db_path = $path . "/" . $db;
        $collection_path = $db_path . "/" . $srcCollection . ".bson";
        if($this->username) {
            $command = "mongorestore --username {$this->username} --password {$this->password} --authenticationDatabase admin --db $db --collection $desCollection $collection_path " . ($drop ? "--drop" : "");
        } else {
            $command = "mongorestore --db $db --collection $desCollection $collection_path " . ($drop ? "--drop" : "");
        }
        $result = exec($command);
        echo json_encode(array("status" => 1, "message" => "Restore success $db $desCollection"));
    }

    function collections()
    {
        $db = $this->input->get("db");
        $file = (int) $this->input->get("file");
        if(!$db) exit();
        if($file) {
            $path = APPPATH . "database/{$db}/";
            $list = array();
            if(is_dir($path)) {
                $items = array_diff(scandir($path), array('..', '.'));
                foreach ($items as $name) {
                    $file_path = $path . $name;
                    $file_info = new SplFileInfo($file_path);
                    $ext = $file_info->getExtension();
                    if($ext == "bson")
                        $list[] = array("name" => $file_info->getBasename(".bson"));
                }
            }
        } else {
            $this->load->library("mongo_db");
            $this->mongo_db->switch_db($db);
            $list = $this->mongo_db->command(["listCollections"=>1, "authorizedCollections"=> true, "nameOnly"=>true]);
        }
        echo json_encode(array("data" => $list, "total" => count($list)));
    }

    function data($database, $collection)
    {
        $this->load->library("mongo_db");
        $this->mongo_db->switch_db($database);
        $request = json_decode($this->input->get("q"), TRUE);

        // Kendo to aggregate
        $this->load->library("kendo_aggregate");
        $this->kendo_aggregate->set_kendo_query($request)->filtering();
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        // Get data
        $data_aggregate = $this->kendo_aggregate->sorting()->paging()->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($collection, $data_aggregate);
        // Result
        $response = array("data" => $data, "total" => $total);

        echo json_encode($response);
    }

    function delete($database, $collection, $id)
    {
        try {
            if($this->input->method() != "delete") throw new Exception("Wrong method", 1);
            
            $this->load->library("mongo_db");
            $this->mongo_db->switch_db($database);
            if(!$id) throw new Exception("Error Processing Request", 1);

            $result = $this->mongo_db->where_id($id)->delete($collection);
            echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function js()
    {
        $this->load->library("mongo_db");
        $jscode = $this->input->post("js");
        $result = $this->mongo_db->run($jscode);
        echo $result;
    }

    function list_indexes($database, $collection)
    {
        try {
            $this->load->library("mongo_db");
            $this->mongo_db->switch_db($database);
            $listIndexes = $this->mongo_db->list_indexes($collection);
            echo json_encode(array("data" => $listIndexes, "total" => count($listIndexes)));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function remove_index($database, $collection)
    {
        try {
            $name = $this->input->get("name");
            $this->load->library("mongo_db");
            $this->mongo_db->switch_db($database);
            $result = $this->mongo_db->remove_index($collection, $name);
            if(!$result) throw new Exception("Something error");
            echo json_encode(array("status" => !empty($result[0]["ok"]) ? 1 : 0));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function add_index($database, $collection)
    {
        try {
            $request = json_decode(file_get_contents('php://input'), TRUE);
            if(empty($request["keys"])) throw new Exception("Error Processing Request", 1);
            
            $this->load->library("mongo_db");
            $this->mongo_db->switch_db($database);

            $options = array();
            if(!empty($request["name"])) $options["name"] = $request["name"];
            $result = $this->mongo_db->add_index($collection, $request["keys"], $options);
            if(!$result) throw new Exception("Something error");
            echo json_encode(array("status" => !empty($result[0]["ok"]) ? 1 : 0));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function drop_collection($database)
    {
        try {
            $collection = $this->input->get("collection");
            if(!$database || !$collection) throw new Exception("Error Processing Request", 1);
            
            $this->load->library("mongo_db");
            $this->mongo_db->switch_db($database);

            $result = $this->mongo_db->drop_collection($collection);

            echo json_encode(array("status" => !empty($result[0]["ok"]) ? 1 : 0));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function export_document()
    {
        try {
            $db         = $this->input->post("db");
            $collection = $this->input->post("collection");
            $id         = $this->input->post("id");
            $path = APPPATH . "database/document/";
            if (!@file_exists($path)) { 
                @mkdir($path, 0644);
            }
            $file_name = "{$db}@{$collection}@{$id}.json";
            $file_path = $path . $file_name;
            if($this->username) {
                $command = "mongoexport --username {$this->username} --password {$this->password} --authenticationDatabase admin --db $db --collection $collection --query '{\"_id\":ObjectId(\"$id\")}' --out $file_path";
            } else {
                $command = "mongoexport --db $db --collection $collection --query '{\"_id\":ObjectId(\"$id\")}' --out $file_path";
            }
            $result = exec($command);
            echo json_encode(array("status" => 1, "message" => "Export success {$file_name}"));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function json_file_list($db, $collection)
    {
        try {
            $path = APPPATH . "database/document/";
            $files =  array_filter(scandir($path), function($v) use ($db, $collection) {
                return strpos($v, $db . "@" . $collection) === 0;
            });
            $data = [];
            foreach ($files as $key => $value) {
                $file_path = $path . $value;
                $file_info = new SplFileInfo($file_path);
                $data[] = array("file" => $value, "content" => file_get_contents($file_info), "time" => $file_info->getATime());
            }
            echo json_encode(array("status" => 1, "data" => $data, "total" => count($files)));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function import_document($delete = "")
    {
        try {
            $file         = $this->input->post("file");
            list($db, $collection, $id_with_ext_json) = explode("@", $file);
            $path = APPPATH . "database/document/";
            if (!@file_exists($path)) { 
                throw new Exception("Folder not exists");
            }
            $file_path = $path. $file;
            if (!@file_exists($file_path)) { 
                throw new Exception("File not exists");
            }
            if($this->username) {
                $command = "mongoimport --username {$this->username} --password {$this->password} --authenticationDatabase admin --db $db --collection $collection $file_path";
            } else {
                $command = "mongoimport --db $db --collection $collection $file_path";
            }
            $result = exec($command);
            if($delete)
                unlink($file_path);
            else rename($path . $file, $path . $db . "@" . $collection . "@imported.json");
            echo json_encode(array("status" => 1, "message" => "Import success"));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}