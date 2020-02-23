<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customer extends WFF_Controller
{

    private $collection = "Customer";

    public function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
    }

    public function upsert($key_field, $value)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data[$key_field])) {
                throw new Exception("Lack of " . $key_field, 401);
            }
            $value = urldecode($value);
            $data["createdBy"] = $this->session->userdata("extension");
            $result = $this->crud->where([$key_field => $value])->update($this->collection, ['$set' => $data], ["upsert" => true]);
            $doc = $this->crud->where([$key_field => $value])->getOne($this->collection);
            echo json_encode(array("status" => $result ? 1 : 0, "data" => [$doc]));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}