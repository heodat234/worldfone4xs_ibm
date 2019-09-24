<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Assign extends CI_Controller {

	private $collection = "User";
	private $sub_collection = "Telesalelist";
	private $jsondata_collection = "Jsondata";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->sub_collection = set_sub_collection($this->sub_collection);
		$this->crud->select_db($this->config->item("_mongo_db"));
	}

	function read($id_import)
	{
		$this->load->library("crud");
		$request = json_decode($this->input->get("q"), TRUE);

		$model = $this->crud->build_model($this->collection);
		$project = array();
		foreach ($model as $key => $value) {
			$project[$key] = 1;
		}
        // Kendo to aggregate
        $this->load->library("kendo_aggregate", $model);
     //    $lookup = array(
     //        '$lookup' => [
		   //          'from' => '2_Telesalelist',
		   //          'let' => [
		   //              'post_ext' => '$extension'
		   //          ],
		   //          'pipeline' => [
		   //              [
		   //                  '$match' => [
		   //                      '$expr' => [
		   //                          '$and' => [
		   //                              [
		   //                                  '$eq' => [
		   //                                      '$assign', '$$post_ext'
		   //                                  ]
		   //                              ],
		   //                              [
		   //                                  '$eq' => [
		   //                                      '$id_import', $id_import	
		   //                                  ]
		   //                              ]
		   //                          ]
		   //                      ]
		   //                  ]
		   //              ]
		   //          ],
		   //          'as' => 'assign_detail'
		   //      ]
    	// );
    	$lookup = array('$lookup' => array(
        		"from" => $this->sub_collection,
			    "localField" => "extension",
			    "foreignField" => "assign",
			    "as" => "assign_detail"
        	)
    	);
    	
    	$project = array(
    		'$project' => array_merge($project, array(
    			'count_detail'				=> array('$size' => '$assign_detail'),
    			"assign_detail.id_import"	=> 1
    		))
    	);
        $this->kendo_aggregate->set_kendo_query($request)->selecting()->adding($lookup, $project)->filtering();
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();//  pre($total_aggregate);
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        // Get data
        
        $data_aggregate = $this->kendo_aggregate->sorting()->paging()->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
        // Change foreign_key
       
	    $count_fiexd = 0;
        foreach ($data as &$doc) {
        	$doc['count_detail'] = 0;
        	foreach ($doc['assign_detail'] as $value) {
        		if ($value['id_import'] == $id_import) {
        			$doc['count_detail'] += 1;
        		}
        	}
        	$doc['id_import'] = $id_import;
        	$count_fiexd += $doc["count_detail"];

        }
        $request1 = array("filter"=>array("logic"=>"and","filters"=>[array("field"=>"id_import","operator"=>"eq","value"=>$id_import)]));

        $model = $this->crud->build_model($this->sub_collection);
        $this->load->library("kendo_aggregate", $model);  
        $this->kendo_aggregate->set_kendo_query($request1)->selecting()->filtering();
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->sub_collection, $total_aggregate);
        $total_data = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        $count_random = $total_data - $count_fiexd;

        foreach ($data as &$doc) {
        	$doc['count_random'] = $count_random;
        	$doc['checked'] = 0;
        }
        // Result
        $response = array("data" => $data, "total" => $total,"count_random" => $count_random);
		echo json_encode($response);
	}

	function detail($id)
	{
		$this->load->model("language_model");
		$response = $this->crud->where_id($id)->getOne($this->collection);
		$response = $this->language_model->translate($response);
		echo json_encode($response);
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["createdBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
	}

	function update()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$id = $data['id_import'];
		$match['id_import'] = $id;
		$match['assign'] = '';

        $response = $this->crud->read($this->sub_collection, $request = array(), array(), $match);
        $response = $response['data'];
		shuffle($response);
		
		for ($i=0; $i < $data['random']; $i++) { 
			$insert_data["assign"]	= $data['extension'];
			$this->crud->where_id($response[$i]['id'])->update($this->sub_collection, array('$set' => $insert_data));
		}
		echo json_encode(array("status" => 1, "data" => []));
	}

	function delete($id)
	{
		$permanent = TRUE;
		$result = $this->crud->where_id($id)->delete($this->collection, $permanent);
		if($result) {
			$this->crud->where_object_id("diallist_id", $id)->delete_all($this->sub_collection, $permanent);
		}
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}
}