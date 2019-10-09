<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Memcachestats extends WFF_Controller {

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->memcache = new Memcache();
		$this->memcache->addServer('127.0.0.1', 11211, 1);
    }

    function read() {
    	$this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
		$list = array();
		$allSlabs = $this->memcache->getExtendedStats('slabs');
		//$items = $this->memcache->getExtendedStats('items');
		foreach($allSlabs as $server => $slabs) {
		    foreach($slabs AS $slabId => $slabMeta) {
		    	$slabId = (int) $slabId;
		    	if($slabId) {
			        $cdump = $this->memcache->getExtendedStats('cachedump', $slabId);
			        foreach($cdump AS $server => $entries) {
			            if($entries) {
			                foreach($entries AS $eName => $eData) {
			                    $list[] = array(
			                        'key' => $eName,
			                        'value' =>	$this->cache->get($eName),
			                        'metadata' => $eData
			                    );
			                }
			            }
			        }
		    	}
		    }
		}
		echo json_encode(array("data" => $list, "total" => count($list)));
    }

    function flush() {
    	$this->memcache->flush();
    	echo json_encode(array("status" => 1));
    }

    function delete() {
    	$key = $this->input->get("key");
    	$this->memcache->delete($key);
    	echo json_encode(array("status" => 1));
    }

    function stats() {
    	$status = $this->memcache->getStats();
    	$data = $this->dataStat($status);
    	echo json_encode($data);
    }

    private function dataStat($status) {
        $this->load->helper("server");
    	$percCacheHit=((real)$status ["get_hits"]/ (real)$status ["cmd_get"] *100); 
        $percCacheHit=round($percCacheHit,3); 
        $percCacheMiss=100-$percCacheHit; 
        $MBRead= getSymbolByQuantity($status["bytes_read"], 3); //round($status["bytes_read"]/(1024*1024), 3); 
        $MBWrite=getSymbolByQuantity($status["bytes_written"], 3);
        $MBSize=getSymbolByQuantity($status["limit_maxbytes"], 3); 
        $MBUsed=getSymbolByQuantity($status["bytes"], 3);
    	$data = array(
    		[
    			"key" => "Memcache Server version", 
    			"value" => $status["version"]
    		],
            [
                "key" => "Used/Total allowed", 
                "value" => $MBUsed . " / " . $MBSize
            ],
            [
                "key" => "Current number of items stored by the server",
                "value" => $status["curr_items"]
            ],
    		[
    			"key" => "Process id of this server process", 
    			"value" => $status["pid"]
    		],
    		[
    			"key" => "Number of seconds this server has been running", 
    			"value" => $status["uptime"]
    		],
    		[
    			"key" => "Accumulated user time for this process", 
    			"value" => $status["rusage_user"]
    		],
    		[
    			"key" => "Accumulated system time for this process", 
    			"value" => $status["rusage_system"]
    		],
    		[
    			"key" => "Total number of items stored by this server ever since it started", 
    			"value" => $status["total_items"]
    		],
    		[
    			"key" => "Number of open connections", 
    			"value" => $status["curr_connections"]
    		],
    		[
    			"key" => "Total number of connections opened since the server started running", 
    			"value" => $status["total_connections"]
    		],
    		[
    			"key" => "Number of connection structures allocated by the server", 
    			"value" => $status["connection_structures"]
    		],
    		[
    			"key" => "Cumulative number of retrieval requests", 
    			"value" => $status["cmd_get"]
    		],
    		[
    			"key" => "Cumulative number of storage requests", 
    			"value" => $status["cmd_set"]
    		],
    		[
    			"key" => "Number of keys that have been requested and found present", 
    			"value" => $status["get_hits"] . " ($percCacheHit%)"
    		],
    		[
    			"key" => "Number of items that have been requested and not found", 
    			"value" => $status["get_misses"] . " ($percCacheMiss%)"
    		],
    		[
    			"key" => "Total number of bytes read by this server from network", 
    			"value" => $MBRead
    		],
    		[
    			"key" => "Total number of bytes sent by this server to network", 
    			"value" => $MBWrite
    		],
    		[
    			"key" => "Number of valid items removed from cache to free memory for new items", 
    			"value" => $status ["evictions"]
    		]
    	);
    	return $data;
    }
}