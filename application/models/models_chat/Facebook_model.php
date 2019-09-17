<?php

class facebook_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
        // $this->load->model('api_model');
        $this->load->model("chat_model");
        $this->parent_user = $this->session->userdata('parent_user');
        $this->username = $this->session->userdata('username');
        $this->name = $this->session->userdata('name');
        $this->access_token = 'EAAJJJdJny5EBAEpHNUXtZBZB20UQDa5nMCaPZCakZBZBn8Ul1koie2ENZBKGOzvrmAlg3T21pYn2h7f1VabpqvtwLiPAPQZARZBlgKROUASkZCv1l8O8tLZBLN7njww4FfYo1wrJzmJ4LW8zr0JfEFlGsiYNSn7ZC4h83rQvBzhAG2XGIkZCkca6znJlaZAxEAt1AXxpJm84DpdtYigZDZD';
        /*var_dump($this->chat_model->checkRoomUserExists('1', '2'));
        exit('sadas');*/
    }

    public function curl_Get($url, $args =array()) {
        $i = 0;
        $arr_line = '';
        if (!empty($args)) {
            foreach ($args as $key => $arg) {
                $arr_line .= ($i == 0) ? '?' : '';
                $arr_line .= $key . '=' . $arg;
                $i++;
            }
        }

        // Logs
        //file_put_contents('debugger/facebookapi.txt', print_r(date('Y-m-d H:i:s') . ' : ' . json_encode($args) . PHP_EOL, true), FILE_APPEND);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . $arr_line,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        $response = curl_exec($curl);
        $responseArr = json_decode($response, true);

        // Logs
        //file_put_contents('debugger/facebookapi.txt', print_r(date('Y-m-d H:i:s') . ' : ' . json_encode($responseArr) . PHP_EOL, true), FILE_APPEND);
        $response = curl_exec($curl);
        if (curl_error($curl)) {
            curl_close($curl);
            responseAPI(400, 'Request ERROR');
        }

        if (empty($responseArr)) {
            curl_close($curl);
            responseAPI(400, 'Failed');
        }

        curl_close($curl);

        return $responseArr;
    }

    public function curl_Update($url, $data, $creat_flag = 0) {
        $curl = curl_init();

        // Logs
        file_put_contents('debugger/UpdateFBAPI.txt', print_r(date('Y-m-d H:i:s') . ' : ' . json_encode($data) . PHP_EOL, true), FILE_APPEND);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data
        ));

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));


        $responseArr = json_decode($response, true);

        // Logs
        file_put_contents('debugger/UpdateFBResponse.txt', print_r(date('Y-m-d H:i:s') . ' : ' . json_encode($responseArr) . PHP_EOL, true), FILE_APPEND);


        if (empty($responseArr)) {
            curl_close($curl);
            responseAPI(400, 'Failed');
        }

        curl_close($curl);

        return $responseArr;
    }

    public function getUserInfo($user_id, $page_id) {
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/".$user_id."?access_token=".$access_token);
        return $response;
    }

    public function createPosts() {

        $data = array(
            "message" => "Những ngày trôi",
            "link" => "http://www.phunungaynay.vn/wp-content/uploads/2015/09/xem-hinh-anh-3d-dep-nhat-ve-thien-nhien-2015-10.jpg"
                // "access_token" => "EAAJJJdJny5EBAKrcRJ8dtgECws3ZCoFW5mGvmraZC9QzrV9dk9Lmp3ZCaV4LpZCLrIofKZB0cYxVwnIG3X4Pm2GugOOHKhooNXqwATpM8813M0hvpKlqQB8meB5b8ufezRS2ZBNwsEJ31FanjnXJzw3uG8f6pQ6sr4upy8d7HnhGfAZA14bJadXtGW4mGxugtYc35Rpm9tj0wZDZD"
        );
        $response = $this->curl_Update("https://graph.facebook.com/v3.1/480604385639904/feed?access_token=" . $this->access_token, json_encode($data));
        echo "<pre>";
        print_r($response);
    }

    public function getPostsByPageId($page_id) {
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];

        $posts = array();
        
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/".$page_info['page_id']."/feed?fields=message,attachments,created_time&access_token=" . $access_token);
        if (isset($response['data'])) {
            $posts = $response['data'];
            if (isset($response['paging']['next'])) {
                $next_url = $response['paging']['next'];
            }else{
                $next_url = '';
            }

            while (!empty($next_url)) {
                $response = $this->curl_Get($next_url);
                $posts = array_merge($posts, $response['data']);
                if (isset($response['paging']['next'])) {
                    $next_url = $response['paging']['next'];
                }else{
                    $next_url = '';
                }
            }
            return $posts;
        }else{
            var_dump($response);
        }
        
    }

    public function createPhoto() {
        $data = array(
            "caption" => "Những ngày trôi về phía cũ",
            "url" => "http://www.phunungaynay.vn/wp-content/uploads/2015/09/xem-hinh-anh-3d-dep-nhat-ve-thien-nhien-2015-10.jpg"
                // "access_token" => "EAAJJJdJny5EBAKrcRJ8dtgECws3ZCoFW5mGvmraZC9QzrV9dk9Lmp3ZCaV4LpZCLrIofKZB0cYxVwnIG3X4Pm2GugOOHKhooNXqwATpM8813M0hvpKlqQB8meB5b8ufezRS2ZBNwsEJ31FanjnXJzw3uG8f6pQ6sr4upy8d7HnhGfAZA14bJadXtGW4mGxugtYc35Rpm9tj0wZDZD"
        );
        $response = $this->curl_Update("https://graph.facebook.com/v3.1/480604385639904/photos?access_token=" . $this->access_token, json_encode($data));
        echo "<pre>";
        print_r($response);
    }

    public function updatePostPhoto() {
        //echo "<b>Những ngày trôi về phía cũ</b>";
        $data = array(
            "message" => "Cảnh đẹp hữu tình \n Lòng người xao xuyến \n Duyên trời tựa may bay",
                // "url"    => "http://www.phunungaynay.vn/wp-content/uploads/2015/09/xem-hinh-anh-3d-dep-nhat-ve-thien-nhien-2015-10.jpg"
                // "access_token" => "EAAJJJdJny5EBAKrcRJ8dtgECws3ZCoFW5mGvmraZC9QzrV9dk9Lmp3ZCaV4LpZCLrIofKZB0cYxVwnIG3X4Pm2GugOOHKhooNXqwATpM8813M0hvpKlqQB8meB5b8ufezRS2ZBNwsEJ31FanjnXJzw3uG8f6pQ6sr4upy8d7HnhGfAZA14bJadXtGW4mGxugtYc35Rpm9tj0wZDZD"
        );
        //truyền id bài viết
        $response = $this->curl_Update("https://graph.facebook.com/v3.1/480604385639904_726942737672733?access_token=" . $this->access_token, json_encode($data));
        echo "<pre>";
        print_r($response);
    }

    //  get conversation_id bai viet gui tin nhan
    public function sentMessagers($conversation_id, $page_id) {
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];
        $data = array(
            "message" => "This+is+a+test+message"
         );
        $response = $this->curl_Update("https://graph.facebook.com/v3.1/t_1789399521185962/messages?access_token=" . $access_token, json_encode($data));
        echo "<pre>";
        print_r($response);
    }

    public function getCommentsByPostId($page_id, $post_id) {
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];

        $comments = array();
        
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/".$post_id."/comments?fields=from,permalink_url,message,created_time&access_token=" . $access_token);
        if (isset($response['data'])) {
            $comments = $response['data'];
            if (isset($response['paging']['next'])) {
                $next_url = $response['paging']['next'];
            }else{
                $next_url = '';
            }

            while (!empty($next_url)) {
                $response = $this->curl_Get($next_url);
                $comments = array_merge($comments, $response['data']);
                if (isset($response['paging']['next'])) {
                    $next_url = $response['paging']['next'];
                }else{
                    $next_url = '';
                }
            }
            return $comments;
        }else{
            var_dump($response);
        } 
    }

    public function getCommentsChillByCommentParentId($page_id, $parentId) {
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];

        $comments = array();
        
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/".$parentId."/comments?fields=from,permalink_url,message,created_time,parent&access_token=" . $access_token);
        if (isset($response['data'])) {
            $comments = $response['data'];
            if (isset($response['paging']['next'])) {
                $next_url = $response['paging']['next'];
            }else{
                $next_url = '';
            }

            while (!empty($next_url)) {
                $response = $this->curl_Get($next_url);
                $comments = array_merge($comments, $response['data']);
                if (isset($response['paging']['next'])) {
                    $next_url = $response['paging']['next'];
                }else{
                    $next_url = '';
                }
            }
            return $comments;
        }else{
            var_dump($response);
        } 
    }

    /*public function getComments() {
        $data = array(
                // "access_token" => "EAAJJJdJny5EBAPuoju4fZBLvfLncaQIpOnPIV7PT5rWbXfDa7A3GjQ1pnTcJoK3jZA8csPMVtLddodL6YwfdeI1N11x7ZAFHcL8ZAQ6SfdItwmEmo6La7iwWnP7IqhjrJXXoAkMPXKTojHuZAD6dlqd5FZAdJb7To8NwLNmd1x2ogWuINCxMDVg6ZA1VmtrTUZAsgr0SLd4eHwZDZD"
        );
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/480604385639904_726942737672733/comments?access_token=" . $this->access_token, $data);
        echo "<pre>";
        print_r($response);
    }*/
    public function getConversations($page_id) {
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];
        $access_token = 'EAADxcsZCyMuwBABz4QyxlxHnAStmrAeGLV8BAuMFPn7ZAcZCTiwZArjj1mA6KTEEAsfBPjqke1gmy8oL0LrnWmAFwjinok45z6tZCNhxUp3n6ZBL8Q89VOhvSf2BCzM00oQk3UKIveb4MCkepY1d5dwrwnY3H9Wj8toJZCAvrPUIVIw5PMH63hv7KmZAmZAgRhf8ZD';
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/me/conversations?access_token=". $access_token);
        $message = array();
        if ($response) {
            foreach ($response['data'] as $key => $value) {
               
            }
        }
        return $message;
    }
    
    public function getConversation($page_id) {
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];
        $access_token = 'EAADxcsZCyMuwBABz4QyxlxHnAStmrAeGLV8BAuMFPn7ZAcZCTiwZArjj1mA6KTEEAsfBPjqke1gmy8oL0LrnWmAFwjinok45z6tZCNhxUp3n6ZBL8Q89VOhvSf2BCzM00oQk3UKIveb4MCkepY1d5dwrwnY3H9Wj8toJZCAvrPUIVIw5PMH63hv7KmZAmZAgRhf8ZD';
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/me/conversations?fields=messages{to,from,message,created_time},subject&access_token=". $access_token);
        $message = array();
        
        if (isset($response['data'])) {
            //$response['data'] các cuộc trò chuyện

            //$value['messages']['data'] trò chuyện của 1 người
            foreach ($response['data'] as $key => $value) {
                $message[$key]['id'] = $value['id'];
                $message[$key]['message'] = $value['messages']['data'];
                $message[$key]['paging'] = $value['messages']['paging']['next'];
                if (isset($value['messages']['paging']['next'])) {
                   $message[$key]['message'][] = $this->getConversationNext($value['messages']['paging']['next']);
                }
                break;
            }
        }else{
            var_dump($response);
        }
        return $message;
    }

    public function syncPostsFacebookFanpage($page_id){
        $posts = $this->getPostsByPageId($page_id);
        
        if (!empty($posts)) {
            $data_post=array();
            foreach ($posts as $key => $post) {
                $attachments = array();
                if (isset($post['attachments'])) {
                    if (isset($post['attachments']['data'])) {
                        foreach ($post['attachments']['data'] as $attachment) {
                            if (isset($attachment['subattachments']['data'])) {                            
                                foreach ($attachment['subattachments']['data'] as $subattachment) {
                                    $attachments[] = array(
                                        'url'   => $subattachment['media']['image']['src'],
                                        'height'   => $subattachment['media']['image']['height'],
                                        'width'   => $subattachment['media']['image']['width'],
                                    );
                                }
                            }
                        }
                    }
                }
                $data_post[] = array(
                    'source'    => 'facebook',
                    'id'        => $post['id'],
                    'content'    => isset($post['message']) ? $post['message'] : '' ,
                    'timestamp' => strtotime($post['created_time']),
                    'attachments'   => $attachments,

                );
                
             
            }
           return $data_post;
        }
    }

    public function syncCommentsFacebookFanpage($page_id){
        $posts = $this->getPostsByPageId($page_id);
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];
        if (!empty($posts)) {
            foreach ($posts as $key => $post) {
                /*if ($post['id']!='330162184091095_405331809907465') {
                   continue;
                }*/
                $comment_posts = $this->getCommentsByPostId($page_id, $post['id']);
                // var_dump($comment_posts);

                foreach ($comment_posts as $comment) {//comment_parents
                    if (empty($comment['message']) || !isset($comment['from']['id'])) {
                        continue;
                    }

                    /*people*/
                    if ($page_info['page_id']!=$comment['from']['id']) {
                        $people_app_id = $comment['from']['id'];
                        $name = $comment['from']['name'];
                        //Kiểm tra user tồn tại hay chưa
                        $people_info = $this->mongo_db->where(array( 'people_id' => $people_app_id, 'page_id' => $page_id ))->getOne('people');
                        if (empty($people_info)) {
                            $people_data = array(
                                'source'      => 'facebook',
                                'people_id'   => $people_app_id,
                                'page_id'     => $page_info['_id']->{'$id'},
                                'name'        => $name,
                                'phone'       => '',
                                'email'       => '',
                                'address'     => '',
                                'profile_pic' => 'https://graph.facebook.com/'. $people_app_id .'/picture?height=100&width=100',
                                'locale'      => '',
                                'timezone'    => '',
                                'gender'      => '',
                                'date_added'  => time(),
                            );
                            //var_dump($people_data); exit();
                            $people_insert = $this->mongo_db->insert('people', $people_data);
                            $people_id = $people_insert->{'$id'};
                            $sender_id = $people_insert->{'$id'};

                        }else{
                            //var_dump($people_info); exit();
                            $people_id = $people_info['_id']->{'$id'};
                            $sender_id = $people_info['_id']->{'$id'};
                        }
                    }else{
                        continue;
                        /*$sender_id = $page_info['_id']->{'$id'};
                        $name = $comment['from']['name'];*/
                    }
                    /*end people*/

                    //create room
                    //Kiểm tra có room chưa trước khi tạo
                    $room_info = $this->mongo_db->where(array('to.user_id' => $sender_id,'to.post_id' => $post['id'], 'source' => 'facebook', 'status' => 1 ))->getOne('chatGroups');
                    if (empty($room_info)) {
                        $room_array = array(
                            "user_id_create" => $this->parent_user,
                            "page_id"        => $page_info['_id']->{'$id'},
                            "trigger"        => "comment",
                            "source"         => "facebook",
                            'type'           => 'private',//private/group
                            'from'           => array("id" => $this->parent_user),
                            "to"             => array(
                                "comment_id" => $comment['id'],
                                "user_id"    => $people_id,
                                "post_id" => $post['id'], 
                                "post_url"   => $comment['permalink_url']
                            ), 
                            'group_user'     => '',
                            'group_name'     => '',
                            'date_active'    => strtotime($comment['created_time']),
                            'date_added'     => strtotime($comment['created_time']),
                            'status'         => 1,
                        );
                        $result = $this->mongo_db->insert('chatGroups', $room_array);
                        $room_id = $result->{'$id'};
                    }else{
                        $room_id = $room_info['_id']->{'$id'};
                    }
                    

                    $data_insert = array(
                        "trigger" => "comment",
                        "source" => "facebook", 
                        "type" => "text", 
                        "page_id" => $page_id, 
                        "sender_id" => $sender_id, 
                        "sender_info" => array(
                            "user_id" => $sender_id, 
                        ), 
                        "details" => array(
                            "type" => "status", 
                            "permalink_url" => $comment['permalink_url'], 
                            "id" => $comment['id'], 
                            "status_type" => "mobile_status_update", 
                                    // "is_published" => true
                        ), 
                        "room_id" => $room_id, 
                        "comment_id" => $comment['id'], 
                        "text" => $comment['message'], 
                        "date_added" => strtotime($comment['created_time']),
                    );
                    $this->mongo_db->insert('chatMessages', $data_insert);
                    //comment chill
                    $comment_chills = $this->getCommentsChillByCommentParentId($page_id, $comment['id']);
                    // var_dump($comment_chills);
                    foreach ($comment_chills as $comment_chill) {
                        if (!isset($comment_chill['from']['id']) || !in_array($comment_chill['from']['id'], array($comment['from']['id'], $page_info['page_id']))) {
                            continue;
                        }
                        if ($page_info['page_id']!=$comment_chill['from']['id']) {
                            $people_app_id = $comment_chill['from']['id'];
                            $name = $comment_chill['from']['name'];
                                //Kiểm tra user tồn tại hay chưa
                            $people_info = $this->mongo_db->where(array( 'people_id' => $people_app_id, 'page_id' => $page_id ))->getOne('people');
                            if (empty($people_info)) {
                                $people_data = array(
                                    'source'      => 'facebook',
                                    'people_id'   => $people_app_id,
                                    'page_id'     => $page_info['_id']->{'$id'},
                                    'name'        => $name,
                                    'phone'       => '',
                                    'email'       => '',
                                    'address'     => '',
                                    'profile_pic' => 'https://graph.facebook.com/'. $people_app_id .'/picture?height=100&width=100',
                                    'locale'      => '',
                                    'timezone'    => '',
                                    'gender'      => '',
                                    'date_added'  => time(),
                                );
                                    //var_dump($people_data); exit();
                                $people_insert = $this->mongo_db->insert('people', $people_data);
                                $people_id = $people_insert->{'$id'};
                                $sender_chill_id = $people_insert->{'$id'};

                            }else{
                                // var_dump($people_info);
                                $people_id = $people_info['_id']->{'$id'};
                                $sender_chill_id = $people_info['_id']->{'$id'};
                            }
                        }else{
                            // continue;
                                $sender_chill_id = $page_info['_id']->{'$id'};
                                $name = $comment['from']['name'];
                        }
                        $data_insert = array(
                            "trigger" => "comment",
                            "source" => "facebook", 
                            "type" => "text", 
                            "page_id" => $page_id, 
                            "sender_id" => $sender_chill_id, 
                            "sender_info" => array(
                                "user_id" => $sender_chill_id, 
                            ), 
                            "details" => array(
                                "type" => "status", 
                                "permalink_url" => $comment_chill['permalink_url'], 
                                "id" => $comment_chill['id'], 
                                "status_type" => "mobile_status_update", 
                                    // "is_published" => true
                            ), 
                            "room_id" => $room_id, 
                            "comment_id" => $comment_chill['id'], 
                            "text" => $comment_chill['message'], 
                            "date_added" => strtotime($comment_chill['created_time']),
                        );
                        $this->mongo_db->insert('chatMessages', $data_insert);
                    }
                    

                }                
            }
        }
    }

    public function syncConversationFacebookFanpage($page_id){
        set_time_limit(0);
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];
        // var_dump($access_token);
        // $access_token = 'EAADxcsZCyMuwBAHkRZCRZCIYLhsGNCvvKsvrC9WFusIsZC513oT7OWpY4UxeWO6yhGVLmWIsubmUImIcvWgZBtQA42d8rgoDS5tMtEuIegqizZCCMefdCI0RqnPiNhbhlao486yz0t7myUblySlc0lTZAXm7gN1FYXbZCI3WCUB9vatvdCA6DZAXeRbq7k265aq9nZB60XKDqzUklx1mgZBFXjDc5G7Nk6Mp28ZD';
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/me/conversations?fields=senders,link,updated_time,from,private_reply_conversation&access_token=". $access_token);
        
        $message = array();
        if (isset($response['data'])) {
            $conversations = $response['data'];
            if (isset($response['paging']['next'])) {
                $next_url = $response['paging']['next'];
            }else{
                $next_url = '';
            }

            while (!empty($next_url)) {
                $response = $this->curl_Get($next_url);
                $conversations = array_merge($conversations, $response['data']);
                if (isset($response['paging']['next'])) {
                    $next_url = $response['paging']['next'];
                }else{
                    $next_url = '';
                }
            }
            foreach ($response['data'] as $key => $value) {
                $data_insert = array(
                    'parent_user'   => $this->parent_user,
                    'page_id'       => $page_id,
                    'data'          => $value,
                    'timestamp'     => time(),
                );
               $this->mongo_db->insert('cron_facebook_conversations', $data_insert);
            }
        }else{
            var_dump($response);
        }
        // $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        // $access_token = $page_info['page_info']['access_token'];
        // $this->getConversations($page_id);

    }

    public function cronSyncMessagesbyConversationFacebook($page_id){
        $page_info = $this->mongo_db->where( array("_id" => new mongoId($page_id) ))->getOne('pageapps');
        $access_token = $page_info['page_info']['access_token'];
        /*var_dump($page_info);
        exit();*/
        $conversations = $this->mongo_db->order_by(array("timestamp" => -1))->limit(50)->get('cron_facebook_conversations');
        // var_dump($conversations);//t_1741229359322843
        // $access_token = 'EAADxcsZCyMuwBAHkRZCRZCIYLhsGNCvvKsvrC9WFusIsZC513oT7OWpY4UxeWO6yhGVLmWIsubmUImIcvWgZBtQA42d8rgoDS5tMtEuIegqizZCCMefdCI0RqnPiNhbhlao486yz0t7myUblySlc0lTZAXm7gN1FYXbZCI3WCUB9vatvdCA6DZAXeRbq7k265aq9nZB60XKDqzUklx1mgZBFXjDc5G7Nk6Mp28ZD';
        /*$page_id = '';*/
        // var_dump($conversations[0]['data']['senders']['data'][0]['id']);
        
        foreach ($conversations as $key => $value) {
            // var_dump($value['data']['senders']['data'][0]['id']);exit();
            if ($page_info['page_id']!=$value['data']['senders']['data'][0]['id']) {
                $people_app_id = $value['data']['senders']['data'][0]['id'];
                $name = $value['data']['senders']['data'][0]['name'];
                $email = $value['data']['senders']['data'][0]['email'];
            }else{
                $people_app_id = $value['data']['senders']['data'][1]['id'];
                $name = $value['data']['senders']['data'][1]['name'];
                $email = $value['data']['senders']['data'][1]['email'];
            }
            // exit('aaa');
            // var_dump($people_app_id);
            $messages = $this->getConversationMessages($value['data']['id'],$access_token);//$this->curl_Get("https://graph.facebook.com/v3.1/".$value['data']['id']."/messages?fields=message,created_time,to,from,attachments&access_token=". $access_token);
            /*var_dump($messages);
            exit();*/

            //Kiểm tra user tồn tại hay chưa
            $people_info = $this->mongo_db->where(array( 'people_id' => $people_app_id, 'page_id' => $page_id ))->getOne('people');
            if (empty($people_info)) {
                $people_data = array(
                    'source'      => 'facebook',
                    'people_id'   => $people_app_id,
                    'page_id'     => $page_info['_id']->{'$id'},
                    'name'        => $name,
                    'phone'       => '',
                    'email'       => $email,
                    'address'     => '',
                    'profile_pic' => 'https://graph.facebook.com/'. $people_app_id .'/picture?height=100&width=100',
                    'locale'      => '',
                    'timezone'    => '',
                    'gender'      => '',
                    'date_added'  => time(),
                );
                /*var_dump($people_data);
                exit();*/
                $people_insert = $this->mongo_db->insert('people', $people_data);
                $people_id = $people_insert->{'$id'};

            }else{
                $people_id = $people_info['_id']->{'$id'};
            }
            
            //create room
            $room_array = array(
                "user_id_create" => $value['parent_user'],
                "page_id"        => $page_info['_id']->{'$id'},
                'conversation_id'   => $value['data']['id'],
                "trigger"        => "message",
                "source"         => "messenger",
                'type'           => 'private',//private/group
                'from'           => array("id" => $value['parent_user']),
                'to'             => array("user_id" => $people_id),
                'group_user'     => '',
                'group_name'     => '',
                'date_active'    => strtotime($value['data']['updated_time']),
                'date_added'     => strtotime($value['data']['updated_time']),
                'status'         => 1,
            );
            $result = $this->mongo_db->insert('chatGroups', $room_array);
            $room_id = $result->{'$id'};
            // end create room
            foreach ($messages as $message) {
                if ($message['from']['id'] == $page_info['page_id']) {
                    $sender_id = $page_info['_id']->{'$id'};
                }else{
                    $sender_id = $people_id; 
                }
                $url = '';
                $type = 'text';
                if (isset($message['attachments'])) {
                    // var_dump($message['attachments']['data'][0]['image_data']['url']);
                    if ($message['attachments']['data'][0]['mime_type'] =="image/jpeg") {
                       
                        if (isset($message['attachments']['data'][0]['image_data']['url'])) {
                            var_dump($message['attachments']['data'][0]);
                            $type = 'image';
                            $url = $message['attachments']['data'][0]['image_data']['url'];
                        }else{
                            continue;
                            // $type = 'image';
                            // $url = $message['attachments']['data'][0]['image_data']['file_url'];
                        }
                        
                    }
                }
                
                /*$date_added = new DateTime($message['created_time'], new DateTimeZone('UTC'));
                $date_added->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
                $date_added = $date_added->format('Y-m-d H:i:s');*/

                $message_data = array(
                    'trigger'        => 'message',
                    'source'         => 'messenger',
                    'type'           => $type,
                    'page_id'        => $value['page_id'],
                    'sender_id'      => $sender_id,
                    'sender_info'    => array(
                        'name'      => $message['from']['name'],
                        'user_id'   => $sender_id, 
                    ),
                    'room_id'        => $room_id,
                    'message_app_id' => $message['id'],
                    'text'           => $message['message'],
                    'url'            => $url,
                    'date_added'     => strtotime($message['created_time']),
                );
                // var_dump($message_data);
                $this->mongo_db->insert('chatMessages', $message_data);
            }
            // exit('aab');
            
            // Gởi cho socket giao diện báo là room này đã chuẩn bị xong
            // $this->sendUrl($this->omni_webhook_socket_url,$message_data);
            
        }
    }

    public function getConversationMessages($conversation_id, $access_token) {
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/".$conversation_id."/messages?fields=message,created_time,to,from,attachments&limit=499&access_token=". $access_token);
        $message = array();
        if (isset($response['data'])) {
            $message = $response['data'];
            // $message[0]['message'] = 'he111111111111111111';
            
            if (isset($response['paging']['next'])) {
                $next_url = $response['paging']['next'];
            }else{
                $next_url = '';
            }

            while (!empty($next_url)) {
                $response = $this->curl_Get($next_url);
                $message = array_merge($message, $response['data']);
                if (isset($response['paging']['next'])) {
                    $next_url = $response['paging']['next'];
                }else{
                    $next_url = '';
                }
            }
            
        }else{
            var_dump($response);
        }

        return $message;
    }
    public function getConversationNext($next_url ='') {
        $message = array();//$message[$key]['message']
        
        while (!empty($next_url)) {
            $response = $this->curl_Get($next_url);
            $message[] = $response['data'];
            if (isset($response['paging']['next'])) {
                $next_url = $response['paging']['next'];
            }else{
                $next_url = '';
            }
            
        }
        return $message;
    }

    public function createRoomUser($user_id){
        $json = array();
        if ($this->checkRoomUserExists($this->username, $this->input->post('user_id'))) {
            $json['room_id'] = $this->checkRoomUserExists($this->username, $this->input->post('user_id'));
            // $json['newroom'] = 'no';
            return $json['room_id'];
        }else{
            // $json['newroom'] = 'yes';
            $room_array = array(
                'user_id_create'    => $this->username,
                'type'  => $this->input->post('type'),//private/group
                'from'  => array("id" => $this->username, "username" => $this->name, "type" => "extension"),
                'to'    => array("user_id" => $this->input->post('user_id'), "username" => $this->input->post('user_name'), "type"  => $this->input->post('user_type')),
                'group_user'    => '',
                'group_name'    => '',
                'date_active'   => time(),
                'date_added'    => time(),
                'status'    => 1,
            );
            $result = $this->mongo_db->insert('chatGroups', $room_array);
            $json['room_id'] = $result->{'$id'};
            // $json['user_ids'] = ;
        }

        return $result->{'$id'};
    }

    /*public function checkPeople(){
        $people_info = $this->mongo_db->where(array( 'people_id' => $recipient_id, 'page_id' => $page_id ))->getOne('people');
        if (empty($people_info)) {
            $people_data = array(
                'source'      => 'messenger',
                'people_id'   => $recipient_id,
                'page_id'     => $page_id,
                'name'        => $userInfo->last_name .' '. $userInfo->first_name,
                'phone'       => '',
                'email'       => '',
                'address'     => '',
                'profile_pic' => $userInfo->profile_pic,
                'locale'      => $userInfo->locale,
                'timezone'    => $userInfo->timezone,
                'gender'      => $userInfo->gender,
                'date_added'  => time(),
            );
            $people_insert = $this->mongo_db->insert('people', $people_data);
            $recipient_id = $people_insert->{'$id'};
        }else{
            $recipient_id = $people_info['_id']->{'$id'};
        }
    }*/

}
