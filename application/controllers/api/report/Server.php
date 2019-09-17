<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Server extends WFF_Controller {

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->helper("server");
    }

    function loadavg()
    {
        //GET SERVER LOADS
        $loadresult = @exec('uptime');  
        preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",$loadresult,$avgs);
        $loadavg = array();
        if(count($avgs) == 4) {
            foreach ($avgs as $index => $value) {
                if($index != 0) {
                    $loadavg[] = (double) $value;
                }
            }
        } else $loadavg = sys_getloadavg();
        //GET SERVER UPTIME
        $uptime = explode(' up ', $loadresult);
        $uptime = explode(',', $uptime[1]);
        $runningfor = $uptime[0].', '.$uptime[1];
        $users = $uptime[2];

        echo json_encode(array("data" => $loadavg, "runningfor" => $runningfor, "users" => $users, "serverName" => $_SERVER['SERVER_ADDR']));
    }

    function ram()
    {
        $free_mem_arr = preg_split('/ +/', @exec('grep MemFree /proc/meminfo'));
        $cache_mem_arr = preg_split('/ +/', @exec('grep ^Cached /proc/meminfo'));
        $free_mem = $free_mem_arr[1] + $cache_mem_arr[1];
        echo json_encode(array("free" => $free_mem, "serverName" => $_SERVER['SERVER_ADDR']));
    }

    function topmem()
    {
        //-- The number of processes to display in Top RAM user
        $i = 5;
        $tom_mem_arr = array();
        
        exec("ps -e k-rss -ocomm=,rss= | head -n $i", $tom_mem_arr, $status);
        $top_mem = implode(' KiB <br/>', $tom_mem_arr );
        $top_mem = "<b>COMMAND\t\tResident memory</b><br/>" . $top_mem . " KiB";
        echo $top_mem;
    }

    function topcpu()
    {
        $i = 5;
        $top_cpu_use = array();
        exec("ps -e k-pcpu -ocomm=,pcpu= | head -n $i", $top_cpu_use, $status);
        $top_cpu = implode(' % <br/>', $top_cpu_use );
        $top_cpu = "<b>COMMAND\t\tCPU utilization </b><br/>" . $top_cpu. " %";
        echo $top_cpu;
    }

    function service()
    {
        $timeout = 1;
        $services = array();
        $services[] = array("port" => 80, "service" => "Internet Connection", "ip" => "google.com");
        $services[] = array("port" => 21, "service" => "FTP", "ip" => "");
        $services[] = array("port" => 22, "service" => "Open SSH", "ip" => "");
        $services[] = array("port" => 11211, "service" => "Memcache", "ip" => "");
        $services[] = array("port" => 3422, "service" => "TCP/UDP", "ip" => "");
        
        $data = array();
        foreach ($services  as $service) {
            if($service['ip']==""){
               $service['ip'] = "localhost";
            }
            $doc = array("name" => $service['service'], "port" => $service['port']);
            $fp = @fsockopen($service['ip'], $service['port'], $errno, $errstr, $timeout);
            if (!$fp) {
                $doc["status"] = 0;
            } else {
                $doc["status"] = 1;
                fclose($fp);
            }
            $data[] = $doc;
        }
        echo json_encode(array("data" => $data, "total" => count($data)));
    }

    function mongotop() {
        header('Content-type: text/plain');
        header('X-Accel-Buffering: no');
        try {
            //Change Execution Time
            $time = 1000;
            ini_set('max_execution_time', 0);
            $username = $this->config->item("session_mongo_user");
            $password = $this->config->item("session_mongo_password");
            $handle = popen("mongotop --host 127.0.0.1 -u $username -p $password --authenticationDatabase admin", 'r');
            $i = 0;
            $content = "";
            while (!feof($handle) && $i < $time + 1) {
                $i++; 
                $buffer = fgets($handle);
                echo $buffer;
                ob_flush();
                flush();
            }
            ob_end_flush();
            pclose($handle);
            exit();
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }

    function tail() {
        header('Content-type: text/plain');
        try {
            //Change Execution Time
            ini_set('max_execution_time', 600);
            $filepath   = $this->input->get("filepath");
            $amount     = (int) $this->input->get("amount");
            $stop       = $this->input->get("stop");
            if(!$filepath || !$amount) throw new Exception("Error Processing Request. Lack of input", 1);
            
            $handle = popen("tail -f {$filepath} 2>&1", 'r');
            $i = 0;
            while(!feof($handle)) {
                $buffer = fgets($handle);
                echo "$buffer\n";
                ob_flush();
                flush();
                $i++;
                if($i > $amount || $stop) {
                    break;
                }
            }
            pclose($handle);
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}