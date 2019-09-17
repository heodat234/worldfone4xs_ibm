<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class livechat_webhook_hander_clients extends CI_Controller {
	// protected $username;	
 	function __construct() {
        parent::__construct();
        $this->load->config('worldui');
        //     $this->load->model("models_chat/chat_model");
        $this->load->model('models_chat/wfpbx_model');
        $this->omni_webhook_socket_url = $this->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/chat';
        $this->omni_webhook_notifi_createroom = $this->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/loadnewroom';
        $this->OMNI_WEBHOOK_LIVECHAT_REMOTE = $this->config->item('OMNI_WEBHOOK_LIVECHAT_REMOTE');
    }

    public function index(){
        $data = $_REQUEST;
        // var_dump($data);
       /* $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($data, true));
        fclose($f);*/
        if (empty($data)) {
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data_put_to_queue = array(
                'webhook_type'  => 'livechat_remote',
                'webhook_method' => 'POST',
                'webhook_headers' => getallheaders(),
                'webhook_data'  => $data,
                'requestTimestamp'      => time(),
            );
            $data_put_to_queue = json_decode(json_encode($data_put_to_queue));
            // $data_put_to_queue = (object)$data_put_to_queue;
            /*$data_put_to_queue = array_map(function($array){
                return (object)$array;
            }, $data_put_to_queue);*/
            // var_dump($data_put_to_queue);
            /*$f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($data_put_to_queue, true));
            fclose($f);*/
            $this->run($data_put_to_queue);
            // $queue->useTube('omni.handlemsg')->put(json_encode($data_put_to_queue), 0);
            echo json_encode(array( 'status'    => 0, 'errorMessage' => 'success' ));
            header('Content-Type: application/json');
            exit();
        }
    }
    function run($data) { 
        $webhook_data = $data->webhook_data;
        
        $page_app_id = $webhook_data->page_id;
        $page_info = $this->mongo_db->where(array('page_id'=> $page_app_id, 'source' => 'livechat_remote'))->getOne('livechat_remote_pageapps');
        // $data->webhook_data->page_name = $page_info['page_info']['name'];
        if (!empty($page_info)) {
            $page_id = $page_info['_id']->{'$id'};
        }else{
            return;
        }
        if (!isset($webhook_data->messages->type)) {
            $webhook_data->messages->type='text';
        }
        if ($webhook_data->trigger=='message') {
            if ($webhook_data->messages->type=='text') {
                $this->sendmessage_text($page_id,$data);
            }else{
                $this->sendmessage_image($page_id,$data);
            }
            

            
        }
    }

    public function sendmessage_text(){
        $data = $_REQUEST;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data_put_to_queue = array(
                'webhook_type'  => 'livechat_remote',
                'webhook_method' => 'POST',
                'webhook_headers' => getallheaders(),
                'webhook_data'  => $data,
                'requestTimestamp'      => time(),
            );
            $data_put_to_queue = json_decode(json_encode($data_put_to_queue));
            $data = $data_put_to_queue;// $this->run($data_put_to_queue);

            /*var_dump($data);
        
            $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($data, true));
            fclose($f);*/

            $webhook_data = $data->webhook_data;
            
            $page_app_id = $webhook_data->page_id;
            $page_info = $this->mongo_db->where(array('_id'=> new mongoId($page_app_id), 'source' => 'livechat_remote'))->getOne('livechat_remote_pageapps');
         

            if (!empty($page_info)) {
                $page_id = $page_info['_id']->{'$id'};
            }else{
                return;
            }

            $this->pullMsgText($page_id,$data);
        }
    }

    private function pullMsgText($page_id, $data){
            $webhook_data = $data->webhook_data;
            $url = '';
            $type = 'text';
            /*$f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($webhook_data, true));
            fclose($f);*/
            $text = $webhook_data->messages->text;
            $timestamp = strtotime(date('Y-m-d H:i:s', $webhook_data->timestamp));

            $sender_app_id = $webhook_data->messages->sender_id;


            $page_info = $this->getPageInfoById($page_id);
            // var_dump($page_info);
            
            $name = isset($webhook_data->messages->sender_info->name) && !empty($webhook_data->messages->sender_info->name) ? $webhook_data->messages->sender_info->name : '';
            $phone = '';
            $email = '';
            $address = '';

            $properties = array();
            if (isset($webhook_data->surveys) && !empty($webhook_data->surveys)) {
                $livechats_survey = $this->mongo_db->where(array('livechat_id'  => $page_id ))->getOne('livechats_survey');
                //var_dump($webhook_data->surveys);
                foreach ($webhook_data->surveys as $key => $value) {
                    if (in_array($key,array('name', 'phone', 'email', 'address'))) {
                        $$key = $value;
                    }
                }
            }

            // var_dump($webhook_data->properties);
            if (isset($webhook_data->properties) && !empty($webhook_data->properties)) {
                foreach ($webhook_data->properties as $property) {
                    $properties[] = array(
                        'name'  => $property->name,
                        'value' => $property->value,
                    );
                }
            }

            /*properties*/
            // Kiểm tra đã có people này chưa nếu chưa thì thêm vào database
            $people_info = $this->mongo_db->where(array( 'people_id' => $sender_app_id, 'page_id' => $page_id ))->getOne('livechat_remote_people');
            if (empty($people_info)) {            
                $people_data = array(
                    'source'      => 'livechat_remote',
                    // 'parent_user' => $page_info['parent_user'],
                    'people_id'   => $sender_app_id,
                    'page_id'     => $page_id,
                    'name'        => $name,
                    'phone'       => $phone,
                    'email'       => $email,
                    'address'     => $address,
                    'profile_pic' => '',
                    'locale'      => '',
                    'timezone'    => '',
                    'gender'      => '',
                    'properties'     => $properties,
                    'date_added'  => time(),
                );
                // var_dump($people_data);
                // exit('12345_livechat_remote_people');
                

                $people_insert = $this->mongo_db->insert('livechat_remote_people', $people_data);
                $sender_id = $people_insert->{'$id'};
                $people_info = $this->mongo_db->where(array( '_id' => new  mongoId($sender_id), 'page_id' => $page_id ))->getOne('livechat_remote_people');
            }else{
                $sender_id = $people_info['_id']->{'$id'};
            }

            // Check room và tạo room 

            $room_id = $this->checkandCreateRoom($webhook_data,$sender_id);

            
            // $sender_id = '';
            // Socket bắn đi cho omnisale
            //Xử lý xong bắn về cho https://webhook.worldfone.vn/omni/livechat_remote
            $data_in_queue = array(
                'trigger'   => 'message',
                'page_id'   => $page_id,
                'page_info' => array(
                    'page_id'  => $page_info['page_id'],
                    'name'  => $page_info['name'],
                ),
                'messages' => array(
                    // 'message_app_id' => $message_app_id,
                    'text'=> $text,
                    'sender_id'   => $sender_id,
                    'sender_info' => array(
                        'name'        => !empty($people_info['name']) ? $people_info['name'] : "Khách viếng thăm",
                        'user_id'     => $sender_id,
                        'profile_pic' => '',
                        'properties'  => isset($people_info['properties']) ? $people_info['properties'] : array(),
                    ),
                    'type' => $type,
                    'url'  => $url,
                    'source'=> array(
                        'type' => "livechat_remote",
                        'id'   => $page_id,
                    ) 
                ),
                'timestamp'   => $timestamp,
            );
            /*$f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($data, true));
            fclose($f);*/
            $this->sendUrl($this->OMNI_WEBHOOK_LIVECHAT_REMOTE, $data_in_queue);

            // Lưu vào db riêng của livechat_remote
            $message_data = array(
                'room_id'   => $room_id,
                'trigger' => 'message',
                'page_id'   => $page_id,
                'page_info' => array(
                    'page_id'  => $page_info['page_id'],
                    'name'  => $page_info['name'],
                ),
                'source' => 'livechat_remote',
                'type' => $type,
                'page_id' => $page_info['page_id'],
                'sender_id' => $sender_id,
                'sender_info' => array(
                    'name'        => !empty($name) ? $name : "Khách viếng thăm",
                    'user_id'     => $sender_id,
                    'profile_pic' => '',
                ),
                'text' => $text,
                'url' => $url,
                'date_added' => $timestamp,
            );

            $this->mongo_db->insert('livechat_remote_chatMessages', $message_data);
            
        }
    public function sendmessage_image(){
        $data = $_REQUEST;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data_put_to_queue = array(
                'webhook_type'  => 'livechat_remote',
                'webhook_method' => 'POST',
                'webhook_headers' => getallheaders(),
                'webhook_data'  => $data,
                'requestTimestamp'      => time(),
            );
            $data_put_to_queue = json_decode(json_encode($data_put_to_queue));
            $data = $data_put_to_queue;// $this->run($data_put_to_queue);

            /*var_dump($data);
        
            $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($data, true));
            fclose($f);*/

            $webhook_data = $data->webhook_data;
            
            $page_app_id = $webhook_data->page_id;
            $page_info = $this->mongo_db->where(array('_id'=> new mongoId($page_app_id), 'source' => 'livechat_remote'))->getOne('livechat_remote_pageapps');
         

            if (!empty($page_info)) {
                $page_id = $page_info['_id']->{'$id'};
            }else{
                return;
            }

            $this->pullMsgImage($page_id,$data);
        }
    }

    private function pullMsgImage($page_id, $data){
        $webhook_data = $data->webhook_data;
        
        $type = 'image';
        $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($webhook_data, true));
        fclose($f);
        $text = $webhook_data->messages->text;
        $url = $webhook_data->messages->url;
        $timestamp = strtotime(date('Y-m-d H:i:s', $webhook_data->timestamp));

        $sender_app_id = $webhook_data->messages->sender_id;


        $page_info = $this->getPageInfoById($page_id);
            // var_dump($page_info);

        $name = isset($webhook_data->messages->sender_info->name) && !empty($webhook_data->messages->sender_info->name) ? $webhook_data->messages->sender_info->name : '';
        $phone = '';
        $email = '';
        $address = '';

        $properties = array();
        if (isset($webhook_data->surveys) && !empty($webhook_data->surveys)) {
            $livechats_survey = $this->mongo_db->where(array('livechat_id'  => $page_id ))->getOne('livechats_survey');
                //var_dump($webhook_data->surveys);
            foreach ($webhook_data->surveys as $key => $value) {
                if (in_array($key,array('name', 'phone', 'email', 'address'))) {
                    $$key = $value;
                }
            }
        }

            // var_dump($webhook_data->properties);
        if (isset($webhook_data->properties) && !empty($webhook_data->properties)) {
            foreach ($webhook_data->properties as $property) {
                $properties[] = array(
                    'name'  => $property->name,
                    'value' => $property->value,
                );
            }
        }

        /*properties*/
            // Kiểm tra đã có people này chưa nếu chưa thì thêm vào database
        $people_info = $this->mongo_db->where(array( 'people_id' => $sender_app_id, 'page_id' => $page_id ))->getOne('livechat_remote_people');
        if (empty($people_info)) {            
            $people_data = array(
                'source'      => 'livechat_remote',
                    // 'parent_user' => $page_info['parent_user'],
                'people_id'   => $sender_app_id,
                'page_id'     => $page_id,
                'name'        => $name,
                'phone'       => $phone,
                'email'       => $email,
                'address'     => $address,
                'profile_pic' => '',
                'locale'      => '',
                'timezone'    => '',
                'gender'      => '',
                'properties'     => $properties,
                'date_added'  => time(),
            );
                // var_dump($people_data);
                // exit('12345_livechat_remote_people');


            $people_insert = $this->mongo_db->insert('livechat_remote_people', $people_data);
            $sender_id = $people_insert->{'$id'};
            $people_info = $this->mongo_db->where(array( '_id' => new  mongoId($sender_id), 'page_id' => $page_id ))->getOne('livechat_remote_people');
        }else{
            $sender_id = $people_info['_id']->{'$id'};
        }

            // Check room và tạo room 

        $room_id = $this->checkandCreateRoom($webhook_data,$sender_id);


            // $sender_id = '';
            // Socket bắn đi cho omnisale
            //Xử lý xong bắn về cho https://webhook.worldfone.vn/omni/livechat_remote
        $data_in_queue = array(
            'trigger'   => 'message',
            'page_id'   => $page_id,
            'page_info' => array(
                'page_id'  => $page_info['page_id'],
                'name'  => $page_info['name'],
            ),
            'messages' => array(
                    // 'message_app_id' => $message_app_id,
                'text'=> $text,
                'sender_id'   => $sender_id,
                'sender_info' => array(
                    'name'        => !empty($people_info['name']) ? $people_info['name'] : "Khách viếng thăm",
                    'user_id'     => $sender_id,
                    'profile_pic' => '',
                    'properties'  => isset($people_info['properties']) ? $people_info['properties'] : array(),
                ),
                'type' => $type,
                'href'  => $url,
                'source'=> array(
                    'type' => "livechat_remote",
                    'id'   => $page_id,
                ) 
            ),
            'timestamp'   => $timestamp,
        );
            /*$f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($data, true));
            fclose($f);*/
            $this->sendUrl($this->OMNI_WEBHOOK_LIVECHAT_REMOTE, $data_in_queue);

            // Lưu vào db riêng của livechat_remote
            $message_data = array(
                'room_id'   => $room_id,
                'trigger' => 'message',
                'page_id'   => $page_id,
                'page_info' => array(
                    'page_id'  => $page_info['page_id'],
                    'name'  => $page_info['name'],
                ),
                'source' => 'livechat_remote',
                'type' => $type,
                'page_id' => $page_info['page_id'],
                'sender_id' => $sender_id,
                'sender_info' => array(
                    'name'        => !empty($name) ? $name : "Khách viếng thăm",
                    'user_id'     => $sender_id,
                    'profile_pic' => '',
                ),
                'text' => $text,
                'url' => $url,
                'date_added' => $timestamp,
            );

            $this->mongo_db->insert('livechat_remote_chatMessages', $message_data);
            $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($data, true));
            fclose($f);
            
        }

    

    // private function pullMsgImage($page_id, $data){
    //     $data = $_REQUEST;
    //     var_dump($data);
    //     $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
    //     fwrite($f, print_r($data, true));
    //     fclose($f);
    // }

    private function checkandCreateRoom($webhook_data, $sender_id){
        $page_id = $webhook_data->page_id;
        $room = $this->mongo_db->where(array('to.id' => $sender_id, 'status' => 1))->getOne('livechat_remote_chatGroups');

        if (!empty($room)) {
            $room_id = $room['_id']->{'$id'};
                //Lưu lại active mới nhất
            $this->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('date_active' => time()))->update('livechat_remote_pageapps');
        } else {
            $room_array = array(
                'page_id' => $page_id,
                    'from' => array(
                        "id" => $page_id,
                    ),
                    'to' => array(
                        "id" => $sender_id,
                    ),
                    'date_active' => time(),
                    'date_added' => time(),
                    'status' => 1,
                    'source' => 'livechat_remote'
                );

            $result = $this->mongo_db->insert('livechat_remote_chatGroups', $room_array);
            $room_id = $result->{'$id'};
        }
        return $room_id;
    }   

    private function getPageInfoById($page_id){
        $page_info = $this->mongo_db->where(array('_id'=> new mongoId($page_id) ))->getOne('livechat_remote_pageapps');
        if (!empty($page_info)) {
            return array(
                'page_id'     => $page_id, 
                'name'        => $page_info['page_info']['name'],
                // 'parent_user' => $page_info['created_by'],

            );
        }else{
            return false;
        }
    }

    


/*    private function getwebhookUrl($_id){
        $pageapps_info = $this->mongo_db->where(array( '_id' => new mongoId($_id) ))->getOne('livechat_remote_pageapps');
        $user_parent = $pageapps_info['created_by'];
        $user_info = $this->mongo_db->where(array('username' =>$user_parent))->getOne('users');
        if (isset($user_info['webhook_url']) && !empty($user_info['webhook_url'])) {
            return $user_info['webhook_url'];
        }
    }*/

    // $this->sendUrl($this->omni_webhook_socket_url, $message_data);

    private function sendUrl($url, $data) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYHOST => 0, // don't verify ssl 
            CURLOPT_SSL_VERIFYPEER => false, //
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'cache-control: no-cache',
                'content-type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);
    }

    /*API LIVE CHAT REMOTE*/

    public function getlivechat(){
        $data = $_REQUEST;
        try{
            if (!isset($data['livechat_id']) || empty($data['livechat_id'])) {
                throw new Exception('livechat_id field is required');
            }

            if (!isset($data['st_token']) || empty($data['st_token'])) {
                throw new Exception('st_token field is required');
            }else{
                $st_token = $data['st_token'];
            }

            // var_dump($this->mongo_db->where(array('_id' => new mongoId($data['livechat_id']), 'source'   => 'livechat' ))->getOne('livechat_remote_pageapps'));
            $page_info = $this->mongo_db->where(array('_id' => new mongoId($data['livechat_id']), 'source'  => 'livechat_remote' ))->getOne('livechat_remote_pageapps');
            // var_dump($page_info);
            if (empty($page_info)) {
                throw new Exception('Livechat Empty');
            }

            /*if (!isset($data['website_url']) || empty($data['website_url'])) {
                throw new Exception('website_url not approver');
            }*/
            
            $survey_info = $this->mongo_db->where(array('livechat_id' => $data['livechat_id'] ))->getOne('livechats_remote_survey');
            foreach ($survey_info['data_field'] as $key => $value) {
                if (empty($value['check'])) {
                    unset($survey_info['data_field'][$key]);
                }
            }
            // var_dump($page_info);
            // var_dump($survey_info);
            $data_return = array_merge($page_info, $survey_info);
            $people_info = $this->mongo_db->where(array('people_id' => $st_token ))->getOne('livechat_remote_people');
            if (!empty($people_info)) {
                $people_info = array(
                    'name'    => $people_info['name'],
                    'phone'   => $people_info['phone'],
                    'email'   => $people_info['email'],
                    'address' => $people_info['address'],
                );
                $data_return = array_merge($data_return, array('people_info' => $people_info ));
            }else{
                
            }
            
            // var_dump($data_return);
            echo json_encode(array( 'error' => 0, 'message' => 'success','data' => $data_return ));
            header('Content-Type: application/json');

        }catch (Exception $ex) {
            echo json_encode(array( 'error' => 1, 'message' => $ex->getMessage() ));
            header('Content-Type: application/json');
            http_response_code(403);
            exit();
        }
    }

    public function getmessages(){
        $data = $_REQUEST;
        try{
            if (!isset($data['st_token']) || empty($data['st_token'])) {
                throw new Exception('st_token field is required');
            }else{
                $st_token = $data['st_token'];
            }


            $people_info = $this->mongo_db->where(array('people_id' => $st_token ))->getOne('livechat_remote_people');
            $people_id = $people_info['_id']->{'$id'};

            

            // var_dump($people_info);
            
            $messages = [];
            if (!empty($people_info)) {
                $room_info = $this->mongo_db->where(array('to.id'  => $people_id, 'status' => 1 ))->getOne('livechat_remote_chatGroups');
                $page_info = $this->mongo_db->where(array('page_id'=> $people_info['page_id'], 'source' => 'livechat_remote'))->getOne('livechat_remote_pageapps');
                if (!empty($page_info)) {
                    $page_id = $page_info['_id']->{'$id'};
                    $page_name = $page_info['page_info']['name'];
                }else{
                    throw new Exception('page_info is not found');
                }
                $agents = [];
                if ($room_info) {
                    // $user_info = $this->mongo_db->where(array( 'username'   => $room_info['from']['id'] ))->getOne('users');
                    $messages_query = $this->mongo_db->where(array('room_id' => $room_info['_id']->{'$id'}))->order_by(array('date_added' => 1))->limit(50)->get('livechat_remote_chatMessages');
                    // var_dump($user_info);
                    // var_dump($messages_query);
                    foreach ($messages_query as $message) {             
                        $profile_pic = '';
                        if ($people_id!= $message['sender_id']) {
                            $sender_id = $message['sender_id'];
                            $name = $page_name;
                            // $user_info = $this->mongo_db->where(array('username' => $message['sender_id'], 'status' => 1))->getOne('users');
                            $profile_pic = isset($page_info['page_info']['avatar']) && !empty($page_info['page_info']['avatar']) ? $page_info['page_info']['avatar'] : 'http://kim.worldfone.vn/assets/images/avatar_default.svg';
                        }else{
                            $sender_id = $st_token;
                            $name = $people_info['name'];
                        }
                        if ($message['type']=='text' && empty($message['text'])) {
                        }else{
                            // if ($message['_id']->{'$id'} =='5c1cd3c5bbe345d6043e8cac') {
                                // var_dump($message);
                                // $this->mongo_db->where(array( '_id'  => new mongoId($message['_id']->{'$id'})  ))->delete('chatMessages');
                            // }
                            $messages[] = array(
                                "id"    => $message['_id']->{'$id'},
                                'type'        => $message['type'],
                                'text'        => $message['text'],
                                'url'         => !empty($message['url']) ? $message['url'] : "",
                                'profile_pic' => $profile_pic,
                                'user_id'     => $sender_id,
                                'name'        => $name,
                                'timestamp'   => $message['date_added'],
                            );
                        }
                    }
                }
            }else{
                throw new Exception('people_info is not found');
            }
            
            header('Content-Type: application/json');
            echo json_encode(array( 'error' => 0, 'message' => 'success','data' => $messages ));
            
        }catch (Exception $ex) {
            header('Content-Type: application/json');
            echo json_encode(array( 'error' => 1, 'message' => $ex->getMessage() ));            
            http_response_code(403);
            exit();
        }
    }

    public function getagent(){
        
    }

    public function getpeople(){
        $data = $_REQUEST;
        try{
            if (!isset($data['user_id']) || empty($data['user_id'])) {
                throw new Exception('user_id field is required');
            }
            $people_info = $this->mongo_db->where(array('people_id' => (int)$data['user_id'], 'parent_user' => $this->app->getUserName() ))->getOne('people');
            if (empty($people_info)) {
                throw new Exception('Empty user');
            }
            
            $data_return = array(
                'page_id'   => $people_info['page_id'],
                'id'        => $people_info['_id']->{'$id'},
                'name'  => $people_info['name'],
                'phone' => $people_info['phone'],
                'email' => $people_info['email'],
                'address'   => $people_info['address'],
                'picture'   => $people_info['profile_pic'],

                
            );
            
            header('Content-Type: application/json');
            echo json_encode(array( 'error' => 0, 'message' => 'success','data' => $data_return ));

        }catch (Exception $ex) {
            
            header('Content-Type: application/json');
            echo json_encode(array( 'error' => 1, 'message' => $ex->getMessage() ));
            http_response_code(403);
            exit();
        }
    }

    public function getpeopleById($id){
        $data = $_REQUEST;
        try{
            if (!isset($data['user_id']) || empty($data['user_id'])) {
                throw new Exception('user_id field is required');
            }
            $people_info = $this->mongo_db->where(array('people_id' => (int)$data['user_id'], 'parent_user' => $this->app->getUserName() ))->getOne('people');
            if (empty($people_info)) {
                throw new Exception('Empty user');
            }
            
            $data_return = array(
                'page_id'   => $people_info['page_id'],
                'id'        => $people_info['_id']->{'$id'},
                'name'  => $people_info['name'],
                'phone' => $people_info['phone'],
                'email' => $people_info['email'],
                'address'   => $people_info['address'],
                'picture'   => $people_info['profile_pic'],

                
            );
            
            header('Content-Type: application/json');
            echo json_encode(array( 'error' => 0, 'message' => 'success','data' => $data_return ));

        }catch (Exception $ex) {
            
            header('Content-Type: application/json');
            echo json_encode(array( 'error' => 1, 'message' => $ex->getMessage() ));
            http_response_code(403);
            exit();
        }
    }

    public function upload_image(){
        $data = $_REQUEST;
        var_dump($data);
        $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($data, true));
        fclose($f);

        // basename($_FILES["fileToUpload"]["name"]);
        /*$target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                echo "File is not an image.";
                $uploadOk = 0;
            }
        }*/
    }

    public function upload_gif(){
        exit('unsupported!');       
    }



 	
 		
}



?>