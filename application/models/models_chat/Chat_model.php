<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "/libraries/omnisales-sdk/autoload.php";

use Omnisales\Omnisales;
use Omnisales\OmnisalesApp;

class Chat_model extends CI_Model  {

    public function __construct() {
        parent::__construct();
        $this->load->library("session");
        $this->load->library('mongo_db4x');

        $this->username = $this->session->userdata('extension');

        $data_config = array(
            "app_id" => $this->config->item('omnisale_app_id'),
            "app_secret" => $this->config->item('omnisale_app_secret'),
        );

        $this->Omnisales = new Omnisales($data_config);

        $app = new OmnisalesApp($data_config['app_id'], $data_config['app_secret']);
        $this->access_token = $app->getAccessToken();
    }

    // Kiểm tra phòng đã được tạo giữa 2 user
	public function checkRoomUserExists($user_curent, $user_id){
		/*$user_id = 'KH2255803';//$this->input->post('user_id');
		$user_curent = $this->username;*/
		$pipeline = array(
			/*array(
				'$match' => array('type' => 'private')
			),*/
			array(
				'$match' => array(
					'$or' => array(
						array(
							'$and'	=> array(
								array('from.id' => $user_curent),
								array('to.id' => $user_id)
							)
						),
						array(
							'$and'	=> array(
								array('from.id' => $user_id),
								array('to.id' => $user_curent)
							),
						)
					),
					'$and' => array(
						array('status' => 1),
						// array('to.id' => $user_curent)
					)
				)
			),
			/*array(
				'$match' => array(
					'$or' => array(
						array('from.id' => $user_curent),
						array('to.id' => $user_id)
					),
					'$or' => array(
						array('from.id' => $user_id),
						array('to.id' => $user_curent)
					)
				)
			),*/
		);	
		$results = $this->mongo_db4x->aggregate_pipeline("chatGroups", $pipeline);
		// var_dump($results);
		if (!empty($results)) {
			return $results[0]['_id']->{'$id'};
		}else{
			return false;
		}
	}

	function getListOrdersByPeopleId($peopleId) {
        $data = $this->mongo_db4x->where(array('people_id' => $peopleId))->select(array('_id', 'customerName', 'customerPhone', 'deliverAddress', 'Wards_name', 'Province_name', 'Districts_name', 'productName'))->order_by(array('createdTime' => 'desc'))->limit(4)->get('orders');
        return $data;
    }

    function getProductInfoByOrderId($orderId) {
        $temp = $this->mongo_db4x->where(array('order_id' => $orderId))->get('orders_products');
        $productNameList = array_column($temp, 'name');
        $data = implode(", ",$productNameList);
        return $data;
    }

    function addNotes($insertArray) {
        $data = $this->mongo_db4x->insert('notes', $insertArray);
        return $data;
    }

    function getlistNotes($peopleId) {
        $data = $this->mongo_db4x->where(array('people_id' => $peopleId))->order_by(array('created_time' => 'desc'))->limit(3)->get('notes');
        return $data;
    }

    function getProfilePicByUserName($username) {
        $data = $this->mongo_db4x->where(array('username' => $username))->getOne('users');
        return $data;
    }

    function addreportPeople($insertData) {
        $data = $this->mongo_db4x->insert('report_people', $insertData);
        return $data;
    }

    function countPeopleReport($people_id) {
        $data = $this->mongo_db4x->where(array('people_id' => $people_id))->count('report_people');
        return $data;
    }

    function getListLabels() {
        $data = $this->mongo_db4x->order_by(array('stt' => 'asc'))->get('labels');
        return $data;
    }

    function updateLabels($_id, $updateLabelInfo) {
        $labelInfo = $this->mongo_db4x->where(array('_id' => new mongoId($_id)))->select(array('labels'))->getOne('chatGroups');
        $updateValue = array();
        $updateLabelInfo['label_id'] = $updateLabelInfo['_id']['$id'];
        unset($updateLabelInfo['_id']);
        if(!empty($labelInfo['labels'])) {
            $listLabelId = array_column($labelInfo['labels'], 'label_id');
            if(!in_array($updateLabelInfo['label_id'], $listLabelId)) {
                $updateValue = $labelInfo['labels'];
                array_push($updateValue, $updateLabelInfo);
            }
            else return 'existed';
        }
        else {
            array_push($updateValue, $updateLabelInfo);
        }
        $data = $this->mongo_db4x->where(array('_id' => new mongoId($_id)))->set(array('labels' => $updateValue))->update('chatGroups');
        return $data;
    }

    public function getNewNotify() {
        $pipeline = array(
            array(
                '$sort' => array("date_added" => -1),
            ),
        );

        $newNotify_query = $this->mongo_db4x->aggregate_pipeline('chatNotifi', $pipeline);
        $newNotify = array();
        $array_tam = array();
        $array_assigns = array();
        foreach ($newNotify_query as $noti) {
            if ($noti['source'] == 'transfer') {
                if (in_array($this->username, $noti['users'])) {
                    $user_info = $this->getUserInfoByUsername($noti['sender_id']);
                    if (!isset($array_assigns[$noti['source']][$noti['sender_id']][$noti['room_id']])) {
                        $newNotify[] = array(
                            'icon'  => '',
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
                        'icon'  => '',
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
                $group_info = $this->mongo_db4x->where(array('_id' => new mongoId($noti['group_id'])))->getOne('groups');

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
                        'date_added' => date('H:i d/m/Y'),
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
                     // var_dump($noti);
                    $avatar = $noti['sender_info']['profile_pic'];
                    $newNotify[] = array(
                        'id' => $noti['_id']->{'$id'},
                        'avatar' => $avatar,
                        'type' => $noti['source'],
                        'icon' => $icon,
                        'name' => $noti['title'],
                        'user_id' => $noti['sender_id'],
                        'text' => $noti['text'],
                        'date_added' => date('H:i d/m/Y'),
                    );
                    $array_tam[$noti['source']][$noti['sender_id']] = $noti['text'];
                }
            }
        }

        return $newNotify;
    }

    function getUserInfoByUsername($username) {
        $user_info = $this->mongo_db4x->where(array('username' => $username))->getOne('users');
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
}