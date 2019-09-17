<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
$url_libraries = __DIR__ . "/../../libraries";
require_once $url_libraries . "/omnisales-sdk/autoload.php";

use Omnisales\Omnisales;
use Omnisales\OmnisalesApp;

class chat extends WFF_Controller {

    private $access_token;
    private $Omnisales;

    public function __construct() {
        parent::__construct();
        $this->load->config('worldui');
        $this->load->model("models_chat/chat_model");
        $this->load->model('models_chat/wfpbx_model');
        $this->load->model('models_chat/facebook_model');
        $this->username = $this->session->userdata('user');
        $this->load->model('models_chat/user_model');
        $this->name = $this->session->userdata('name');
        $this->userextension    = $this->session->userdata('extension');
        $this->agentname        = $this->session->userdata('agentname');
        $this->username         = $this->session->userdata('user');
        $this->parent_user = $this->session->userdata('parent_user');
        $this->parent_id = $this->session->userdata('parent_id');

        $user_info = $this->user_model->getProfileByUserName($this->username);
        $this->avatar = $user_info['profile_pic'];

        $data_config = array(
            "app_id" => $this->config->item('omnisale_app_id'),
            "app_secret" => $this->config->item('omnisale_app_secret'),
        );

        $this->Omnisales = new Omnisales($data_config);

        $app = new OmnisalesApp($data_config['app_id'], $data_config['app_secret']);
        $this->access_token = $app->getAccessToken();
    }

    public function index($version = 'v1') {

        if ($version === 'v1') {
            // var_dump($this->getPostbyIdFacebook());
            $data['title'] = 'Chatbox';
            $data['template'] = $this->config->item('template');
            $data['template']['header'] = 'navbar-fixed-top';
            $data['template']['main_style'] = 'style-alt';
            $data['template']['footer'] = 'footer-fixed';
            $data['username'] = $this->username;
            $data['name'] = $this->name;
            $userdata = $this->session->userdata;
            // $data['is_admin'] = $userdata['isadmin'];
            // $data['issupervisor'] = $userdata['issupervisor'];
            $data['danhbas'] = $this->getDanhba();
            // var_dump($this->getNewMess());
            // exit('aa');
            // $data['agent_groups'] = $this->get_agent_group();
            $data['invite_id'] = $this->input->get('invite');

            $data['supervisors'] = $this->wfpbx_model->getAgent(0, 1, 0);


            // var_dump($data['agents']);
            $data['rooms'] = $this->loadRoom();
            // var_dump($data['rooms']);
            // $this->getlastMes($room_id);
            $data['room_join'] = array();
            foreach ($data['rooms'] as $room) {
                $data['room_join'][] = $room['room_id'];
            }

            $data['room_join'] = json_encode($data['room_join']);
            // var_dump($data['room_join']);
            // $data['check'] = $this->checkRoomUserExists();
            // var_dump($data['rooms']);

            $data['SERVER_NAME'] = $_SERVER['SERVER_NAME'];

            $this->load->view('templates/worldui/template_start', $data);
            $this->load->view('templates/worldui/page_head', $data);
           // $this->load->view('chatnodejs/chat');
            $this->load->view('templates/worldui/page_footer');
            $this->load->view('templates/worldui/template_end');
        }
    }

    public function getRooms() {
        $type = $this->input->get('type');
        $rooms = $this->loadRoom($type);
        header('Content-Type: application/json');
        //print_r(base_url('assets/images/avatar_default.jpg'));
        echo json_encode($rooms);
    }

    public function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
        $sort_col = array();
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }
        array_multisort($sort_col, $dir, $arr);
    }

    public function checkInvites() {
        // var_dump($this->username);
        $pipeline = array(
            /* array(
              '$match' => array('type' => 'private')
             */
            array(
                '$match' => array('array_invite' => $this->username),
            ),
        );
        $results = $this->mongo_db->aggregate_pipeline("chatnodejsInvite", $pipeline);
        // var_dump($results);
        if (!empty($results)) {
            foreach ($results as $result) {
                $data_array[] = array(
                    'id' => $result['_id']->{'$id'},
                    'title' => $result['title'],
                    'content' => $result['content'],
                    'sender_info' => $result['sender_info'],
                    'date_added' => date("H:i d/m/Y", $result['date_added']),
                        // 'content'        => $result['content'],
                );
            }

            echo json_encode($data_array);
        } else {
            echo '';
        }
    }

    public function deleteInvite() {
        $json = array();
        $room = $this->mongo_db->where(array("_id" => new MongoId($this->input->post('data_table_id'))))->delete_all('_chatnodejsInvite');
        $new_group_user = array();
        $json['success'] = 'success';
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function countMesUnReadByRoom($room_id, $user_id) {
        $unread = $this->mongo_db->where(array("room_id" => $room_id, "user_id" => $user_id))->get('_chatViews');
        return $unread;
    }

    public function countMesUnRead($user_id) {
        $unread = $this->mongo_db->where(array("user_id" => $user_id))->get('_chatViews');
        return $unread;
    }

    public function getNewMess() {
        $countMesUnRead = $this->countMesUnRead($this->username);
        $new_mes = array();
        foreach ($countMesUnRead as $mes) {
            /* if ($mes['line']=="message") {
              $line = "Facebook";
              }else{
              $line = "";
             */

            $new_mes[] = array(
                'room_id' => $mes['room_id'],
                'name' => $this->getNameRoom($mes['room_id']),
                'text' => $mes['mes_text'],
            );
        }

        return $new_mes;
    }

    public function ajaxGetNewMess() {
        $countMesUnRead = $this->countMesUnRead($this->username);
        $new_mes = array();
        foreach ($countMesUnRead as $mes) {
            $new_mes[] = array(
                'room_id' => $mes['room_id'],
                'name' => $this->getNameRoom($mes['room_id']),
                'text' => $mes['mes_text'],
            );
        }

        header('Content-Type: application/json');
        echo json_encode($new_mes);
    }

    public function ajaxGetNewNotify() {
        $pipeline = array(
            array(
                '$sort' => array("date_added" => -1),
            ),
        );

        $newNotify_query = $this->mongo_db->aggregate_pipeline('chatNotifi', $pipeline);
        $newNotify = array();
        $array_tam = array();
        $array_assigns = array();
        foreach ($newNotify_query as $noti) {
            if ($noti['source'] == 'transfer') {
                if (in_array($this->username, $noti['users'])) {
                    $user_info = $this->getUserInfoByUsername($noti['sender_id']);
                    if (!isset($array_assigns[$noti['source']][$noti['sender_id']][$noti['room_id']])) {
                        $newNotify[] = array(
                            'id' => $noti['_id']->{'$id'},
                            'avatar' => $user_info['profile_pic'],
                            'name' => $noti['title'],
                            'user_id' => $noti['sender_id'],
                            'text' => $noti['text'],
                            'room_id' => $noti['room_id'],
                            'date_added' => date('H:i d/m/Y'),
                        );
                        $array_assigns[$noti['source']][$noti['sender_id']][$noti['room_id']] = $noti['text'];
                    }
                }
            } elseif ($noti['source'] == 'transfer_success') {
                if (in_array($this->username, $noti['users'])) {
                    $user_info = $this->getUserInfoByUsername($noti['sender_id']);
                    //if (!isset($array_assigns[$noti['source']][$noti['sender_id']][$noti['room_id']])) {
                    $newNotify[] = array(
                        'id' => $noti['_id']->{'$id'},
                        'avatar' => $user_info['profile_pic'],
                        'name' => $noti['title'],
                        'user_id' => $noti['sender_id'],
                        'text' => $noti['text'],
                        'room_id' => $noti['room_id'],
                        'date_added' => date('H:i d/m/Y'),
                    );
                    $array_assigns[$noti['source']][$noti['sender_id']][$noti['room_id']] = $noti['text'];
                    //}
                }
            } elseif (!empty($noti['group_id'])) {
                $group_info = $this->mongo_db->where(array('_id' => new mongoId($noti['group_id'])))->getOne('groups');

                if ($group_info) {
                    $users = $group_info['users'];
                    if (in_array($this->username, $users) || $group_info['created_by'] == $this->username) {
                        // continue;
                    } else {
                        continue;
                    }
                }
                if (!isset($array_tam[$noti['source']][$noti['sender_id']])) {
                    if ($noti['source'] == "new_facebook_chat") {
                        $icon = '<img src ="' . base_url() . 'img/if_Messenger_2525.png"/>';
                    } elseif ($noti['source'] == "new_facebook_comment") {
                        $icon = '<img src ="' . base_url() . 'img/if_Facebook_comment.png"/>';
                    } elseif ($noti['source'] == "new_viber_chat") {
                        $icon = '<img src ="' . base_url() . 'img/if_Viber_3030.png"/>';
                    } else {
                        $icon = "";
                    }
                    $avatar = $noti['sender_info']['profile_pic'];
                    $newNotify[] = array(
                        'id' => $noti['_id']->{'$id'},
                        // 'line'  => $noti['line'],
                        'type' => $noti['source'],
                        'avatar' => $avatar,
                        'icon' => $icon,
                        'name' => $noti['title'],
                        'user_id' => $noti['sender_id'],
                        'text' => $noti['text'],
                        'date_added' => date('H:i d/m/Y'), //date("Y-m-d H:i:s", $noti['date_added']),
                    );
                    $array_tam[$noti['source']][$noti['sender_id']] = $noti['text'];
                }
            } else {
                if (!isset($array_tam[$noti['source']][$noti['sender_id']])) {
                    if ($noti['source'] == "new_facebook_chat") {
                        $icon = '<img src ="' . base_url() . 'img/if_Messenger_2525.png"/>';
                    } elseif ($noti['source'] == "new_facebook_comment") {
                        $icon = '<img src ="' . base_url() . 'img/if_Facebook_comment.png"/>';
                    } elseif ($noti['source'] == "new_viber_chat") {
                        $icon = '<img src ="' . base_url() . 'img/if_Viber_3030.png"/>';
                    } else {
                        $icon = "";
                    }
                    $avatar = $noti['sender_info']['profile_pic'];
                    $newNotify[] = array(
                        'id' => $noti['_id']->{'$id'},
                        'avatar' => $avatar,
                        'type' => $noti['source'],
                        'icon' => $icon,
                        'name' => $noti['title'],
                        'user_id' => $noti['sender_id'],
                        'text' => $noti['text'],
                        'date_added' => date('H:i d/m/Y'), //date("Y-m-d H:i:s", $noti['date_added']),
                    );
                    $array_tam[$noti['source']][$noti['sender_id']] = $noti['text'];
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($newNotify);
    }

    public function redirectNotify() {
        $json = array();

        $id = $this->input->post('id');
        $data_notis = $this->mongo_db->where(array("_id" => new MongoId($this->input->post('id'))))->getOne('chatNotifi');
        if (empty($data_notis)) {
            return;
        }
        $source = $data_notis['source'];
        $type = isset($data_notis['type']) ? $data_notis['type'] : "";
        $sender_id = $data_notis['sender_id'];

        if ($source == "messenger" || $source == "zalo") {
            // $data_views = $this->mongo_db->where( array("type" => "new_facebook_chat", "sender_id" => $sender_id ))->getOne('chatNotifi');
            $room_array = array(
                'user_id_create' => $this->username,
                'page_id' => $data_notis['page_id'],
                'trigger' => $data_notis['trigger'],
                'source' => $source, //private/group
                'from' => array("id" => $this->username, "name" => $this->name, "type" => "agent"),
                'to' => array("user_id" => $sender_id, "name" => $data_notis['sender_info']['name'], "type" => ''),
                'group_user' => '',
                'group_name' => '',
                'date_active' => time(),
                'date_added' => time(),
                'status' => 1,
            );
            $result = $this->mongo_db->insert('chatGroups', $room_array);
            $room_id = $result->{'$id'};
            //sau đó chuyển những tin nhắn mới qua cho user đó
            $this->UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source, $data_notis['page_id']);
            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'page_id' => $data_notis['page_id']))->delete_all('chatNotifi');
            $json['redirect'] = base_url() . 'chat?invite=' . $room_id;
        } elseif ($source == "facebook") {
            // $data_views = $this->mongo_db->where( array("type" => "new_facebook_comment", "sender_id" => $sender_id ))->getOne('chatNotifi');
            $room_array = array(
                'page_id' => $data_notis['page_id'],
                'trigger' => $data_notis['trigger'],
                'source' => $source, //private/group
                'from' => array("id" => $this->username, "name" => $this->name, "type" => "agent"),
                'to' => array(
                    "user_id" => $sender_id,
                    "name" => $data_notis['sender_info']['name'],
                ),
                'details' => array(
                    "comment_id" => $data_notis['details']['comment_id'],
                    "post_id" => $data_notis['details']['post_id'],
                    "post_url" => $data_notis['details']['post_url'],
                ),
                'group_user' => '',
                'group_name' => '',
                'date_active' => time(),
                'date_added' => time(),
                'status' => 1,
            );
            $result = $this->mongo_db->insert('chatGroups', $room_array);
            $room_id = $result->{'$id'};
            //sau đó chuyển những tin nhắn mới qua cho user đó
            $this->UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source, $data_notis['page_id']);
            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'page_id' => $data_notis['page_id']))->delete_all('chatNotifi');
            $json['redirect'] = base_url() . 'chat?invite=' . $room_id;
        } else if ($source == "livechat") {
            $room_array = array(
                'user_id_create' => $this->username,
                'page_id' => $data_notis['page_id'],
                'trigger' => $data_notis['trigger'],
                'source' => $source,
                'from' => array("id" => $this->username, "name" => $this->name, "type" => "agent"),
                'to' => array("user_id" => $sender_id, "name" => $data_notis['title'], "type" => ''),
                'group_user' => '',
                'group_name' => '',
                'date_active' => time(),
                'date_added' => time(),
                'status' => 1,
            );
            $result = $this->mongo_db->insert('chatGroups', $room_array);
            $room_id = $result->{'$id'};
            //sau đó chuyển những tin nhắn mới qua cho user đó
            $this->UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source, $data_notis['page_id']);
            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'page_id' => $data_notis['page_id']))->delete_all('chatNotifi');
            $json['redirect'] = base_url() . 'chat?invite=' . $room_id;
        } elseif ($source == "viber") {
            if ($this->checkRoomUserExists($this->username, $sender_id)) {
                $json['room_id'] = $this->checkRoomUserExists($this->username, $sender_id);
                $room_id = $json['room_id'];
            } else {
                //$data_views = $this->mongo_db->where( array("type" => "new_viber_chat", "sender_id" => $sender_id ))->getOne('chatNotifi');
                $room_array = array(
                    'page_id' => $data_views['page_id'],
                    'type' => "new_viber_chat", //private/group
                    'from' => array("id" => $this->username, "name" => $this->name, "type" => "extension"),
                    'to' => array("user_id" => $sender_id, "name" => $data_views['sender_info']['username'], "type" => 'viber'),
                    'group_user' => '',
                    'group_name' => '',
                    'date_active' => time(),
                    'date_added' => time(),
                    'status' => 1,
                );
                $result = $this->mongo_db->insert('chatGroups', $room_array);
                $room_id = $result->{'$id'};
                //sau đó chuyển những tin nhắn mới qua cho user đó
                $this->UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source, $data_notis['page_id']);
            }
            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'page_id' => $data_notis['page_id']))->delete_all('chatNotifi');
            $json['redirect'] = base_url() . 'chat?invite=' . $room_id;
        } elseif ($source == "transfer") {
            $room_id = $data_notis['room_id'];
            $username = $data_notis['send_to'];
            $user_info = $this->getUserInfoByUsername($username);
            $room_info = $this->mongo_db->where(array('_id' => new mongoId($room_id)))->getOne('chatGroups');
            if (isset($room_info['transfer_logs'])) {
                $transfer_logs = $room_info['transfer_logs'];
                $transfer_logs[] = array(
                    'username' => $username,
                    'timestamp' => time(),
                );
            } else {
                $transfer_logs[] = array(
                    'username' => $username,
                    'timestamp' => time(),
                );
            }
            if (isset($room_info['to']['user_id']) && $room_info['to']['user_id'] == $data_notis['sender_id']) {
                $to = array(
                    'user_id' => $username,
                );
                $this->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('to' => $to, 'transfer_logs' => $transfer_logs))->update('chatGroups');
            } elseif (isset($room_info['from']['id']) && $room_info['from']['id'] == $data_notis['sender_id']) {
                $from = array(
                    'id' => $username,
                );
                $this->mongo_db->where(array('_id' => new mongoId($room_id)))->set(array('from' => $from, 'transfer_logs' => $transfer_logs))->update('chatGroups');
            }
            $json['redirect'] = base_url() . 'chat?invite=' . $data_notis['room_id'];
            $this->mongo_db->where(array("sender_id" => $sender_id, 'source' => $source, 'room_id' => $data_notis['room_id']))->delete_all('chatNotifi');
            /* Gởi lại tin nhắn báo là đã nhận room này */
            $insert_noti = array(
                'source' => 'transfer_success',
                'room_id' => $room_id,
                'sender_id' => $this->username,
                // 'send_to'   => $data_notis['sender_id'],
                'sender_info' => array(
                    'user_id' => $data_notis['sender_id'],
                    'type' => 'agent',
                ),
                'title' => $this->username . ' Đã nhận room của bạn',
                'text' => '',
                'users' => array($data_notis['sender_id']),
                'date_added' => time(),
            );
            $json['data_emit'] = array(
                'send_to' => $sender_id,
                'source' => 'transfer_success',
                'title' => $this->username . ' Đã nhận room của bạn',
                'text' => $this->username . ' Đã nhận room của bạn',
                'avatar' => $user_info['profile_pic'],
            );

            $insert_noti['avatar'] = $user_info['profile_pic'];
            $this->mongo_db->insert('chatNotifi', $insert_noti);
        } elseif ($source == "transfer_success") {
            $this->mongo_db->where(array("_id" => new MongoId($this->input->post('id'))))->delete('chatNotifi');
        };

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function UpdateMesChuaPhanphoiBySenderId($room_id, $sender_id, $source) {
        $this->mongo_db->where(array("sender_id" => $sender_id, "room_id" => ''))->set(array('room_id' => $room_id, 'source' => $source))->update_all('chatMessages');
    }


    public function ajaxUpdateReadMes() {
        $json = array();
        $this->updateReadMes($this->input->post('room_id'), $this->username);
        $json['success'] = 'success';
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function updateReadMes($room_id, $user_id) {
        $result = $this->mongo_db->where(array('room_id' => $room_id, 'user_id' => $user_id))->delete_all('_chatViews');
    }

    public function renameGroupUser() {
        $json = array();

        $kq = $this->mongo_db->where(array("_id" => new MongoId($this->input->post('room_id'))))->set(array('group_name' => $this->input->post('name')))->update('chatGroups');
        $json['success'] = 'success';
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function deleteGroupUser() {
        $json = array();
        // $this->updateReadMes($this->input->post('room_id'), $this->username);
        $room = $this->mongo_db->where(array("_id" => new MongoId($this->input->post('room_id'))))->getOne('chatGroups');
        // var_dump($room['group_user']);
        $new_group_user = array();
        foreach ($room['group_user'] as $group_user) {
            if ($group_user['user_id'] == $this->input->post('user_id')) {
                continue;
            }
            $new_group_user[] = $group_user;
        }
        $kq = $this->mongo_db->where(array("_id" => new MongoId($this->input->post('room_id'))))->set(array('group_user' => $new_group_user))->update('chatGroups');
        $json['success'] = 'success';
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function ajaxCreateRoomUser() {
        $json = array();
        if ($this->checkRoomUserExists($this->username, $this->input->post('user_id'))) {
            $json['room_id'] = $this->checkRoomUserExists($this->username, $this->input->post('user_id'));
            $json['newroom'] = 'no';
            // return $json['room_id'];
        } else {
            $json['newroom'] = 'yes';
            $room_array = array(
                'user_id_create' => $this->session->userdata("extension"),
                'type' => $this->input->post('type'), //private/group
                'from' => array("id" => $this->username, "username" => $this->name, "type" => "extension"),
                'to' => array("user_id" => $this->input->post('user_id'), "username" => $this->input->post('user_name'), "type" => $this->input->post('user_type')),
                'group_user' => '',
                'group_name' => '',
                'date_active' => time(),
                'date_added' => time(),
                'status' => 1,
            );
            $result = $this->mongo_db->insert('chatGroups', $room_array);
            $json['room_id'] = $result->{'$id'};
            // $json['user_ids'] = ;
        }

        // return $result->{'$id'};
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function createRoomUser($user_id) {
        $json = array();
        if ($this->checkRoomUserExists($this->username, $this->input->post('user_id'))) {
            $json['room_id'] = $this->checkRoomUserExists($this->username, $this->input->post('user_id'));
            // $json['newroom'] = 'no';
            return $json['room_id'];
        } else {
            // $json['newroom'] = 'yes';
            $room_array = array(
                'user_id_create' => $this->username,
                'type' => $this->input->post('type'), //private/group
                'from' => array("id" => $this->username, "username" => $this->name, "type" => "extension"),
                'to' => array("user_id" => $this->input->post('user_id'), "username" => $this->input->post('user_name'), "type" => $this->input->post('user_type')),
                'group_user' => '',
                'group_name' => '',
                'date_active' => time(),
                'date_added' => time(),
                'status' => 1,
            );
            $result = $this->mongo_db->insert('chatGroups', $room_array);
            $json['room_id'] = $result->{'$id'};
            // $json['user_ids'] = ;
        }

        return $result->{'$id'};
    }

    public function addUserGroup() {
        $json = array();
        $group_array = array();
        if (!empty($this->input->post('user_id'))) {

            $room = $this->mongo_db->where(array("_id" => new MongoId($this->input->get('room_id'))))->getOne('chatGroups');

            foreach ($this->input->post('user_id') as $user_id) {
                if (!empty($this->getAgent($user_id))) {
//$this->get_daily($user_id)
                    $agent = json_decode($this->getAgent($user_id));
                    $group_array[] = array(
                        'user_id' => $agent[0]->extension,
                        'name' => $agent[0]->agentname,
                    );
                } else {
                    $agent = json_decode($this->get_daily($user_id));
                    $group_array[] = array(
                        'user_id' => $agent->info['0']->id,
                        'name' => $agent->info['0']->HoTen,
                    );
                }
            }
            $group_curent = $room['group_user'];
            $group_curent = array_merge($group_curent, $group_array);
            // var_dump($group_curent);exit();
            $kq = $this->mongo_db->where(array("_id" => new MongoId($this->input->get('room_id'))))->set(array('group_user' => $group_curent))->update('chatGroups');


            $json['room_id'] = $this->input->get('room_id');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function createRoomGroup() {
        $json = array();
        $group_array = array();
        if (!empty($this->input->post('user_id'))) {
            foreach ($this->input->post('user_id') as $user_id) {
                if (!empty($this->getAgent($user_id))) {
//$this->get_daily($user_id)
                    $agent = json_decode($this->getAgent($user_id));
                    $group_array[] = array(
                        'user_id' => $agent[0]->extension,
                        'name' => $agent[0]->agentname,
                    );
                } else {
                    $agent = json_decode($this->get_daily($user_id));
                    $group_array[] = array(
                        'user_id' => $agent->info['0']->id,
                        'name' => $agent->info['0']->HoTen,
                    );
                }
            }

            $room_array = array(
                'user_id_create' => $this->session->userdata("extension"),
                'type' => 'group', //private/group
                'from' => array("id" => $this->username, "username" => $this->name, "type" => "extension"),
                'to' => array(),
                'group_user' => $group_array,
                'group_name' => '',
                'date_active' => time(),
                'date_added' => time(),
            );
            $result = $this->mongo_db->insert('chatGroups', $room_array);
            $json['room_id'] = $result->{'$id'};
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function updateActiceRoom($room_id) {
        $kq = $this->mongo_db->where(array("_id" => new MongoId($room_id)))->set(array('date_active' => time()))->update('chatGroups');
    }

    public function updateViews($room_id, $user_id, $mes_id, $mes_text) {
        $user_array = array(
            'room_id' => $room_id,
            'user_id' => $user_id,
            'mes_id' => $mes_id,
            'mes_text' => $mes_text,
        );
        $result = $this->mongo_db->insert('chatViews', $user_array);
    }

    public function loadRoom($type = '') {
        $pipeline = array();
        if ($type == 'message') {
            $pipeline[] = array(
                '$match' => array('trigger' => 'message'),
            );
        } elseif ($type == 'comment') {
            $pipeline[] = array(
                '$match' => array('trigger' => 'comment'),
            );
        }

        $pipeline[] = array(
            '$match' => array(
                '$or' => array(
                    array('from.id' => $this->userextension),
                    array('to.user_id' => $this->username),
                    array('group_user' => array('$elemMatch' => array('user_id' => $this->username)),
                    ),
                ),
            ),
        );

        $pipeline[] = array(
            '$sort' => array("date_active" => -1),
        );
        // var_dump($pipeline);
        $results = $this->mongo_db->aggregate_pipeline("chatGroups", $pipeline);
        // print_r($results);
        $data_group = array();
        foreach ($results as $value) {
            if ($type != 'resolved') {
                if (empty($value['status'])) {
                    continue;
                }
            } else {
                if ($value['status'] != 0) {
                    continue;
                }
            }
             $room_id = $value['_id']->{'$id'};
             //Check pepple nếu k có insert vào db people
        $people_chat = $this->mongo_db->where('_id', new mongoId($room_id))->getOne('chatGroups');
        $people_info_pp = $this->mongo_db->where('people_id', $people_chat['to']['user_id'])->getOne('people');

        if (empty($people_info_pp)) {

            $data_sending = array(
                'user_id' => $people_chat['to']['user_id'],
            );

            $response = $this->Omnisales->get('app/getprofile', $data_sending, $this->access_token);

            $httpcode = $response->gethttpStatusCode();
            $response = $response->getDecodedBody();
            $data_peple = array(
                "page_id" => $response['data']['page_id'],
                "sender_id" => $people_chat['to']['user_id'],
                "people_id" => $response['data']['id'],
                "name" => isset($response['data']['name']) ? $response['data']['name'] : "south telecom",
                "phone" => $response['data']['phone'],
                "email" => $response['data']['email'],
                "address" => $response['data']['address'],
                "profile_pic" => isset($response['data']['picture']) ? $response['data']['picture'] : $profile_pic,
                "source" => $people_chat['source'],
                "date_added" => (int) time(),
                "room_id" => $room_id
            );
            //         print_r($response); //exit();
            $result_pp = $this->mongo_db->insert('people', $data_peple);
        }
           
            $show_user = '';
            $count = 0;
            if ($value['from']['id'] == $this->userextension) {
                $show_user = 'to';
            } else {
                $show_user = 'from';
            }
            
            // var_dump($value);
            if ($value['source'] == "messenger") {
                //$icon = base_url().'img/if_Messenger_2525.png';
                // $icon = base_url().'assets/images/flogo_rgb_hex-brc-site-250.png';

                $icons = array(
                    base_url().'assets/images/flogo_rgb_hex-brc-site-250.png',
                    base_url() . 'assets/images/message_icon.svg',                    
                );
            } elseif ($value['source'] == "zalo") {
                $icons = array(
                    base_url() . 'assets/images/zalo_favicon.ico'   ,
                    base_url() . 'assets/images/message_icon.svg',
                );
            } elseif ($value['source'] == "facebook") {
                $icons = array(
                    base_url().'assets/images/flogo_rgb_hex-brc-site-250.png',
                    base_url() . 'assets/images/comment_icon.svg',                    
                );
                // $icons = base_url() . 'assets/images/comment_icon.svg'; //base_url().'assets/images/flogo_rgb_hex-brc-site-250.png';
            } elseif ($value['source'] == "livechat") {
                $icons = array(
                    base_url() . 'assets/images/livechat_chat_icon.png',
                    base_url() . 'assets/images/message_icon.svg',
                );
            } elseif ($value['source'] == "viber") {
                $icons = array(
                    base_url() . 'img/if_Viber_3030.png',
                    base_url() . 'assets/images/message_icon.svg',
                );
            } else {
                $icons = array();
            }

            $page_name = '';
            if (isset($value['page_id'])) {
                if ($value['source'] == 'messenger' || $value['source'] == 'facebook') {
                    $page_info = $this->mongo_db->where(array("_id" => new mongoId($value['page_id']), 'source' => 'facebook'))->getOne('pageapps');
                    $page_name = $page_info['page_info']['name'];
                } else {
                    $page_info = $this->mongo_db->where(array("_id" => new mongoId($value['page_id']), 'source' => $value['source']))->getOne('pageapps');
                    $page_name = $page_info['page_info']['name'];
                } 
            }

            $people_info = $this->mongo_db->where(array('people_id' => $value['to']['user_id']))->getOne('people');

            $avatar = base_url('assets/images/avatar_default.jpg');

            if ($people_info) {
                if ($value['source'] == 'messenger') {
                    $avatar = $people_info['profile_pic'];
                } elseif ($value['source'] == 'facebook') {
                    $avatar = $people_info['profile_pic']; //$this->urlImgF($pageapps['page_info']['access_token'], $value['to']['id']);
                } elseif ($value['source'] == 'livechat') {
                    $avatar = base_url('assets/images/avatar_default.jpg'); //$this->urlImgF($pageapps['page_info']['access_token'], $value['to']['id']);
                } else {
                    $avatar = $people_info['profile_pic'];
                }
                $avatar = str_replace('http:', 'http:', $avatar);
            }

            $transfer_from = '';
            if (isset($value['transfer_logs'])) {
                $assign_end = end($value['transfer_logs']);
                $transfer_from = $assign_end['username'];
            }
            $data_group[] = array(
                'room_id' => $room_id,
                'group_name' => $this->getNameRoom($room_id),
                // 'profile_pic' => $value['source'],
                'transfer_from' => $transfer_from,
                'show_user' => $show_user,
                'avatar' => $avatar,
                'icons' => $icons,
                'page_name' => $page_name,
                'source' => $value['source'],
                'from' => $value['from'],
                'to' => $value['to'],
                //'last_mes' => $this->getlastMes($room_id),
                'unread' => count($this->countMesUnReadByRoom($room_id, $this->username)),
                'status' => $value['status'],
                'group_user' => $value['group_user'],
                'date_added' => $value['date_added'],
                    // 'date_active'    => $value['date_active'],
            );
        }

        return $data_group;
    }

    public function getNameRoom($room_id) {
       
         //Check pepple nếu k có insert vào db people
        
        $room = $this->mongo_db->where(array("_id" => new MongoId($room_id)))->getOne('chatGroups');

        if ($room['from']['id'] == $this->userextension) {
            $people_info = $this->mongo_db->where(array('people_id' => $room['to']['user_id']))->getOne('people');
            $group_name = $people_info['name'];
        } else {

            $group_name = $room['from']['name'];
        }
        return $group_name;
    }

    // Kiểm tra phòng đã được tạo giữa 2 user
    public function checkRoomUserExists($user_curent, $user_id) {
        $pipeline = array(
            /* array(
              '$match' => array('type' => 'private')
             */
            array(
                '$match' => array(
                    '$or' => array(
                        array(
                            '$and' => array(
                                array('from.id' => $user_curent),
                                array('to.id' => $user_id),
                            ),
                        ),
                        array(
                            '$and' => array(
                                array('from.id' => $user_id),
                                array('to.id' => $user_curent),
                            ),
                        ),
                    ),
                    '$and' => array(
                        array('status' => 1),
                    // array('to.id' => $user_curent)
                    ),
                ),
            ),
        );
        $results = $this->mongo_db->aggregate_pipeline("chatGroups", $pipeline);
        // var_dump($results);
        if (!empty($results)) {
            return $results[0]['_id']->{'$id'};
        } else {
            return false;
        }
    }

    public function getlastMes($room_id) {
        $last_mes = $this->mongo_db->where(array("room_id" => $room_id))->order_by(array("date_added" => -1))->limit(1)->get('chatMessages');
        if (isset($last_mes[0]['text'])) {
            return excerpt($last_mes[0]['text'], 15);
        } else {
            return '';
        }
    }

    public function sendChat() {
        $room_query = $this->mongo_db->where(array("_id" => new MongoId($this->input->post('room_id'))))->getOne('chatGroups');
        // var_dump($this->username);
        // var_dump($this->userextension);
        // var_dump($room_query);
        if ($room_query['from']['id'] == $this->userextension) {
            $receiver_id = $room_query['to']['user_id'];
        } else {
            $receiver_id = $room_query['from']['id'];
        }
        $json = array();
        $text = preg_replace('/(<br>)+$/', '', $this->input->post('text'));


        $data_chat = array(
            'sender_id' => $this->username,
            'sender_info' => array(
                "user_id" => $this->username,
                "name" => $this->name,
            ),
            'room_id' => $this->input->post('room_id'),
            'type' => 'text',
            'text' => $text,
            'views' => array(),
            'date_added' => time(),
        );
        $result = $this->mongo_db->insert('chatMessages', $data_chat);

        $mes = $this->mongo_db->where(array("_id" => new MongoId($result->{'$id'})))->getOne('chatMessages');
        $this->updateActiceRoom($this->input->post('room_id'));

        $json['id'] = $result->{'$id'};
        $json['text'] = $mes['text'];
        $json['page_id'] = $room_query['page_id'];
        $json['receiver_id'] = $receiver_id;
        $json['trigger'] = $room_query['trigger'];
        $json['source'] = $room_query['source'];
        $json['sender_id'] = $this->username;
        $json['name'] = $this->session->userdata('user');
        $json['type'] = 'text';
        $json['date'] = date("H:i", $mes['date_added']);
        $json['timestamp'] = $mes['date_added'];



        //Gởi tin qua webhook
        if ($room_query['trigger'] == 'message') {
            if ($json['source'] == 'livechat') {
                $data_sending = array(
                    'receiver_id' => $receiver_id,
                    'page_id' => $room_query['page_id'],
                    'message' => $text,
                    'name' => $this->username,
                    'avatar' => $this->avatar,
                    'metadata' => array(
                        'id' => $json['id'],
                    ),
                );
            } else {
                $data_sending = array(
                    'receiver_id' => $receiver_id,
                    'page_id' => $room_query['page_id'],
                    'message' => $text,
                    'metadata' => array(
                        'id' => $json['id'],
                    ),
                );
            }
         // print_r($data_sending);
            try{
                $response = $this->Omnisales->post('me/sendmessage/text', $data_sending, $this->access_token);
            }catch(Exception $e){
                $this->mongo_db->where(array( '_id' => mongoId($result->{'$id'}) ))->delete('chatMessages');
            }
            
        /*$httpcode = $response->gethttpStatusCode();
        $response = $response->getDecodedBody();
        var_dump($response);
        var_dump($httpcode);*/
        } elseif ($room_query['trigger'] == 'comment') {
            $data_sending = array(
                'object_id' => $room_query['details']['comment_id'],
                'page_id' => $room_query['page_id'],
                'message' => $text,
                'metadata' => array(
                    'id' => $json['id'],
                ),
            );
            try{
                $response = $this->Omnisales->post('me/comment/create', $data_sending, $this->access_token);
            }catch(Exception $e){
                $this->mongo_db->where(array( '_id' => mongoId($result->{'$id'}) ))->delete('chatMessages');
            }
        }


        $httpcode = $response->gethttpStatusCode();
        $response = $response->getDecodedBody();
        // var_dump($response);
        // var_dump($httpcode);
        if (empty($response['error'])) {
            //Nếu không lỗi
            $json['success'] = 'success';
        } else {
            //Nếu lỗi
        }


        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function AjaxloadChatMes() {
        $loadChatMes = $this->loadChatMes($this->input->get('room_id'), $this->input->get('page'));
        header('Content-Type: application/json');
        echo json_encode($loadChatMes);
    }

    public function loadChatMes($room_id, $page = 0) {
        $curent_id = $this->name;
        $limit = 30;
        // $page = 4;
        $skip = $limit * $page;
        $room = $this->mongo_db->where(array("_id" => new MongoId($room_id)))->getOne('chatGroups');
        // ob_start();
        $pipeline = array(
            array(
                '$match' => array('room_id' => $room_id), //'type' => 'private',
            ),
            array(
                '$sort' => array("date_added" => -1),
            ),
            array(
                '$skip' => $skip,
            ),
            array(
                '$limit' => $limit,
            ),
            array(
                '$sort' => array("date_added" => 1),
            ),
        );
        $results = $this->mongo_db->aggregate_pipeline("chatMessages", $pipeline);
        $data_mes = array();
        $profile_pic = base_url('assets/images/avatar_default.jpg');
//        end
        foreach ($results as $result) {

            $type = isset($result['type']) ? $result['type'] : "";
            $source = isset($result['source']) ? $result['source'] : '';
            if (empty($result['source'])) {
                $url = isset($result['url']) ? $result['url'] : "";
            } else {
                $url = isset($result['url']) ? $result['url'] : "";
                $url = str_replace('http:', 'https:', $url);
                // var_dump($url);
            }

            if (isset($result['comment_trash']) && $result['comment_trash'] == true) {
                $comment_like = 'disabled';
                $comment_hide = 'disabled';
                $comment_trash = 'disabled';
                $comment_view = 'disabled';
            } else {
                $comment_like = isset($result['comment_like']) && !empty($result['comment_like']) ? "active" : "";
                $comment_hide = isset($result['comment_hide']) && !empty($result['comment_hide']) ? "active" : "";
                $comment_trash = isset($result['comment_trash']) && !empty($result['comment_trash']) ? "active" : "";
                $comment_view = isset($result['comment_view']) && !empty($result['comment_view']) ? "active" : "";
            }
            $page_name = '';

            if ($result['sender_id'] == $this->username) {
                $user_info = $this->mongo_db->where(array('username' => $this->username))->getOne('users');

                $name = $user_info['lastname'] . ' ' . $user_info['firstname'];
                if (!empty($user_info['profile_pic'])) {
                    $profile_pic = base_url() . $user_info['profile_pic'];
                } else {
                    $profile_pic = base_url('assets/images/avatar_default.jpg');
                }
            } elseif (isset($result['page_id']) && ($result['page_id'] == $result['sender_id'])) {
                /* if (!empty($source)) {
                  $name = $page_name;
                 */
                if ($result['source'] == 'messenger' || $result['source'] == 'facebook') {
                    $page_facebook = $this->getFacbookPageById($result['page_id']);
                    $name = $page_facebook['name'];
                    $profile_pic = "https://graph.facebook.com/" . $page_facebook['page_id'] . "/picture?height=150&amp;width=150"; //isset($result['sender_info']['profile_pic']) ? $result['sender_info']['profile_pic'] : base_url('assets/images/avatar_default.jpg');
                } else {
                    $profile_pic = base_url('assets/images/avatar_default.jpg');
                }
            } else {

                $people_info = $this->mongo_db->where(array('page_id' => $result['page_id'], 'sender_id' => $result['sender_id']))->getOne('people');
//                print_r($result['sender_id']);
//                print_r($people_info);
                if ($people_info) {
                    $name = $people_info['name'];

                    if ($result['source'] == 'messenger' || $result['source'] == 'facebook') {
                        $profile_pic = $people_info['profile_pic'];
                    } else {
                        $profile_pic = base_url('assets/images/avatar_default.jpg');
                    }
                    $profile_pic = str_replace('http:', 'http:', $profile_pic);
                }

                if ($source == 'messenger' || $source == 'facebook') {
                    $page_info = $this->mongo_db->where(array("_id" => new mongoId($result['page_id']), 'source' => 'facebook'))->getOne('pageapps');
                    $page_name = $page_info['page_info']['name'];
                } else {
                    $page_info = $this->mongo_db->where(array("_id" => new mongoId($result['page_id']), 'source' => $source))->getOne('pageapps');
                    $page_name = $page_info['page_info']['name'];
                }
            }

            $data_mes[] = array(
                'id' => $result['_id']->{'$id'},
                'text' => $result['text'],
                'sender_id' => $result['sender_id'],
                'profile_pic' => $profile_pic,
                'name' => $name, //isset($name) ? $name : '',
                'romm_id' => $room_id, //isset($name) ? $name : '',
                'date' => date('H:i', $result['date_added']),
                'timestamp' => $result['date_added'],
                'type' => $type,
                'url' => $url,
                'details' => isset($result['details']) ? $result['details'] : array(),
                'comment_like' => $comment_like, //isset($result['comment_like']) && $result['comment_like'] == true ? "active" : "",
                'comment_hide' => $comment_hide, //isset($result['comment_hide']) && $result['comment_hide'] == true ? "active" : "",
                'comment_trash' => $comment_trash, //isset($result['comment_trash']) && $result['comment_trash'] == true ? "active" : "",
                'comment_view' => $comment_view,
                    /* 'text'    => $result['text'],
                      'text'  => $result['text'],
                      'text'  => $result['text'],
                     */
            );
        }
        return $data_mes;
    }

    public function loadGroupBox() {
        ob_start();
        /**/
        $room = $this->mongo_db->where(array("_id" => new MongoId($this->input->post('room_id'))))->getOne('chatGroups');
        $room_id = $room['_id']->{'$id'};
        $agents = array();
        /* foreach ($this->get_agent_group() as $groups) {
          foreach ($groups['items'] as $value) {
          $agents[$value['userid']] = $value['name'];
          }
         */
        foreach ($this->getDanhba() as $extension) {
            foreach ($extension['user_array'] as $value) {
                $agents[$value['extension']] = $value['agentname'];
            }
        }
        ?>
        <div class="modal-body">
            <input type="text" onkeyup="searchAgent()" placeholder="" class="form-control search_agent">
            <div class="choice-user-group"></div>
            <ul class="list-group scroll1 agent-list" style=" height: 300px; overflow-y: scroll;">
                <?php if (!empty($room['group_user'])): ?>
                    <?php foreach ($agents as $key => $agent): ?>
                        <?php $show = 1; ?>
                        <?php foreach ($room['group_user'] as $group_user): ?>
                            <?php
                            if ($group_user['user_id'] == $key) {
                                $show = 0;
                                break;
                            }
                            ?>
                        <?php endforeach ?>
                        <?php if ($show == 1): ?>
                            <li class="list-group-item" data-id="<?php echo $key; ?>"><?php echo $agent; ?></li>
                        <?php endif ?>
                    <?php endforeach ?>
                <?php else: ?>
                    <?php foreach ($agents as $key => $agent): ?>
                        <li class="list-group-item" data-id="<?php echo $key; ?>"><?php echo $agent; ?></li>
                    <?php endforeach ?>
                <?php endif ?>
            </ul>
        </div>

        <div class="modal-footer">
            <?php if ($room['type'] == "group"): ?>
                <button type="submit" class="btn btn-primary btn-add-user-group" data-room-id="<?php echo $room_id; ?>">Thêm thành viên</button>
            <?php else: ?>
                <button type="submit" class="btn btn-primary btn-create-group">Tạo group</button>
            <?php endif ?>
            <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
        </div>


        <?php
        $list_post = ob_get_contents();
        ob_end_clean();
        echo $list_post;
    }

    public function conversation() {
        $username = $this->session->userdata('user');
        $room = $this->mongo_db->where(array("_id" => new MongoId($this->input->get('room_id'))))->getOne('chatGroups');
        if (empty($room)) {
            return '';
        }
        $room_id = $room['_id']->{'$id'};
        $json['room_id'] = $room_id;
        $json['source'] = $room['source'];
        $json['page_id'] = $room['page_id'];
        $json['trigger'] = $room['trigger'];
        $json['details'] = array();
        if ($room['trigger'] == 'comment') {
            $json['details']['comment_id'] = isset($room['details']['comment_id']) ? $room['details']['comment_id'] : '';
            $post_id = $room['details']['post_id'];
            $json['details']['post'] = $this->getPostbyIdFacebook($room['page_id'], $post_id);
        }

        $json['nameRoom'] = $this->getNameRoom($room_id);
        $json['messages'] = $this->loadChatMes($room_id, $this->input->get('page'));

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function loadChatBox() {
        ob_start();
        // $room_id = $this->createRoomUser();
        $userdata = $this->session->userdata;
        // $room_id
        $room = $this->mongo_db->where(array("_id" => new MongoId($this->input->post('room_id'))))->getOne('chatGroups');

        if (empty($room)) {
            return '';
        }
        $room_id = $room['_id']->{'$id'};
        $NameRoom = $this->getNameRoom($room_id);
        $chatmes = $this->loadChatMes($room_id);
        if ($room['from']['id'] != $this->username) {
            $type_user = $room['from']['type'];
            $to_user_id = $room['from']['id'];
        } else {
            $type_user = $room['to']['type'];
            $to_user_id = $room['to']['user_id'];
        }
        ?>
        <div class="chatbox chatbox<?php echo $this->input->post('room_id'); ?>" data-room-id="<?php echo $this->input->post('room_id'); ?>" data-next="0" data-pre="0">
            <div class="chat-head">
                <div class="user-info">
                    <span><i class="fa fa-star-o" style=" margin-top: 3px; font-size: 18px;"></i></span>
                    <!-- <?php if ($room['type'] == 'group'): ?>
                                                                                            <a class="name-room"><input type="text" name="" value="<?php echo $NameRoom; ?>"> <i class="fa fa-check-circle-o" style="display: none; cursor: pointer;"></i> <i class="fa fa-times-circle-o" style="display: none;cursor: pointer;"></i></a>
                    <?php else: ?> -->
                        <a class="name-room"><?php echo $NameRoom; ?></a>
                        <!-- <?php endif ?> -->
                    <!-- <?php if ($room['type'] == 'group'): ?>
                                                                                            <a class="btn-togger-code" style=" cursor: pointer; font-size: 14px; display: block; margin-left: 28px; "><i class="fa fa-angle-right" aria-hidden="true"></i> <?php echo count($room['group_user']); ?> thành viên</a>
                    <?php endif ?> -->
                </div>
                <div class="user-actions">
                    <!-- <a data-toggle="modal" data-target="#add-to-group" class="btn-add-to-group-popup"><i class="fa fa-plus"></i></a> -->
                    <!-- <a class="btn-more btn-add-ticket-chat" data-phone="">Thêm Ticket</a> -->
                    <!-- <?php if ($room['type'] != 'group' & $type_user == "agency"): ?>
                                                                                            <a class="btn-call" data-phone="<?php echo $to_user_info->info['0']->DienThoai; ?>"><i class="fa fa-phone"></i></a>
                    <?php endif ?> -->
                    <a class="btn-search-single" title="Tìm kiếm"><i class="fa fa-search" aria-hidden="true"></i></a>
                    <?php //if ($room['type']== 'new_facebook_chat' || $room['type']== 'new_facebook_comment' || $room['type']== 'new_viber_chat' || $room['type']== 'new_viber_chat'):     ?>
                    <a class="btn-close-room" data-room-id="<?php echo $this->input->post('room_id'); ?>"title="Đóng phiên"><i class="fa fa-times"></i></a>
                    <?php //endif    ?>
                </div>
            </div>
            <?php if (!empty($room['group_user'])): ?>
                <div class="box-group" style="display: none;">
                    <?php foreach ($room['group_user'] as $group_user): ?>
                        <span class="label" data-id="<?php echo $group_user['user_id']; ?>"><?php echo $group_user['name']; ?>

                        </span>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
            <div class="chat-content-w ps ps--theme_default scroll1">
                <div class="chat-content">
                    <?php echo $chatmes; ?>
                </div>
                <div class="ps__scrollbar-x-rail" style="left: 0px; bottom: 0px;">
                    <div class="ps__scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                </div>
                <div class="ps__scrollbar-y-rail" style="top: 0px; right: 0px;">
                    <div class="ps__scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                </div>
            </div>
            <div class="box-load-bottom">
                <button type="button" class="btn-load-bottom btn btn-primary btn-sm">Tải thêm...</button>
            </div>
            <div class="box-searh-single">
                <span>Tìm kiếm</span> <input type="text" name=""> <!-- <button type="button" class="btn-next"><i class="fa fa-angle-up"></i> Cũ hơn</button> <button type="button" class="btn-pre"><i class="fa fa-angle-down"></i> Mới hơn</button>  --> <a title="Hủy tìm kiếm" class="btn-remove-box-search"><i class="fa fa-times-circle-o "></i></a>
            </div>
            <div class="chat-controls">
                <div class="chat-input"><textarea placeholder="Nhập 1 tin nhắn..."></textarea></div>
                <div class="chat-input-extra">
                    <div class="chat-extra-actions">
                        <a class="btn-upload" ><i class="fas fa-file-image" aria-hidden="true"></i></a>
                    </div>
                    <div class="chat-btn" v-on:click="btn_chat" data-room-id="<?php echo $room_id; ?>" type="button"><a class="btn btn-primary btn-sm" href=""><i class="far fa-paper-plane"></i></a></div>
                </div>
            </div>
        </div><!--/.chatbox-->
        <script type="text/javascript">
            $chat_content = $(document).find('.chatbox<?php echo $this->input->post('room_id'); ?> .chat-content-w');
            $chat_content.scrollTop($chat_content.height());
            $(document).find('.chatbox<?php echo $this->input->post('room_id'); ?> textarea').focus();
            $('.chatbox<?php echo $this->input->post('room_id'); ?> .chat-content-w').scroll(function (e) {
                height = $(this).height();
                var curent_scroll = $(this).scrollTop();
                if (curent_scroll == 0) {
                    loadTop('<?php echo $this->input->post('room_id'); ?>');
                }

                /*if(height+10 < curent_scroll) {
                 loadBottom('<?php echo $this->input->post('room_id'); ?>');
                 }*/
            });
        </script>
        <?php
        $list_post = ob_get_contents();
        ob_end_clean();
        echo $list_post;
    }

    public function uploadFileNode() {
        $json = array();
        $config['upload_path'] = FCPATH . 'upload/chatnode';
        $config['allowed_types'] = 'gif|jpg|png|mp3|image/jpe|image/jpeg|jpeg|png|doc';
        $config['max_size'] = 25000;

        $new_name = "file" . time();
        $config['file_name'] = $new_name;
        if (file_exists(FCPATH . 'upload/chatnode') == "") {
            mkdir(FCPATH . 'upload/chatnode', 0777, true);
        }

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('file')) {
            $error = array('error' => $this->upload->display_errors());
            $json['error'] = $error['error'];
        } else {
            $room_id = $this->input->get('room_id');
            $room_query = $this->mongo_db->where(array("_id" => new MongoId($room_id)))->getOne('chatGroups');
            if ($room_query['from']['id'] == $this->userextension) {
                $receiver_id = $room_query['to']['user_id'];
            } else {
                $receiver_id = $room_query['from']['id'];
            }
            $data = array('upload_data' => $this->upload->data());
            $duoifile = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $json['link'] = base_url() . 'upload/chatnode/' . $config['file_name'] . '.' . $duoifile;
            $text = $_FILES['file']['name'];

            
            $data_sending = array(
                'receiver_id' => $receiver_id, // user id
                'page_id' => $room_query['page_id'],
                'url' => $json['link'],
            );
            // var_dump($data_sending);
            $response = $this->Omnisales->post('me/sendmessage/image', $data_sending, $this->access_token);

            $httpcode = $response->gethttpStatusCode();
            $response = $response->getDecodedBody();
            if (empty($response['error'])) {
                if (in_array($duoifile, array("jpg", "jpe", "jpeg", "gif", "png"))) {
                    $type = 'image';
                } else {
                    $type = 'file';
                }

                $data_chat = array(
                    'sender_id' => $this->username,
                    'sender_info' => array("user_id" => $this->username, "name" => $this->name, "type" => '' /* $this->username_type */),
                    'room_id' => $room_id,
                    'views' => array(),
                    'text' => $text,
                    'url' => $json['link'],
                    'type' => $type,
                    // 'type_room'        => $room_query['type'],
                    'date_added' => time(),
                );

                $result = $this->mongo_db->insert('chatMessages', $data_chat);
                $message_id = $result->{'$id'};
                $mes = $this->mongo_db->where(array("_id" => new MongoId($message_id)))->getOne('chatMessages');

                if ($room_query['from']['id'] == $this->username) {
                    $receiver_id = $room_query['to']['user_id'];
                } else {
                    $receiver_id = $room_query['from']['id'];
                }
                $json['message_id'] = $message_id;
                $json['page_id'] = $room_query['page_id'];
                $json['receiver_id'] = $receiver_id;
                $json['trigger'] = $room_query['trigger'];
                $json['source'] = $room_query['source'];
                $json['sender_id'] = $mes['sender_id'];
                $json['username'] = $this->name;
                $json['text'] = html_entity_decode($mes['text']);
                $json['type'] = $type;
                $json['date_added'] = $mes['date_added'];
                $json['date'] = date("H:i", $mes['date_added']);
                $json['url'] = $json['link'];
                $json['success'] = 'success';
            }
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function autoJoin() {
        $data['rooms'] = $this->loadRoom();
        // var_dump($data['rooms']);
        $data['room_join'] = array();
        foreach ($data['rooms'] as $room) {
            $data['room_join'][] = $room['room_id'];
        }
        // $data['room_join'] = json_encode($data['room_join']);
        header('Content-Type: application/json');
        echo json_encode($data['room_join']);
    }

    //SEARCH
    public function getPageById($_id, $room_id) {
        $pipeline = array(
            array(
                '$match' => array('room_id' => $room_id), //'type' => 'private',
            ),
            array(
                '$sort' => array("date_added" => -1),
            ),
        );
        $chats = $this->mongo_db->aggregate_pipeline("chatMessages", $pipeline);

        $limit = 30;
        //$skip = $limit*$page;
        $count = 0;
        foreach ($chats as $key => $chat) {
            $count++;
            if ($_id == $chat['_id']->{'$id'}) {
             
                if ($count <= $limit) {
                    $page = 0;
                } else {
                    $page = ceil($count / $limit) - 1;
                }

                return $page;
            }
        }
        return false;
    }

    public function searchSignle() {
        $json = array();
        $pipeline = array(
            array(
                '$match' => array(
                    'room_id' => $this->input->post('room_id'),
                    'text' => array('$regex' => $this->input->post('text'), '$options' => '$i'),
                ),
            ),
            array(
                '$limit' => 20,
            ),
            array(
                '$sort' => array("date_added" => -1),
            ),
        );

        $results = $this->mongo_db->aggregate_pipeline("chatMessages", $pipeline);
        $chats = array();
        foreach ($results as $result) {
            $getPageById = $this->getPageById($result['_id']->{'$id'}, $this->input->post('room_id'));
            // $people_info = $this->mongo_db->where(array('page_id'    => $value['page_id'], 'people_id'   => $value['sender_id']  ))->getOne('people');
            $avatar = base_url('assets/images/avatar_default.jpg');

            $user_info = $this->getSenderInfoByMesId($result['_id']->{'$id'});
            $chats[] = array(
                'room_id' => $this->input->post('room_id'),
                'id' => $result['_id']->{'$id'},
                'page' => $getPageById,
                'avatar' => $user_info['profile_pic'],
                'group_name' => $result['sender_info']['name'],
                'text' => excerpt($result['text'], 15),
                'date_added' => date('H:i d/m/Y', $result['date_added']),
            );
        }
        $json['success'] = $chats;
        // usleep( 10 * 100000 );
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getSenderInfoByMesId($mes_id) {
        $mes_info = $this->mongo_db->where(array("_id" => new MongoId($mes_id)))->getOne('chatMessages');
        $room_info = $this->mongo_db->where(array("_id" => new MongoId($mes_info['room_id'])))->getOne('chatGroups');
        $sender_info = array();
        if ($mes_info['sender_id'] == $this->username) {
            $user_info = $this->mongo_db->where(array('username' => $this->username))->getOne('users');
            if (!empty($user_info['profile_pic'])) {
                $profile_pic = base_url() . $user_info['profile_pic'];
            } else {
                $profile_pic = base_url('assets/images/avatar_default.jpg');
            }
            $sender_info = array(
                'name' => $user_info['lastname'] . ' ' . $user_info['firstname'],
                'sender_id' => $this->username,
                'profile_pic' => $profile_pic,
            );
        } else {
            $profile_pic = isset($mes_info['sender_info']['profile_pic']) ? $mes_info['sender_info']['profile_pic'] : base_url('assets/images/avatar_default.jpg');
            $profile_pic = str_replace('http:', 'https:', $profile_pic);
            $sender_info = array(
                'name' => $mes_info['sender_info']['name'],
                'sender_id' => $mes_info['sender_id'],
                'profile_pic' => $profile_pic,
            );
        }
        return $sender_info;
    }

    public function getUserInfoByUsername($username) {
        $user_info = $this->mongo_db->where(array('username' => $username))->getOne('users');
        if (!empty($user_info['profile_pic'])) {
            $profile_pic = base_url() . $user_info['profile_pic'];
        } else {
            $profile_pic = base_url('assets/images/avatar_default.jpg');
        }
        $info = array(
            'name' => $user_info['lastname'] . ' ' . $user_info['firstname'],
            'username' => $username,
            'profile_pic' => $profile_pic,
        );
        return $info;
    }

    public function changeStatusChat() {
        $json = array();
        $this->mongo_db->where(array("user_id" => $this->username))->delete_all('_chatnodejsStatus');
        $this->mongo_db->where(array("user_id" => $this->username))->insert('_chatnodejsStatus', array("user_id" => $this->username, 'status' => $this->input->post('status'), 'date_added' => time()));
        $json['success'] = 'success';
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getCurentStatusChat() {
        $json = array();
        $status = $this->mongo_db->where(array("user_id" => $this->input->post('user_id')))->getOne('_chatnodejsStatus');
        if (!empty($status)) {
            $json['success'] = $status['status'];
        } else {
            $json['success'] = '';
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function actionComment() {
        $json = array();
        $comment_id = $this->input->post('comment_id');
        $action = trim($this->input->post('action'));

        $mes_id = $this->input->post('id');
        $mes_info = $this->mongo_db->where(array('_id' => new mongoId($mes_id)))->getOne('chatMessages');
        $room_id = $mes_info['room_id'];
        $room_info = $this->mongo_db->where(array("_id" => new MongoId($room_id)))->getOne('chatGroups');
        $json['comment_id'] = $mes_info['details']['comment_id'];

        $json['page_id'] = $room_info['page_id'];
        $value = 1;
        if (isset($mes_info) && isset($mes_info['comment_' . $action])) {
            if (!empty($mes_info['comment_' . $action])) {
                $value = 0;
            } else {
                $value = 1;
            }
        }

        if ($action == 'like') {
            $data_sending = array(
                'object_id' => /* '548221642285147_560661741041137' */$mes_info['details']['comment_id'], // comment id
                'page_id' => $room_info['page_id'],
            );
            if ($value == 1) {
                $response = $this->Omnisales->post('me/comment/likes', $data_sending, $this->access_token);
            } else {
                $response = $this->Omnisales->delete('me/comment/likes', $data_sending, $this->access_token);
            }
        } elseif ($action == 'hide') {
            $data_sending = array(
                'object_id' => $mes_info['details']['comment_id'], // comment id
                'page_id' => $room_info['page_id'],
                'is_hidden' => $value,
            );
            $response = $this->Omnisales->post('me/comment/hide', $data_sending, $this->access_token);
        } elseif ($action == 'trash') {
            $data_sending = array(
                'object_id' => $mes_info['details']['comment_id'], // comment id
                'page_id' => $room_info['page_id'],
            );
            $response = $this->Omnisales->post('me/comment/remove', $data_sending, $this->access_token);
        }

        $httpcode = $response->gethttpStatusCode();
        $response = $response->getDecodedBody();
        // var_dump($response);
        if (empty($response['error'])) {
            $action = 'comment_' . $action;
            $this->mongo_db->set(array($action => $value))->where(array('_id' => new mongoId($mes_id)))->update('chatMessages');
            $json['success'] = 'success';
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getUserByExtension($extension) {
        $getAgents = $this->wfpbx_model->getAgent(0, 1, 0);
        if (!empty($getAgents)) {
            foreach ($getAgents as $key => $value) {
                if ($value['extension'] == $extension) {
                    // $getAgents[$key]['type'] = 'extension';
                    return $value;
                }
            }
        }
    }

    public function getDanhba() {
        $results = $this->mongo_db->order_by(array('sort_order' => 1))->get('chatnodejsSettingGroup');
        $danhba = array();
        foreach ($results as $result) {
            $user_array = array();
            foreach ($result['user_array'] as $value) {
                if ($value !== $this->username) {
                    $user_array[] = $this->getUserByExtension($value);
                }
            }
            $danhba[] = array(
                'name' => $result['name'],
                'user_array' => $user_array,
            );
        }
        return $danhba;
    }

    public function updateCloseRoom() {
        $json = array();
        $return = $this->mongo_db->where(array("_id" => new MongoId($this->input->post('room_id'))))->set(array('status' => 0))->update('chatGroups');
        // var_dump($return);
        $json['success'] = $return;
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function AjaxgetPostbyIdFacebook() {
        $json = array();
        $room_id = $this->input->post('room_id');

        $room_info = $this->mongo_db->where(array("_id" => new MongoId($room_id)))->getOne('chatGroups');
        $page_info = $this->mongo_db->where(array("_id" => new MongoId($room_info['page_id'])))->getOne('pageapps');
        $json['post'] = $this->getPostbyIdFacebook($room_info['page_id'], $room_info['to']['post_id']);
        //var_dump($room_info['page_id']);
        //var_dump($room_info['to']['post_id']);
        // $json['page_name'] = $page_info['page_info']['name'];
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getPostbyIdFacebook($page_id, $post_id) {
        $page_info = $this->mongo_db->where(array("_id" => new mongoId($page_id)))->getOne('pageapps');

        $access_token = $page_info['page_info']['access_token'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://graph.facebook.com/v3.1/" . $post_id . "?access_token=" . $access_token . '&fields=message,attachments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"public_account_id\"\r\n\r\n5138989123439296062\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $response = json_decode($response);
            $data_return = array();
            // var_dump($response);
            $data_return['message'] = isset($response->message) ? $response->message : '';
            if (isset($response->attachments->data)) {
                if (isset($response->attachments->data[0]->subattachments->data)) {
                    foreach ($response->attachments->data[0]->subattachments->data as $value) {
                        $data_return['attachments'][] = $value->media->image->src;
                    }
                } else {
                    if (isset($response->attachments->data[0]->media)) {
                        $data_return['attachments'][] = $response->attachments->data[0]->media->image->src;
                    }
                }
            }

            return $data_return;
        }
    }

    public function getFacbookPageById($page_id) {
        $page_info = $this->mongo_db->where(array('_id' => new mongoId($page_id)))->getOne('pageapps');
        if (!empty($page_info)) {
            return array(
                'page_id' => $page_info['page_id'],
                'name' => $page_info['page_info']['name'],
            );
        } else {
            return false;
        }
    }

    public function urlImgF() {
        $page_id = $this->input->get('page_id');
        $user_id = $this->input->get('user_id');
        $people_info = $this->mongo_db->where(array('_id' => new mongoId($user_id)))->getOne('people');
        // var_dump($people_info);
        // var_dump($people_info['people_id']);
        $pageapps = $this->mongo_db->where(array('_id' => new mongoId($page_id)))->getOne('pageapps');
        /* $url = 'https://graph.facebook.com/'.$people_info['people_id'].'/picture?type=normal&access_token='.$pageapps['page_info']['access_token'];
          var_dump($url);exit(); */
        if ($pageapps && !empty($people_info)) {
            $url = 'https://graph.facebook.com/' . $people_info['people_id'] . '/picture?type=normal&access_token=' . $pageapps['page_info']['access_token'];

            echo file_get_contents($url);
            header('Content-Type: image/jpeg');
        }
    }

    //start code Tram 21122018
    public function getPostFacebook() {
        $post_id = $this->input->post('post_id');

        $data_sending = array();
        $array = array();
        $data_return = array();
        // print_r($post_id);
        foreach ($post_id as $value) {

            if (isset($value['details'])) {
                $data_sending[] = array(
                    'page_id' => $this->input->post('page_id'),
                    'post_id' => $value['details']['post_id'],
                );
            }
        }
        $array = array_unique($data_sending, SORT_REGULAR);

        foreach ($array as $dt) {
            $response = $this->Omnisales->get('app/getpost', $dt, $this->access_token);

            $httpcode = $response->gethttpStatusCode();
            $response = $response->getDecodedBody();

            foreach ($response["data"] as $value) {
                $data_return[] = array(
                    "attachments" => $value['attachments'],
                    "content" => $value['content']
                );
            }
        }


        header('Content-Type: application/json');
        echo json_encode($data_return);
    }
    //endcode
   
   
}
