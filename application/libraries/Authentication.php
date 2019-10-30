<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * @property  Authentication class for authen check.
 */
class Authentication {

    // Private variables.  Do not change!
    private $WFF;
    private $time_cache = 60;
    private $auth_redirect = TRUE;
    private $config_collection = "ConfigType";
    private $use_model = TRUE;
    private $external_path = "externalcrm/";

    function __construct() {
        // Set the super object to a local variable for use later
        $this->WFF =& get_instance();
        $this->time_cache = $this->WFF->config->item("wff_time_cache");
        $this->auth_redirect = $this->WFF->config->item("wff_auth_redirect");
    }

    function authenticate_pbx($username, $password) {
        if( !$username ) throw new Exception("Username empty");
        $this->WFF->load->library("mongo_db");
        // Load db private
        $_db = $this->WFF->config->item("_mongo_db");
        $this->WFF->mongo_db->switch_db($_db);
        $config = $this->WFF->mongo_db->where(array("deleted" => array('$ne' => true)))
        ->getOne($this->config_collection);
        // Reset
        $this->WFF->mongo_db->switch_db();
        try {
        	if(!$config) throw new Exception("Not config");
        	//call webservice login
        	$secret = $config['secret_key'];
            $pbx_url = $config["pbx_url"];  
            $curl = curl_init();          
            curl_setopt_array($curl, array(
              CURLOPT_URL => $pbx_url . $this->external_path . "agentlogin.php",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
              CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Basic " .base64_encode("$username:$password"),
                "cache-control: no-cache",
                "content-type: application/json"
              ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if($err) throw new Exception("Curl error: $err");
            $responseObj = json_decode($response);
            if( !isset($responseObj->result) ) throw new Exception("Authentication error: $response");
            $this->create_login_session($responseObj->result);
			return TRUE;
        } catch (Exception $e) {
            $this->WFF->load->library("session");
        	$this->WFF->session->set_flashdata("error", $e->getMessage());
        	return FALSE;
        }
    }

    function authenticate_pbx_email($email) {
        $this->WFF->load->library("mongo_db");
        // Load db private
        $_db = $this->WFF->config->item("_mongo_db");
        $this->WFF->mongo_db->switch_db($_db);
        $config = $this->WFF->mongo_db->getOne($this->config_collection);
        // Reset
        $this->WFF->mongo_db->switch_db();
        try {
            if(!$config) throw new Exception("Not config");
            //call webservice login
            $secret = $config['secret_key'];
            $pbx_url = $config["pbx_url"]; 
            $curl = curl_init();          
            curl_setopt_array($curl, array(
              CURLOPT_URL => $pbx_url . $this->external_path . "agentloginemail.php",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Basic " .base64_encode("$secret:$email"),
                "cache-control: no-cache",
                "content-type: application/json"
              ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if($err) throw new Exception("Curl error: $err");
            $responseObj = json_decode($response);
            if( !isset($responseObj->result) ) throw new Exception("Authentication error: $response");
            $this->create_login_session($responseObj->result);
            return TRUE;
        } catch (Exception $e) {
            $this->WFF->load->library("session");
            $this->WFF->session->set_flashdata("error", $e->getMessage());
            return FALSE;
        }
    }

    function check_permissions() {
        $extension      = $this->WFF->session->userdata('extension');
        $my_session_id  = $this->WFF->session->userdata('my_session_id');
        $issysadmin     = $this->WFF->session->userdata("issysadmin");
        $type           = $this->WFF->session->userdata("type");

        $this->WFF->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        $this->WFF->config->load('env');
        $env = $this->WFF->config->item('v1');
        //$this->WFF->cache->delete($my_session_id . "_permissions"); // Use for Erase all permission cache
        if (!$permissions = $this->WFF->cache->get($my_session_id . "_permissions")) {
            if(!$this->use_model) {
                $query = http_build_query(array("issysadmin" => $issysadmin, "type" => $type));
                $vApiLocal = str_replace(base_url(), "http://127.0.0.1/", $env["vApi"]);
                $ch = curl_init("{$vApiLocal}permission/access/{$extension}?{$query}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                switch ($httpcode) {
                    case 200:
                        $permissions = json_decode($result, TRUE);
                        $this->WFF->cache->save($my_session_id . "_permissions", $permissions, $this->time_cache);
                        break;
                    
                    default:
                        exit("Can't connect Data Server");
                        break;
                }
            } else {
                $this->WFF->load->model("permission_model");
                $permissions = $this->WFF->permission_model->access();
                $this->WFF->cache->save($my_session_id . "_permissions", $permissions, $this->time_cache);
            }
        }

        //pre($permissions);
        $uri =  trim($this->WFF->uri->uri_string(), "/");

        $defaultUri = array("", "undefined");
        $check_flag = in_array($uri, $defaultUri);

        $allowUri = array("page");
        if(!$check_flag) {
            foreach ($allowUri as $value) {
                if(strpos($uri, $value) === 0) $check_flag = true;
                if($check_flag) break;
            }
        }

        $acc_permission = array(
            "name"      => "",
            "create"    => false,
            "update"    => false,
            "delete"    => false
        );
        $acc_actions = array();
        if(!$check_flag) {
            foreach ($permissions as $permission) {
                // Check view
                if(!empty($permission["uri"])) {
                    $permissionUri = trim($permission["uri"], "/");
                    if( strpos($uri, $permissionUri) === 0 ) {
                        $check_flag = TRUE;
                        $acc_actions = !empty($permission["actions"]) ? $permission["actions"] : [];
                        if(!empty($permission["name"])) 
                            $acc_permission["name"] = $permission["name"];
                        foreach (["create","update","delete"] as $value) {
                            if(!empty($permission[$value])) {
                                $acc_permission[$value] = TRUE;
                            }
                        }
                        break;
                    }
                }
                // Check api
                if(!empty($permission["apis"])) {
                    foreach ($permission["apis"] as $api) {
                        $api = trim($api, "/");
                        if( strpos($uri, $api) === 0 ) {
                            $check_flag = TRUE;
                            if(!empty($permission["actions"]) && is_array($permission["actions"])) {
                                $acc_actions = array_merge($acc_actions, $permission["actions"]);
                            }
                            if(!empty($permission["name"])) 
                                $acc_permission["name"] = $permission["name"];
                            foreach (["create","update","delete"] as $value) {
                                if(!empty($permission[$value])) {
                                    $acc_permission[$value] = TRUE;
                                }
                            }
                        }
                    }
                }
            }

            if(!$check_flag && !$issysadmin && $this->auth_redirect) {
                $this->WFF->load->library("mongo_private");
                // Load db private, write log unaccess
                $this->WFF->mongo_private->insert("Unaccess", 
                    array(
                        "uri"           => $uri, 
                        "extension"     => $extension, 
                        "permissions"   => $permissions,
                        "my_session_id" => $my_session_id,
                        "time"          => (new DateTime())->format('Y-m-d H:i:s')
                    )
                );
                // Redirect 403
                redirect(base_url("page/error/403"));
            }
        }
        
        unset($acc_permission["uri"], $acc_permission["module_id"], $acc_permission["view"], $acc_permission["apis"], $acc_permission["visible"]);
        $acc_permission["actions"]      = $acc_actions;
        $acc_permission["issysadmin"]   = $issysadmin;
        $acc_permission["isadmin"]      = $this->WFF->session->userdata("isadmin");
        $acc_permission["issupervisor"] = $this->WFF->session->userdata("issupervisor");
        return $acc_permission;
    }

    function check_login() {
        $action = $this->WFF->uri->uri_string();
        $user = $this->WFF->session->userdata('extension');
        //neu ma chua dang nhap,ma truy cap 1 controller khac login
        if(!$user && $action != 'page/signin')
        {
            $urlencode = urlencode(fix_current_url());
            redirect(base_url("page/signin?redirect={$urlencode}"));
        }
        //neu ma admin da dang nhap thi khong cho phep vao trang login nua.
        if($user && $action == 'page/signin')
        {
            redirect(base_url());
        }
        // 2 Truong hop con lai chay binh thuong
    }

    function get_nav() {
        $this->WFF->load->library("session");
        $this->WFF->config->load('env');
        $this->WFF->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        $env = $this->WFF->config->item('v1');

        $extension      = $this->WFF->session->userdata("extension");
        $my_session_id  = $this->WFF->session->userdata("my_session_id");
        $issysadmin     = $this->WFF->session->userdata("issysadmin");
        $type           = $this->WFF->session->userdata("type");
        $lang           = $this->WFF->session->userdata("language");

        if (!$nav = $this->WFF->cache->get($my_session_id . "_nav")) {
            if(!$this->use_model) {
                $query = http_build_query(array("issysadmin" => $issysadmin, "type" => $type, "lang" => $lang));
                $vApiLocal = str_replace(base_url(), "http://127.0.0.1/", $env["vApi"]);
                $ch = curl_init("{$vApiLocal}permission/nav/{$extension}?{$query}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                switch ($httpcode) {
                    case 200:
                        $nav = json_decode($result, TRUE);
                        $this->WFF->cache->save($my_session_id . "_nav", $nav, $this->time_cache);
                        break;
                    
                    default:
                        exit("Can't connect Data Server");
                        break;
                }
            } else {
                $this->WFF->load->model("permission_model");
                $nav = $this->WFF->permission_model->nav();
                $this->WFF->cache->save($my_session_id . "_nav", $nav, $this->time_cache);
            }
        }
        return $nav;
    }

    public function create_login_session($userrow) {
        $this->WFF->load->library("session");
        $session_id = $this->WFF->session->session_id;
        $signintime = (int) time();

        $type       = isset($userrow->callcenter_type) ? $userrow->callcenter_type : "";
        $typename   = isset($userrow->callcenter_name) ? $userrow->callcenter_name : "";
        $sub        = $type ? $type . "_" : "";

        $userdata = array(
            "user"          => $userrow->username,
            "issysadmin"    => (bool) (strpos($userrow->username, "sysadmin") === 0),
            "isadmin"       => (bool) (int) $userrow->isadmin,
            "issupervisor"  => (bool) (int) $userrow->issupervisor,
            "agentname"     => $userrow->agentname,
            "extension"     => $userrow->extension,
            "signintime"    => $signintime,
            "my_session_id" => $session_id,
            "type"          => $type,
            "typename"      => $typename
        );

        // Get preference in wff config. Custom here.
        $default_preference = $this->WFF->config->item("default_preference");
        
        $this->WFF->config->load('proui');
        $template = $this->WFF->config->item("template");
        
        if(isset($template["theme"]))
            $default_preference["theme"]            = $template["theme"];

        if(isset($template["page_preloader"]))
            $default_preference["page_preloader"]   = $template["page_preloader"];

        $this->WFF->load->library("mongo_private");

        $userpreference = $this->WFF->mongo_private->where(array("extension" => $userrow->extension))->getOne($sub."User");

        foreach ($default_preference as $key => $value) {
            $userdata[$key] = !empty($userpreference[$key]) ? $userpreference[$key] : $value;
        }

        $this->WFF->session->set_userdata($userdata);

        $logdata = array(
            "agentname"         =>  $userrow->agentname,
            "extension"         =>  $userrow->extension,
            "my_session_id"     =>  $session_id
        );
        // Sign log
        $this->WFF->load->model("agentsign_model");
        $this->WFF->agentsign_model->start($logdata);
        // Update user
        $this->WFF->mongo_private->where(array("extension" => $userrow->extension))
        ->update($sub."User", array('$set' => 
            array(
                "user"                  => $userdata["user"], 
                "issysadmin"            => $userdata["issysadmin"],
                "current_my_session_id" => $session_id,
                "last_signintime"       => $signintime
            )
        ));
    }

    function log_out()
    {
        $this->WFF->load->library("session");
        // Sign log
        $this->WFF->load->model("agentsign_model");
        $this->WFF->agentsign_model->end();

        $this->WFF->session->sess_destroy();
        redirect(base_url('page/signin'));
    }

    private function getIPAddress(){  
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
