<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class livechat_webhook_hander_omnisales extends CI_Controller {
	// protected $username;	
 	function __construct() {
        parent::__construct();
        $this->load->config('worldui');
        //     $this->load->model("models_chat/chat_model");
        $this->load->model('models_chat/wfpbx_model');
        // $this->omni_webhook_socket_url = $this->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/chat';
        // $this->omni_webhook_noifi_createroom = $this->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/loadnewroom';
        $this->OMNI_SOCKET_LIVECHAT_REMOTE = $this->config->item('OMNI_SOCKET_LIVECHAT_REMOTE');
        $this->DIR_UPLOAD_LIVECHAT_REMOTE = '/var/www/worldfone4x_kim_tientran/worldfone4x/upload/livechat/sdk';
        $this->URL_TO_UPLOAD_LIVECHAT_REMOTE = 'http://115.146.126.84/upload/livechat/sdk';
        // var_dump( site_url());
    }

    // public function index(){
    //     // $data = $_REQUEST;
    //     $data = json_decode(file_get_contents('php://input'));
    //     // var_dump($data);
    //     /*$f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in_omni.txt", "a+");
    //     fwrite($f, print_r($data, true));
    //     fclose($f);*/
    //     if (empty($data)) {
    //         exit();
    //     }

    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $data_put_to_queue = array(
    //             'webhook_type'  => 'livechat_remote',
    //             'webhook_method' => 'POST',
    //             'webhook_headers' => getallheaders(),
    //             'webhook_data'  => $data,
    //             'requestTimestamp'      => time(),
    //         );
    //         $data_put_to_queue = json_decode(json_encode($data_put_to_queue));

    //         $this->run($data_put_to_queue);

    //         echo json_encode(array( 'status'    => 0, 'errorMessage' => 'success' ));
    //         header('Content-Type: application/json');
    //         exit();
    //     }
    // }
    /*function run($data) { 
        $webhook_data = $data->webhook_data;
        
        $page_app_id = $webhook_data->page_id;
        $page_info = $this->mongo_db->where(array('page_id'=> $page_app_id, 'source' => 'livechat_remote'))->getOne('livechat_remote_pageapps');
        if (!empty($page_info)) {
            $page_id = $page_info['_id']->{'$id'};
        }else{
            return;
        }
        var_dump($page_id);
        var_dump($data);
        exit('12312');
        if ($webhook_data->trigger=='message') {
            if ($webhook_data->messages->type=='text') {
                $this->sendmessage_text($page_id,$data); 
            }elseif ($webhook_data->messages->type == 'image') {
                $this->sendmessage_image($page_id,$data); 
            }
              
        }
    }*/

    public function sendmessage_text(){
        // $data = $_REQUEST;
        /*var_dump($page_id);
        var_dump($data);
        exit('124');*/
        $data = json_decode(file_get_contents('php://input'));

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

   

            $webhook_data = $data->webhook_data;
            
            $page_app_id = $webhook_data->page_id;
            $page_info = $this->mongo_db->where(array('_id'=> new mongoId($page_app_id), 'source' => 'livechat_remote'))->getOne('livechat_remote_pageapps');
         

            if (!empty($page_info)) {
                $page_id = $page_info['_id']->{'$id'};
            }else{
                return;
            }

            
            // if ($webhook_data->trigger=='message') {
                $this->pullMsgText($page_id,$data);
            // }
   /*         echo json_encode(array( 'status'    => 0, 'errorMessage' => 'success' ));
            header('Content-Type: application/json');*/
        }
    }

    public function sendmessage_image(){

        $data = json_decode(file_get_contents('php://input'));

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

    private function pullMsgText($page_id, $data){
        $webhook_data = $data->webhook_data;
        $url = '';
        $type = 'text';
        /*$f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($webhook_data, true));
        fclose($f);*/
        $text = $webhook_data->messages->text;
        $timestamp = strtotime(date('Y-m-d H:i:s', $webhook_data->timestamp));

        // $sender_app_id = $webhook_data->messages->sender_id;


        $page_info = $this->getPageInfoById($page_id);
        
        // Check room và tạo room 

        $room_id = $this->checkandCreateRoom($webhook_data);
        // var_dump($room_id);
        if (empty($room_id)) {
            var_dump('false rui');
            return;
        }

        
    

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
            'sender_id' => $page_id,
            'recipient_id'  => $webhook_data->recipient->id,
            'sender_info' => array(
                //'name'        => !empty($name) ? $name : "Khách viếng thăm",
                'user_id'     => $page_id,
                // 'profile_pic' => '',
            ),
            'text' => $text,
            'url' => $url,
            'date_added' => $timestamp,
        );

        // var_dump($message_data);

        $this->mongo_db->insert('livechat_remote_chatMessages', $message_data);
        // var_dump($webhook_data);
        //chuyển _id people thành people_app_id
        $people_info = $this->mongo_db->where(array('_id'   => new mongoId($webhook_data->recipient->id) ))->getOne('livechat_remote_people');
        // var_dump($people_info);
        if (!empty($people_info)) {
            $people_app_id = $people_info['people_id'];
        }else{
            return;
        }
        // Socket bắn đi cho livechat remote
        $data_in_queue = array(
            'trigger'   => 'message',
            'page_id'   => $page_id,
            'page_info' => array(
                'page_id'  => $page_info['page_id'],
                'name'  => $page_info['name'],
            ),
            'recipient' => array(
                'id'    => $people_app_id,
            ),
            'sender' => array(
                'id'    => $page_id,
                'name'  => $page_info['name']
            ),
            // 'parent_user'   => $page_info['parent_user'],
            'messages' => array(
                // 'message_app_id' => $message_app_id,
                'text'=> $text,
                'timestamp'   => $timestamp,
                'sender_id'   => $page_id,
                'sender_info' => array(
                    'name'        => $page_info['name'],
                    'user_id'     => $page_id,
                    'profile_pic' => '',
                    // 'gender'      => $userInfo->data->userGender==1 ? 'male' : 'female',
                    // 'birthDate'   => $userInfo->data->birthDate
                ),
                'type' => $type,
                'url'  => $url,
                'source'=> array(
                    'type' => "livechat_remote",
                    'id'   => $page_id,
                ) 
            ),
            'timestamp' => $timestamp,
        );
        
        // var_dump($this->OMNI_SOCKET_LIVECHAT_REMOTE.'/sendmessage/text');
        // var_dump($data_in_queue);
        $this->sendUrl($this->OMNI_SOCKET_LIVECHAT_REMOTE.'/sendmessage/text', $data_in_queue);

        /*$f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($data, true));
        fclose($f);*/
        
    }

    private function pullMsgImage($page_id, $data){
        $webhook_data = $data->webhook_data;
        // var_dump($webhook_data);
        $type = 'image';
        /*$f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($webhook_data, true));
        fclose($f);*/
        $text = isset($webhook_data->messages->text) ? $webhook_data->messages->text : "";
        $url = $webhook_data->messages->url;
        $timestamp = strtotime(date('Y-m-d H:i:s', $webhook_data->timestamp));

        // $sender_app_id = $webhook_data->messages->sender_id;


        $page_info = $this->getPageInfoById($page_id);
        // var_dump($page_info);
        
        

        // Check room và tạo room 

        $room_id = $this->checkandCreateRoom($webhook_data);
        //var_dump($room_id);
        if (empty($room_id)) {
            var_dump('false rui');
            return;
        }

        
        $url = 'http://115.146.126.84'.$url;
        $url1 = $this->downloadFile($url);
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
            'sender_id' => $page_id,
            'recipient_id'  => $webhook_data->recipient->id,
            'sender_info' => array(
                //'name'        => !empty($name) ? $name : "Khách viếng thăm",
                'user_id'     => $page_id,
                // 'profile_pic' => '',
            ),
            'text' => $text,
            'url' => $url1,
            // 'url1'  => $url1,
            'date_added' => $timestamp,
        );

        // var_dump($message_data);

        $this->mongo_db->insert('livechat_remote_chatMessages', $message_data);

        //chuyển _id people thành people_app_id
        $people_info = $this->mongo_db->where(array('_id'   => new mongoId($webhook_data->recipient->id) ))->getOne('livechat_remote_people');
        if (!empty($people_info)) {
            $people_app_id = $people_info['people_id'];
        }else{
            return;
        }
        // Socket bắn đi cho livechat remote
        $data_in_queue = array(
            'trigger'   => 'message',
            'page_id'   => $page_id,
            'page_info' => array(
                'page_id'  => $page_info['page_id'],
                'name'  => $page_info['name'],
            ),
            'recipient' => array(
                'id'    => $people_app_id,
            ),
            'sender' => array(
                'id'    => $page_id,
                'name'  => $page_info['name']
            ),
            // 'parent_user'   => $page_info['parent_user'],
            'messages' => array(
                // 'message_app_id' => $message_app_id,
                'text'=> $text,
                'timestamp'   => $timestamp,
                'sender_id'   => $page_id,
                'sender_info' => array(
                    'name'        => $page_info['name'],
                    'user_id'     => $page_id,
                    'profile_pic' => '',
                    // 'gender'      => $userInfo->data->userGender==1 ? 'male' : 'female',
                    // 'birthDate'   => $userInfo->data->birthDate
                ),
                'type' => $type,
                'url' => $url1,
                'source'=> array(
                    'type' => "livechat_remote",
                    'id'   => $page_id,
                ) 
            ),
            'timestamp' => $timestamp,
        );
        

        $this->sendUrl($this->OMNI_SOCKET_LIVECHAT_REMOTE.'/sendmessage/image', $data_in_queue);

        /*$f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($data, true));
        fclose($f);*/
    }

    private function checkandCreateRoom($webhook_data){
        $page_id = $webhook_data->page_id;
        $room = $this->mongo_db->where(array('from.id' => $page_id, 'to.id' => $webhook_data->recipient->id, 'status' => 1))->getOne('livechat_remote_chatGroups');
        // var_dump(array('from.id' => $page_id, 'to.id' => $webhook_data->recipient->id, 'status' => 1));
        // var_dump($room);
        if (!empty($room)) {
            $room_id = $room['_id']->{'$id'};
            //Lưu lại active mới nhất
            $this->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('date_active' => time()))->update('livechat_remote_pageapps');
        } else {
            $room_id = "";
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
            // var_dump($people_info);
            $people_id = $people_info['_id']->{'$id'};
            $messages = [];
            if (!empty($people_info)) {
                $room_info = $this->mongo_db->where(array('to.id'  => $people_id, 'status' => 1 ))->getOne('livechat_remote_chatGroups');
                
                $agents = [];
                if ($room_info) {
                    $user_info = $this->mongo_db->where(array( 'username'   => $room_info['from']['id'] ))->getOne('users');
                    $messages_query = $this->mongo_db->where(array('room_id' => $room_info['_id']->{'$id'}))->order_by(array('date_added' => 1))->limit(50)->get('livechat_remote_chatMessages');
                    // var_dump($user_info);
                    // var_dump($messages_query);
                    foreach ($messages_query as $message) {             
                        $profile_pic = '';
                        if ($people_id!= $message['sender_id']) {
                            $sender_id = $message['sender_id'];
                            $name = $user_info['lastname'].' '.$user_info['firstname'];
                            $user_info = $this->mongo_db->where(array('username' => $message['sender_id'], 'status' => 1))->getOne('users');
                            $profile_pic = isset($user_info['profile_pic']) && !empty($user_info['profile_pic']) ? $user_info['profile_pic'] : 'https://omnisales.worldfone.vn/portal/assets/images/avatar_default.svg';
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
    }

    public function upload_gif(){
        exit('unsupported!');       
    }


    private function downloadFile($url, $foldername='', $toDir = '') {
        $toDir = $this->DIR_UPLOAD_LIVECHAT_REMOTE;
        $pathinfo = pathinfo($url);
        if (!isset($pathinfo['extension'])) {
            $pathinfo['extension'] = 'jpg';
        }
        // open file in rb mode
        if ($fp_remote = fopen($url, 'rb')) {
            if(!is_dir($toDir.$foldername."/")) {
                mkdir($toDir.$foldername."/");
            }
            $file_name = $this->random_string(50).'.'.$pathinfo['extension'];
            // local filename           
            $local_file = $toDir . $foldername."/" . $file_name;

            // var_dump($local_file);
             // read buffer, open in wb mode for writing
            if ($fp_local = fopen($local_file, 'wb')) {
                // read the file, buffer size 8k
                while ($buffer = fread($fp_remote, 8192)) {     
                    // write buffer in  local file
                    fwrite($fp_local, $buffer);
                }

                // close local
                fclose($fp_local);           
            } else{
                // could not open the local URL
                fclose($fp_remote);
                return false;    
            }

            // close remote
            fclose($fp_remote);

            return $this->URL_TO_UPLOAD_LIVECHAT_REMOTE./*'/'.$foldername.*/'/'.$file_name;
        } else{
            // could not open the remote URL
            return false;
        }
    }

    private function random_string($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        return $key;
    }

 	
 		
}



?>