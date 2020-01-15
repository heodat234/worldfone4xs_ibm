<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Activity_log 
{
	private $collection = "Activity";
	private $uri_string_exceptions = [
		"undefined","js","public","upload","page","playback","api/v1/avatar",
		"api/v1/ping","api/v1/widget","template/nav","template/widget", 
		"api/v1/monitor","api/report/server","api/v1/group/getImageNameById"
	];

    public function __construct() 
    {
    }

    public function write() 
    {
    	try {
		    $CI =& get_instance();
		    if($CI->load->is_loaded('session') && $CI->session->userdata("my_session_id") && $CI->config->item("record_activity")) {
		        $CI->load->library("mongo_db");
		        $CI->mongo_db->switch_db();
		        $uri_string = rtrim($CI->uri->uri_string(), "/");

		        $check_flag = TRUE;

		        foreach ($this->uri_string_exceptions as $value) {
		        	if(strpos($uri_string, $value) === 0) {
		        		$check_flag = FALSE;
		        	}
		        }

		        if($check_flag) {
		        	$time = microtime(TRUE); 
		        	$directory = rtrim($CI->router->fetch_directory(), "/");
			        $class = $CI->router->fetch_class();
			        $function = $CI->router->fetch_method();
		        	$get = $CI->input->get();
		        	$input = file_get_contents('php://input');
		        	$post = $input ? null : $CI->input->post();

		        	foreach (["get", "post", "input"] as $var) {
		        		$$var = $$var ? $$var : null; 
		        	}

		        	$method = $CI->input->method();

		        	$replace_str = $directory . "/" . $class . "/" . (($directory != "api/restful" && $function != "index") ? $function : "");
		        	$replace_str = trim($replace_str, "/");
		        	$param_string = trim(str_replace($replace_str, "", $uri_string), "/");
		        	$params = $param_string ? explode("/", $param_string) : null;
		        	$my_session_id = $CI->session->userdata("my_session_id");
		        	$extension = $CI->session->userdata("extension");
		        	$elapsed_time = (double) $CI->benchmark->elapsed_time("total_execution_time_start", "total_execution_time_end");
		        	$memory_usage = ($usage = memory_get_usage()) != '' ? $usage : 0;

		        	// Add 07/12/2019
		        	$permission = isset($CI->data, $CI->data["permission"]) ? $CI->data["permission"] : null;

		        	$data = array(
			        	"directory"		=> $directory,
			        	"class"			=> $class,
			        	"function"		=> $function,
			        	"uri"			=> $uri_string,
			        	"method"		=> $method,
			        	"params"		=> $params,
			        	"get_data"		=> $get,
			        	"post_data"		=> $post,
			        	"input"			=> $input,
			        	"elapsed_time"	=> $elapsed_time,
			        	"memory_usage"	=> $memory_usage,
			        	"permission"	=> $permission,
			        	"createdAt"		=> $time,
			        );

		        	if(!isset($_SERVER['HTTP_CURRENTURI'])) 
		        	{
		        		// View or ajax test
		        		$data["my_session_id"] 	= $my_session_id;
		        		$data["extension"]		= $extension;
		        		$data["agentname"]		= $CI->session->userdata("agentname");
		        		$CI->mongo_db->insert($this->collection, $data);

		        		// Change to log
		        		file_get_contents(base_url("api/wf4x/activity/moveToLog"));
		        	} 
		        	else 
		        	{
		        		// Ajax from page
		        		$where = array(
		        			"my_session_id"	=> $my_session_id,
		        			"extension" 	=> $extension,
		        			"uri"			=> $_SERVER['HTTP_CURRENTURI']
		        		);
		        		$doc = $CI->mongo_db->where($where)
		        		->order_by(array("createdAt" => -1))->getOne($this->collection);

		        		if($doc) 
		        		{
		        			$CI->mongo_db->where_id($doc["id"])->update($this->collection, 
		        				array(
		        					'$push' => array("ajaxs" => $data), 
		        					'$inc' => array(
		        						"ajaxs_elapsed_time" => $elapsed_time,
		        						"ajaxs_memory_usage" => $memory_usage
		        					)
		        				)
		        			);
		        		}
		        	}
		        }
		    }
        } catch (Exception $e) {
		    log_message("error", "Something wrong when write activity: " . $e->getMessage());
		}
    }
}
 
/* End of file Activity_log.php */
/* Location: ./application/hooks/Activity_log.php */