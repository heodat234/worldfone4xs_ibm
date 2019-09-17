<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Notification_get_conversation  {
    private $WFF;
    private $arraykey;

    function __construct() {
        // $this->WFF->load->config('worldui');
        //     $this->WFF->load->model("models_chat/chat_model");
        $this->WFF =& get_instance();
        $this->WFF->load->model('models_chat/wfpbx_model');
        $this->WFF->omni_webhook_socket_url = $this->WFF->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/chat';
        $this->WFF->omni_webhook_noifi_createroom = $this->WFF->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/loadnewroom';

    }

    /*public function index() {
        print_r($this->WFF->omni_webhook_noifi_createroom);
    }*/

    public function index($data) {
        header('Content-Type: application/json');
        // $data = $_REQUEST;
        // $data = json_decode(file_get_contents('php://input'));

        /*echo "______________________________REUQEST1";
        var_dump($data);
        echo "______________________________1";
        echo "______________________________POST2";
        var_dump($_POST);
        echo "______________________________2";
        echo "______________________________input";
        var_dump(json_decode(file_get_contents('php://input')));
        echo "______________________________3";*/

        $f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($data, true));
        fclose($f);
        
        $data_return  = array();
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($data['trigger'] == 'comment') {
                    $this->WFF->addComment($data);
                } elseif ($data['trigger'] == 'message') {
                    if (!empty($data['messages']['is_echo'])) {
                        $this->WFF->addMsgEcho($data);
                    } else {
                        $this->WFF->addMsg($data);
                    }
                }/*elseif ($data['trigger'] == 'get_livechat_remote') {
                    $data_return = $this->WFF->get_livechat_remote();
                }*/
            }
            echo json_encode(array('status' => 0, 'data'    => $data_return,  'errorMessage' => 'Success'));
        } catch (Exception $ex) {
            echo json_encode(array('status' => 1,  'errorMessage' => $ex));
        }
        
    }

    private function addMsg($data) {
        /*$f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($data, true));
        fclose($f);*/
        $sender_id = $data['messages']['sender_id'];
        $page_id = $data['page_id'];
        $data['messages']['sender_info']['type'] = 'customer';

        $room = $this->WFF->mongo_db->where(array('to.user_id' => $sender_id, 'source' => $data['messages']['source']['type'], 'status' => 1))->getOne('chatGroups');
        
        $f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($room, true));
        fclose($f);

        if (!empty($room)) {
            $room_id = $room['_id']->{'$id'};
            //Lưu lại active mới nhất
            $this->WFF->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('date_active' => time()))->update('chatGroups');
        } else {
            $room_id = '';
            
            //Nếu message không nằm ở nhóm chat nào thì kiểm tra page thuộc quản lý của user nào để add notifi
           $pageapps = $this->WFF->mongo_db->where(array('id' => $page_id))->getOne('pageapps');
           
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

            $notification_data = array(
                'type'            => $data_type,
                'trigger'         => 'message',
                'line'            => $data_line,
                'source'          => $data['messages']['source']['type'],
                'page_id'         => $page_id,
                // 'group_id'        => $chatGroup_Manager['_id']->{'$id'},
                'sender_id'       => $sender_id,
                'title'           => $data['messages']['sender_info']['name'],
                'text'            => $data['messages']['text'],
                'sender_info'     => $data['messages']['sender_info'],
                'group_id'        => $group_id,
                // 'supervisor'      => $chatGroup_Manager['supervisor'],                    
                // 'supervisor_name' => $chatGroup_Manager["supervisor"],
                'date_added'      => $data['messages']['timestamp'],
            );
                
            $this->WFF->redirectNotify($notification_data);
            
            $room_group = $this->WFF->mongo_db->where(array('to.user_id' => $sender_id, 'source' => $data['messages']['source']['type'], 'status' => 1))->getOne('chatGroups');
            $room_id = $room_group["_id"]->{'$id'};
        }

        $room_update = $this->WFF->mongo_db->where(array("_id" => new MongoId($room_id)))->set("read_by", array())->update('chatGroups');

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
        $this->WFF->sendUrl($this->WFF->omni_webhook_socket_url, $message_data);
        
        /*$f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($message_data, true));
        fclose($f);*/

        $result = $this->WFF->mongo_db->insert('chatMessages', $message_data);

        $f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
        fwrite($f, print_r($result, true));
        fclose($f);

    }

    private function addComment($data) {
        $sender_id = $data['messages']['sender_id'];
        $page_id = $data['page_id'];
        $data['messages']['sender_info']['type'] = 'customer';
        $post_id = $data['messages']['details']['post_id'];
        $room = $this->WFF->mongo_db->where(array('to.details.post_id' => $post_id, 'to.user_id' => $sender_id, 'source' => 'facebook', 'status' => 1))->getOne('chatGroups');
        if (!empty($room)) {
            $room_id = $room['_id']->{'$id'};
            //Lưu lại active mới nhất
            $this->WFF->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('date_active' => time()))->update('chatGroups');
        } else {
            $room_id = '';
            //Nếu message không nằm ở nhóm chat nào thì kiểm tra page thuộc quản lý của user nào để add notifi
            $pageapps = $this->WFF->mongo_db->where(array('id' => $page_id))->getOne('pageapps');
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
            $supervisor = $this->WFF->mongo_db->where(array('_id' => new mongoId($group_id)))->getOne('chatGroup_Manager');

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
            
            $this->WFF->redirectNotify($notification_data);
            $room_group = $this->WFF->mongo_db->where(array('to.user_id' => $sender_id, 'source' => $data['messages']['source']['type'], 'status' => 1))->getOne('chatGroups');
            $room_id = $room_group["_id"]->{'$id'};
        }
        $room_update = $this->WFF->mongo_db->where(array("_id" => new MongoId($room_id)))->set("read_by", array())->update('chatGroups');

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

        $this->WFF->sendUrl($this->WFF->omni_webhook_socket_url, $message_data);
        $this->WFF->mongo_db->insert('chatMessages', $message_data);
    }
    
    private function addMsgEcho($data) {//Tin Nhắn được gởi từ page trên facebook
        try {

            if (isset($data['messages']['metadata']['id'])) {
                $message_id = $data['messages']['metadata']['id'];
                $message_info = $this->WFF->mongo_db->where(array('_id' => new mongoId($message_id)))->getOne('chatMessages');
            } else {
                $message_info = '';
            }

            if (empty($message_info)) {
                $recipient_id = $data['messages']['recipient_id'];
                $sender_id = $data['messages']['sender_id'];
                $page_id = $data['page_id'];

                $room = $this->WFF->mongo_db->where(array('to.user_id' => $recipient_id, 'source' => $data['messages']['source']['type'], 'status' => 1))->getOne('chatGroups');
                if (!empty($room)) {
                    $room_id = $room['_id']->{'$id'};
                    //Lưu lại active mới nhất   
                    $this->WFF->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('date_active' => time()))->update('chatGroups');
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
                $this->WFF->sendUrl($this->WFF->omni_webhook_socket_url, $message_data);
                $f = fopen("../worldfone4x/application/controllers/apis/webhooktest_in.txt", "a+");
                fwrite($f, print_r($message_data, true));
                fclose($f);
                $this->WFF->mongo_db->insert('chatMessages', $message_data);
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

    public function redirectNotify($notification_data) {
        $json = array();
        $line = $notification_data['line'];
        $type = $notification_data['type'];
        $sender_id = $notification_data['sender_id'];
        $source = $notification_data['source'];


        if ($line == "livechat" && $type == "new_livechat_chat") {

            $data_views = $this->WFF->mongo_db->where(array("type" => "new_livechat_chat", "sender_id" => $sender_id))->getOne('chatNotifi');

            $room_array = array(
                'page_id' => $notification_data['page_id'],
                'type' => "new_livechat_chat", //private/group
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
                'trigger' => $notification_data['trigger'],
                'source' => $source
            );

            //check tồn tại trong groups
            $data_room = $this->WFF->mongo_db->where(array("page_id" => $notification_data['page_id']))->getOne('chatGroups');

            $result = $this->WFF->mongo_db->insert('chatGroups', $room_array);
            $room_id = $result->{'$id'};
            $notification_data['room_id'] = $room_id;

            $f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($notification_data, true));
            fclose($f);

            $this->WFF->mongo_db->insert('chatNotifi', $notification_data);

            $this->WFF->NotifiCreateChatGroup($room_id);
            

        }
        if ($line == "livechat_remote" && $type == "new_livechat_remote") {

            $data_views = $this->WFF->mongo_db->where(array("type" => "new_livechat_remote", "sender_id" => $sender_id))->getOne('chatNotifi');

            $room_array = array(
                'user_id_create' => $notification_data["supervisor"],
                'page_id' => $notification_data['page_id'],
                'type' => "new_livechat_remote", //private/group
             
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
            $data_room = $this->WFF->mongo_db->where(array("page_id" => $notification_data['page_id']))->getOne('chatGroups');

            $result = $this->WFF->mongo_db->insert('chatGroups', $room_array);
            $room_id = $result->{'$id'};
            $notification_data['room_id'] = $room_id;

            $f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($notification_data, true));
            fclose($f);

            $this->WFF->mongo_db->insert('chatNotifi', $notification_data);

            $this->WFF->NotifiCreateChatGroup($room_id);


            
        }

        if ($line == "facebook" && $type == "new_facebook_chat") {

            $data_views = $this->WFF->mongo_db->where(array("type" => "new_facebook_chat", "sender_id" => $sender_id))->getOne('chatNotifi');

            $room_array = array(
                'user_id_create' => $notification_data["supervisor"],
                'page_id' => $notification_data['page_id'],
                'type' => "new_facebook_chat", //private/group
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
            $data_room = $this->WFF->mongo_db->where(array("page_id" => $notification_data['page_id']))->getOne('chatGroups');

            $result = $this->WFF->mongo_db->insert('chatGroups', $room_array);
            $room_id = $result->{'$id'};
            $notification_data['room_id'] = $room_id;

            $f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($notification_data, true));
            fclose($f);

            $this->WFF->mongo_db->insert('chatNotifi', $notification_data);

            $this->WFF->NotifiCreateChatGroup($room_id);


        }
        if ($type == "new_facebook_comment") {

            $data_views = $this->WFF->mongo_db->where(array("type" => "new_facebook_comment", "sender_id" => $sender_id))->getOne('chatNotifi');
            
            $room_array = array(
                'trigger' => $notification_data['trigger'],
                'user_id_create' => $notification_data["supervisor"],
                'page_id' => $notification_data['page_id'],
                'type' => "new_facebook_comment", //private/group
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
            $data_room = $this->WFF->mongo_db->where(array("page_id" => $notification_data['page_id']))->getOne('chatGroups');

            $result = $this->WFF->mongo_db->insert('chatGroups', $room_array);
            $room_id = $result->{'$id'};
            $notification_data['room_id'] = $room_id;

            $f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($notification_data, true));
            fclose($f);

            $this->WFF->mongo_db->insert('chatNotifi', $notification_data);

            $this->WFF->NotifiCreateChatGroup($room_id);
            
        }
        if ($type == "new_zalo_chat") {

            $data_views = $this->WFF->mongo_db->where(array("type" => "new_zalo_chat", "sender_id" => $sender_id))->getOne('chatNotifi');

            $room_array = array(
                'user_id_create' => $notification_data["supervisor"],
                'page_id' => $notification_data['page_id'],
                'type' => "new_zalo_chat", //private/group
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
            $data_room = $this->WFF->mongo_db->where(array("page_id" => $notification_data['page_id']))->getOne('chatGroups');

            $result = $this->WFF->mongo_db->insert('chatGroups', $room_array);
            $room_id = $result->{'$id'};
            $notification_data['room_id'] = $room_id;

            $f = fopen("/var/www/html/worldfone4x/application/controllers/apis/webhook_in.txt", "a+");
            fwrite($f, print_r($notification_data, true));
            fclose($f);

            $this->WFF->mongo_db->insert('chatNotifi', $notification_data);

            $this->WFF->NotifiCreateChatGroup($room_id);
        }
        
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source) {
        $this->WFF->mongo_db->where(array("sender_id" => $sender_id, "room_id" => ''))->set(array('room_id' => $room_id, 'source' => $source))->update_all('chatMessages');
    }

    public function NotifiCreateChatGroup($room_id) {

        $chat_room = array(
            "room_id" => $room_id
        );
        $response = $this->WFF->sendUrl($this->WFF->omni_webhook_noifi_createroom, $chat_room);
        /*$f = fopen("../worldfone4x/application/controllers/apis/webhooktest_noti.txt", "a+");
        fwrite($f, print_r($response, true));
        fclose($f);*/
        // return ;
    }
    /*public function get_livechat_remote(){
        try{
            $pageapps = $this->WFF->mongo_db->get('livechat_remote_pageapps');
            
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

    

}
