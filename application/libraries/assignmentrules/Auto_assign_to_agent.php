<?php

    if (!defined('BASEPATH')) {
        exit('No direct script access allowed');
    }

    class auto_assign_to_agent extends CI_Controller {

        private $arraykey;

        function __construct() {
            parent::__construct();
            $this->load->config('worldui');
            //     $this->load->model("models_chat/chat_model");
            $this->load->model('models_chat/wfpbx_model');
            $this->omni_webhook_socket_url = $this->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/chat';
            $this->omni_webhook_noifi_createroom = $this->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/loadnewroom';
            
            $this->maxCusAssign = 5;
            /*$this->agents=[
                ['id' =>1,'curCusAssign' =>0,"arrCus"=>[]],
                ['id' =>2,'curCusAssign' =>0,"arrCus"=>[]],
                ['id' =>3,'curCusAssign' =>0,"arrCus"=>[]]
            ];
            $this->customers=[];
            for($i=1;$i<=13;$i++){
                $this->customers[]=["name"=>"test".$i,"phone"=>"123456789".$i];
            }*/

        }

        /*public function index() {
            print_r($this->omni_webhook_noifi_createroom);
        }*/

        public function index($data) {
            header('Content-Type: application/json');
            // $data = $_REQUEST;

            $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($data, true));
            fclose($f);
            
            $data_return  = array();

            try {
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if ($data['trigger'] == 'comment') {
                        $this->addComment($data);
                    } elseif ($data['trigger'] == 'message') {
                        if (!empty($data['messages']['is_echo'])) {
                            $this->addMsgEcho($data);
                        } else {
                            $this->addMsg($data);
                        }
                    }/*elseif ($data['trigger'] == 'get_livechat_remote') {
                        $data_return = $this->get_livechat_remote();
                    }*/
                }
                echo json_encode(array('status' => 0, 'data'    => $data_return,  'errorMessage' => 'Success'));
            } catch (Exception $ex) {
                echo json_encode(array('status' => 1,  'errorMessage' => $ex->getMessage()));
            }
        }

        private function addMsg($data) {
            $sender_id = $data['messages']['sender_id'];
            $page_id = $data['page_id'];
            $data['messages']['sender_info']['type'] = 'customer';

            $room = $this->mongo_db->where(array('to.user_id' => $sender_id, 'source' => $data['messages']['source']['type'], 'status' => 1))->getOne('chatGroups');
           
            if (!empty($room)) {
                $room_id = $room['_id']->{'$id'};
                //Lưu lại active mới nhất
                $this->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('date_active' => time()))->update('chatGroups');
            } else {
                $room_id = '';
        
                //Nếu message không nằm ở nhóm chat nào thì kiểm tra page thuộc quản lý của user nào để add notifi
               $pageapps = $this->mongo_db->where(array('id' => $page_id))->getOne('pageapps');
               
                if (!empty($pageapps)) {
                    $group_id = isset($pageapps['group_id']) ? $pageapps['group_id'] : '';
                } else {
                    $group_id = '';
                }
                if ($data['messages']['source']['type'] == 'messenger') {
                    $data_type = "new_facebook_chat";
                    $data_line = "facebook";
                } else if ($data['messages']['source']['type'] == 'livechat') {
                    $data_type = "new_livechat_chat";
                    $data_line = "livechat";
                } else if ($data['messages']['source']['type'] == 'zalo') {
                    $data_type = "new_zalo_chat";
                    $data_line = "zalo";
                } else if ($data['messages']['source']['type'] == 'livechat_remote') {
                    $data_type = "new_livechat_remote";
                    $data_line = "livechat_remote";
                }


                $chatGroup_Manager = $this->mongo_db->where(array('_id' => new mongoId($group_id)))->getOne('chatGroup_Manager');
                $group_agents = $chatGroup_Manager['agents'];
                $assign_to_user = '';
                if (empty($chatGroup_Manager)) {
                    throw new Exception('chatGroup_Manager Empty');
                }
                if (empty($chatGroup_Manager['agents'])) {
                    throw new Exception('chatGroup_Manager assigns Empty');
                }

                // lấy tất cả chat với from là user này với status của room = 1, 
                // function tính ra con số total của mổi thành viên list thành 1 array
                $group_agent_count_assign = $this->AgentsAssignTotal($group_id, $group_agents);
                usort($group_agent_count_assign, function($a, $b) {
                    return $a['number'] > $b['number'];
                });

                $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
                fwrite($f, print_r($group_agent_count_assign, true));
                fclose($f);

                if ($group_agent_count_assign[0]['number'] >= $this->maxCusAssign) {
                    // Xử lý cho vào hàng đợi
                }else{
                    //assign cho user đó
                    $assign_to_user = $group_agent_count_assign[0]['id'];
                }

                $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
                fwrite($f, print_r($assign_to_user, true));
                fclose($f);

                // var_dump($agents);

                // exit();

                $notification_data = array(
                    'type'            => $data_type,
                    'trigger'         => 'message',
                    'line'            => $data_line,
                    'source'          => $data['messages']['source']['type'],
                    'page_id'         => $page_id,
                    'group_id'        => $chatGroup_Manager['_id']->{'$id'},
                    'sender_id'       => $sender_id,
                    'title'           => $data['messages']['sender_info']['name'],
                    'text'            => $data['messages']['text'],
                    'assign_to_user'  => $assign_to_user,
                    'sender_info'     => $data['messages']['sender_info'],
                    'supervisor'      => $chatGroup_Manager['supervisor'],
                    'supervisor_name' => $chatGroup_Manager["supervisor"],
                    'date_added'      => $data['messages']['timestamp'],
                );
                    // $this->sendUrl($this->omni_webhook_socket_url, $message_data);
                    /*$f = fopen("../worldfone4x/application/controllers/apis/webhooktest.txt", "a+");
                    fwrite($f, print_r($notification_data, true));
                    fclose($f);*/
                // $this->mongo_db->insert('chatNotifi', $notification_data);
                $this->redirectNotify($notification_data);
                
                $room_group = $this->mongo_db->where(array('to.user_id' => $sender_id, 'source' => $data['messages']['source']['type'], 'status' => 1))->getOne('chatGroups');
                $room_id = $room_group["_id"]->{'$id'};
            }

            $room_update = $this->mongo_db->where(array("_id" => new MongoId($room_id)))->set("read_by", array())->update('chatGroups');

            $message_data = array(
                'trigger' => 'message',
                'source' => $data['messages']['source']['type'],
                'type' => $data['messages']['type'],
                'page_id' => $page_id,
                'sender_id' => $sender_id,
                'sender_info' => $data['messages']['sender_info'],
                'room_id' => $room_id,
                'text' => $data['messages']['text'],
                'url' => $data['messages']['url'],
                'date_added' => $data['messages']['timestamp'],
            );
            // Gởi cho socket giao diện
            $this->sendUrl($this->omni_webhook_socket_url, $message_data);

            $this->mongo_db->insert('chatMessages', $message_data);

        }

        private function addComment($data) {
            $sender_id = $data['messages']['sender_id'];
            $page_id = $data['page_id'];
            $data['messages']['sender_info']['type'] = 'customer';
            $post_id = $data['messages']['details']['post_id'];
            $room = $this->mongo_db->where(array('to.details.post_id' => $post_id, 'to.user_id' => $sender_id, 'source' => 'facebook', 'status' => 1))->getOne('chatGroups');
            if (!empty($room)) {
                $room_id = $room['_id']->{'$id'};
                //Lưu lại active mới nhất
                $this->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('date_active' => time()))->update('chatGroups');
            } else {
                $room_id = '';
                //Nếu message không nằm ở nhóm chat nào thì kiểm tra page thuộc quản lý của user nào để add notifi
                $pageapps = $this->mongo_db->where(array('id' => $page_id))->getOne('pageapps');
                if (!empty($pageapps)) {
                    $group_id = isset($pageapps['group_id']) ? $pageapps['group_id'] : '';
                    $username = isset($pageapps['username']) ? $pageapps['username'] : '';
                } else {
                    $group_id = '';
                    $username = '';
                }
                if ($data['messages']['source']['type'] == 'facebook') {
                    $data_type = "new_facebook_comment";
                    $data_line = "facebook";
                } else if ($data['messages']['source']['type'] == 'livechat') {
                    $data_type = "new_livechat_comment";
                    $data_line = "livechat";
                } else if ($data['messages']['source']['type'] == 'zalo') {
                    $data_type = "new_zalo_comment";
                    $data_line = "zalo";
                }
                $supervisor = $this->mongo_db->where(array('_id' => new mongoId($group_id)))->getOne('chatGroup_Manager');

                $notification_data = array(
                    'type' => $data_type,
                    'trigger' => 'comment',
                    'line' => $data_line,
                    'source' => $data['messages']['source']['type'],
                    'page_id' => $page_id,
                    'sender_id' => $sender_id,
                    'title' => $data['messages']['sender_info']['name'],
                    'text' => $data['messages']['text'],
                    'sender_info' => $data['messages']['sender_info'],
                    'details' => $data['messages']['details'],
                    'group_id' => $group_id,
                    'username' => $username,
                    'supervisor' => $supervisor['supervisor'],
                    'supervisor_id' => $supervisor['_id']->{'$id'},
                    'supervisor_name' => $supervisor["supervisor"],
                    'date_added' => $data['messages']['timestamp'],
                );
                // $this->mongo_db->insert('chatNotifi', $notification_data);
                $this->redirectNotify($notification_data);
                $room_group = $this->mongo_db->where(array('to.user_id' => $sender_id, 'source' => $data['messages']['source']['type'], 'status' => 1))->getOne('chatGroups');
                $room_id = $room_group["_id"]->{'$id'};
            }
            $room_update = $this->mongo_db->where(array("_id" => new MongoId($room_id)))->set("read_by", array())->update('chatGroups');

            $message_data = array(
                'trigger' => 'comment',
                'source' => 'facebook',
                'type' => $data['messages']['type'],
                'page_id' => $data['page_id'],
                'sender_id' => $data['messages']['sender_id'],
                'sender_info' => $data['messages']['sender_info'],
                'details' => $data['messages']['details'],
                'room_id' => $room_id,
                'comment_id' => $data['messages']['comment_id'],
                'text' => $data['messages']['text'],
                'date_added' => $data['messages']['timestamp'],
            );

            // Gởi cho socket giao diện

            $this->sendUrl($this->omni_webhook_socket_url, $message_data);
            $this->mongo_db->insert('chatMessages', $message_data);
        }
        
        private function addMsgEcho($data) {//Tin Nhắn được gởi từ page trên facebook
            try {

                if (isset($data['messages']['metadata']['id'])) {
                    $message_id = $data['messages']['metadata']['id'];
                    $message_info = $this->mongo_db->where(array('_id' => new mongoId($message_id)))->getOne('chatMessages');
                } else {
                    $message_info = '';
                }

                if (empty($message_info)) {
                    $recipient_id = $data['messages']['recipient_id'];
                    $sender_id = $data['messages']['sender_id'];
                    $page_id = $data['page_id'];

                    $room = $this->mongo_db->where(array('to.user_id' => $recipient_id, 'source' => $data['messages']['source']['type'], 'status' => 1))->getOne('chatGroups');
                    if (!empty($room)) {
                        $room_id = $room['_id']->{'$id'};
                        //Lưu lại active mới nhất   
                        $this->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('date_active' => time()))->update('chatGroups');
                    } else {
                        $room_id = '';
                    }

                    // Kiểm tra loại là link nhưng bị empty url
                    if ($data['messages']['type'] == 'link' && empty($data['messages']['url'])) {
                        header('Content-Type: application/json');
                        echo json_encode(array('status' => 1, 'errorMessage' => 'Type not support!'));
                        exit();
                    }
                    $data['messages']['sender_info']['type'] = 'page';
                    $message_data = array(
                        'trigger' => 'message',
                        'source' => $data['messages']['source']['type'],
                        'type' => $data['messages']['type'],
                        'page_id' => $page_id,
                        'sender_id' => $sender_id,
                        'recipient_id' => $recipient_id,
                        'sender_info' => $data['messages']['sender_info'],
                        'room_id' => $room_id,
                        'message_app_id' => $data['messages']['message_app_id'],
                        'text' => $data['messages']['text'],
                        'url' => $data['messages']['url'],
                        'date_added' => $data['messages']['timestamp'],
                    );

                    // Gởi cho socket giao diện
                    $this->sendUrl($this->omni_webhook_socket_url, $message_data);
                    $f = fopen("../worldfone4x/application/controllers/apis/webhooktest_in.txt", "a+");
                    fwrite($f, print_r($message_data, true));
                    fclose($f);
                    $this->mongo_db->insert('chatMessages', $message_data);
                }
            } catch (Exception $ex) {
                $f = fopen("../worldfone4x/application/controllers/apis/webhooktest_err.txt", "a+");
                fwrite($f, print_r($ex, true));
                fclose($f);
            }
        }

        

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

        /*public function sendComment() {
            $data = $_REQUEST;
            try {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!isset($data['page_id']) || empty($data['page_id'])) {
                        throw new Exception('page_id field is required');
                    }

                    if (!isset($data['room_id']) || empty($data['room_id'])) {
                        throw new Exception('room_id field is required');
                    }

                    if (!isset($data['text']) || empty($data['text'])) {
                        throw new Exception('text field is required');
                    }

                    $f = fopen("../worldfone4x/application/controllers/apis/webhooktest_add.txt", "a+");
                    fwrite($f, print_r($data, true));
                    fclose($f);
                    $group_info = $this->mongo_db->where(array('_id' => new mongoId($data['room_id'])))->getOne('chatGroups');
                    $object_id = $group_info['to']['comment_id'];
                    $url = 'https://webhook.worldfone.vn/omni/me/comments/' . $object_id;
                    $data_sending = array(
                        'page_id' => $data['page_id'],
                        'text' => $data['text'],
                    );
                    $this->sendUrl($url, $data_sending);
                }
            } catch (Exception $ex) {
                echo json_encode(array('status' => 1, 'errorMessage' => $ex->getMessage()));
            }
        }*/

        public function redirectNotify($notification_data) {
            /*$f = fopen("../worldfone4x/application/controllers/apis/webhooktest_noti.txt", "a+");
            fwrite($f, print_r($notification_data, true));
            fclose($f);*/
            $json = array();
            $line = $notification_data['line'];
            $type = $notification_data['type'];
            $sender_id = $notification_data['sender_id'];
            $source = $notification_data['source'];


            if ($line == "livechat" && $type == "new_livechat_chat") {

                $data_views = $this->mongo_db->where(array("type" => "new_livechat_chat", "sender_id" => $sender_id))->getOne('chatNotifi');

                $room_array = array(
                    'user_id_create' => '',
                    'page_id' => $notification_data['page_id'],
                    'group_id' => $notification_data['group_id'],
                    'type' => "new_livechat_chat", //private/group
                    'from' => array(
                        "id" => $notification_data["assign_to_user"],
                        "username" => $notification_data["assign_to_user"],
                        "name" => $notification_data["assign_to_user"],
                        "type" => "user",
                    ),
                    'to' => array(
                        "id" => $sender_id,
                        "username" => $notification_data['sender_info']['name'],
                        "type" => 'customer',
                        "user_id" => $sender_id,
                    ),
                    'group_user' => array(),
                    'group_name' => $notification_data['sender_info']['name'],
                    'date_active' => time(),
                    'date_added' => time(),
                    'status' => 1,
                    //them 
                    //'room_id' => $room_id,
                    'trigger' => $notification_data['trigger'],
                    'source' => $source
                );

                //check tồn tại trong groups
                $data_room = $this->mongo_db->where(array("page_id" => $notification_data['page_id']))->getOne('chatGroups');

                $result = $this->mongo_db->insert('chatGroups', $room_array);
                $room_id = $result->{'$id'};


                $this->NotifiCreateChatGroup($room_id);
                //sau đó chuyển những tin nhắn mới qua cho user đó
                $this->UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source, $notification_data['page_id']);
    //            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'page_id' => $data_views['page_id']))->delete_all('chatNotifi');
                // $json['redirect'] = base_url() . 'chat?invite=' . $room_id;
            }
            if ($line == "livechat_remote" && $type == "new_livechat_remote") {

                $data_views = $this->mongo_db->where(array("type" => "new_livechat_remote", "sender_id" => $sender_id))->getOne('chatNotifi');

                $room_array = array(
                    'user_id_create' => '',
                    'page_id' => $notification_data['page_id'],
                    'group_id' => $notification_data['group_id'],
                    'type' => "new_livechat_remote", //private/group
                    'from' => array(
                        "id" => $notification_data["assign_to_user"],
                        "username" => $notification_data["assign_to_user"],
                        "name" => $notification_data["assign_to_user"],
                        "type" => "user",
                    ),
                    'to' => array(
                        "id" => $sender_id,
                        "username" => $notification_data['sender_info']['name'],
                        "type" => "customer",
                        "user_id" => $sender_id,
                    ),
                    'group_user' => array(),
                    'group_name' => $notification_data['sender_info']['name'],
                    'date_active' => time(),
                    'date_added' => time(),
                    'status' => 1,
                    //them 
                    //'room_id' => $room_id,
                    'trigger' => $notification_data['trigger'],
                    'source' => $source
                );

                //check tồn tại trong groups
                $data_room = $this->mongo_db->where(array("page_id" => $notification_data['page_id']))->getOne('chatGroups');

                $result = $this->mongo_db->insert('chatGroups', $room_array);
                $room_id = $result->{'$id'};


                $this->NotifiCreateChatGroup($room_id);
                //sau đó chuyển những tin nhắn mới qua cho user đó
                $this->UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source, $notification_data['page_id']);
    //            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'page_id' => $data_views['page_id']))->delete_all('chatNotifi');
                // $json['redirect'] = base_url() . 'chat?invite=' . $room_id;
            }

            if ($line == "facebook" && $type == "new_facebook_chat") {

                $data_views = $this->mongo_db->where(array("type" => "new_facebook_chat", "sender_id" => $sender_id))->getOne('chatNotifi');

                $room_array = array(
                    'user_id_create' => '',
                    'page_id' => $notification_data['page_id'],
                    'group_id' => $notification_data['group_id'],
                    'type' => "new_facebook_chat", //private/group
                    'from' => array(
                        "id" => $notification_data["assign_to_user"],
                        "username" => $notification_data["assign_to_user"],
                        "name" => $notification_data["assign_to_user"],
                        "type" => "user",
                    ),
                    'to' => array(
                        "id" => $sender_id,
                        "username" => $notification_data['sender_info']['name'],
                        "type" => 'customer',
                        "user_id" => $sender_id,
                    ),
                    'group_user' => array(),
                    'group_name' => $notification_data['sender_info']['name'],
                    'date_active' => time(),
                    'date_added' => time(),
                    'status' => 1,
                    //them 
                    //'room_id' => $room_id,
                    'trigger' => $notification_data['trigger'],
                    'source' => $source
                );

                //check tồn tại trong groups
                $data_room = $this->mongo_db->where(array("page_id" => $notification_data['page_id']))->getOne('chatGroups');

                $result = $this->mongo_db->insert('chatGroups', $room_array);
                $room_id = $result->{'$id'};

                $this->NotifiCreateChatGroup($room_id);
                //sau đó chuyển những tin nhắn mới qua cho user đó
                $this->UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source, $notification_data['page_id']);
    //            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'page_id' => $data_views['page_id']))->delete_all('chatNotifi');
                // $json['redirect'] = base_url() . 'chat?invite=' . $room_id;
            }
            if ($type == "new_facebook_comment") {

                $data_views = $this->mongo_db->where(array("type" => "new_facebook_comment", "sender_id" => $sender_id))->getOne('chatNotifi');
                /*$f = fopen("../worldfone4x/application/controllers/apis/webhooktest_noti.txt", "a+");
            fwrite($f, print_r($data_views, true));
            fclose($f);*/
                $room_array = array(
                    'trigger' => $notification_data['trigger'],
                    'user_id_create' => '',
                    'page_id' => $notification_data['page_id'],
                    'group_id' => $notification_data['group_id'],
                    'type' => "new_facebook_comment", //private/group
                    'from' => array(
                        "id" => $notification_data["assign_to_user"],
                        "username" => $notification_data["assign_to_user"],
                        "name" => $notification_data["assign_to_user"],
                        "type" => "user",
                    ),
                    'to' => array(
                        "user_id" => $sender_id,
                        "name" => $notification_data['sender_info']['name'],
                        "id" => $sender_id,
                        "username" => $notification_data['sender_info']['name'],
                        "type" => 'customer',
                        "comment_id" => $notification_data['details']['comment_id'],
                        "parent_id" => $notification_data["supervisor"],
                        "post_id" => $notification_data['details']['post_id'],
                        "verb" => $notification_data["supervisor"],
                        "post_url" => $notification_data['details']['post_url'],
                    ),
                    'details' => array(
                        "comment_id" => $notification_data['details']['comment_id'],
                        "post_id" => $notification_data['details']['post_id'],
                        "post_url" => $notification_data['details']['post_url'],
                    ),
                    'source' => $source,
                    'group_user' => array(),
                    'group_name' => $notification_data['sender_info']['name'],
                    'date_active' => time(),
                    'date_added' => time(),
                    'status' => 1,
                );
                //check tồn tại trong groups
                $data_room = $this->mongo_db->where(array("page_id" => $notification_data['page_id'], "to.name" => $notification_data['sender_info']['name']))->getOne('chatGroups');
                // print_r($data_room);exit();
                if (empty($data_room)) {

                    $result = $this->mongo_db->insert('chatGroups', $room_array);
                    $room_id = $result->{'$id'};
                } else {
                    $room_id = $data_room['_id']->{'$id'};
                }

                $this->NotifiCreateChatGroup($room_id);
                //sau đó chuyển những tin nhắn mới qua cho user đó
                $this->UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source, $notification_data['page_id']);
    //            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'page_id' => $data_views['page_id']))->delete_all('chatNotifi');
                // $json['redirect'] = base_url() . 'chat?invite=' . $room_id;
                //}
            }
            if ($type == "new_zalo_chat") {

                $data_views = $this->mongo_db->where(array("type" => "new_zalo_chat", "sender_id" => $sender_id))->getOne('chatNotifi');

                $room_array = array(
                    'user_id_create' => '',
                    'page_id' => $notification_data['page_id'],
                    'group_id' => $notification_data['group_id'],
                    'type' => "new_zalo_chat", //private/group
                    'from' => array(
                        "id" => $notification_data["assign_to_user"],
                        "username" => $notification_data["assign_to_user"],
                        "name" => $notification_data["assign_to_user"],
                        "type" => "user",
                    ),
                    'to' => array(
                        "id" => $sender_id,
                        "username" => $notification_data['sender_info']['name'],
                        "type" => 'customer',
                        "user_id" => $sender_id,
                    ),
                    'group_user' => array(),
                    'group_name' => $notification_data['sender_info']['name'],
                    'date_active' => time(),
                    'date_added' => time(),
                    'status' => 1,
                    //them 
                    //'room_id' => $room_id,
                    'trigger' => $notification_data['trigger'],
                    'source' => $source
                );

                //check tồn tại trong groups
                $data_room = $this->mongo_db->where(array("page_id" => $notification_data['page_id']))->getOne('chatGroups');
                /*$f = fopen("../worldfone4x/application/controllers/apis/webhooktest_noti.txt", "a+");
                fwrite($f, print_r($data_room, true));
                fclose($f);*/
                $result = $this->mongo_db->insert('chatGroups', $room_array);
                $room_id = $result->{'$id'};

                $this->NotifiCreateChatGroup($room_id);
                //sau đó chuyển những tin nhắn mới qua cho user đó
                $this->UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source, $notification_data['page_id']);
    //            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'page_id' => $data_views['page_id']))->delete_all('chatNotifi');
                // $json['redirect'] = base_url() . 'chat?invite=' . $room_id;
            }
            
            header('Content-Type: application/json');
            echo json_encode($json);
        }

        public function UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source) {
            $this->mongo_db->where(array("sender_id" => $sender_id, "room_id" => ''))->set(array('room_id' => $room_id, 'source' => $source))->update_all('chatMessages');
        }

        public function NotifiCreateChatGroup($room_id) {

            $chat_room = array(
                "room_id" => $room_id
            );
            $response = $this->sendUrl($this->omni_webhook_noifi_createroom, $chat_room);
            /*$f = fopen("../worldfone4x/application/controllers/apis/webhooktest_noti.txt", "a+");
            fwrite($f, print_r($response, true));
            fclose($f);*/
            // return ;
        }
        /*public function get_livechat_remote(){
            try{
                $pageapps = $this->mongo_db->get('livechat_remote_pageapps');
                
                $data_return = array();
                foreach ($pageapps as $pageapp) {
                    $picture = '';

                    $data_return[] = array(
                        'id'        => $pageapp['_id']->{'$id'},
                        'source'    => $pageapp['source'],
                        'name'      => $pageapp['page_info']['name'],
                        'picture'   => $picture,
                        'status'    => $pageapp['status'],
                    );
                }
                // var_dump($data_return);
                return $data_return;
            }catch (Exception $ex) {
                return false;
            }


            // var_dump($pageapps);
        }*/


        /*public function assign($customers=[]){
            foreach ($customers as $key => $value) {
                $idx=$this->getMinCusAgent($this->agents);
                if($idx==$this->maxCusAssign){
                    break;
                }
                $this->agents[$idx]['curCusAssign']++;
                $this->agents[$idx]['arrCus'][]=$value;
                unset($this->customers[$key]);
            }
            print_r($this->agents[0]);
            print_r($this->agents[1]);
            print_r($this->agents[2]);
            print_r("overflow:");
            print_r($this->customers);
        }*/
        /*public function getMinCusAgent($agents=[]){
            $idx=0;
            $minCusAssign=$this->maxCusAssign;
            foreach ($agents as $key => $agent) {
                if($agent['curCusAssign']<$minCusAssign){
                    $idx=$key;
                    $minCusAssign=$agent['curCusAssign'];
                }
            }
            return ($minCusAssign<$this->maxCusAssign) ? $idx : $minCusAssign;
        }*/

        public function AgentsAssignTotal($group_id, $agents){
            $pipeline = array();
            $data_return = array();
            foreach ($agents as $agent) {
                $pipeline = [];
                $pipeline[] = array(
                    '$match' => array(
                        '$and' => array(
                            array('group_id' => (string)$group_id),
                            array('from.id' => (string)$agent),
                            array('status' => 1),
                        )
                    )
                );

                

                $results = $this->mongo_db->aggregate_pipeline("chatGroups", $pipeline);

                $f = fopen("/var/www/worldfone4x_kim_tientran/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
                fwrite($f, print_r($results, true));
                fclose($f);

                $data_return[] = array(
                    'id'        => $agent,
                    'number'    => count($results),
                );
                
            }
            return $data_return;
            
            
        }

        

    }
