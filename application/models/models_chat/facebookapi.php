<?php

class facebookapi extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
        $this->load->model('api_model');

        $this->access_token = 'EAAJJJdJny5EBAEpHNUXtZBZB20UQDa5nMCaPZCakZBZBn8Ul1koie2ENZBKGOzvrmAlg3T21pYn2h7f1VabpqvtwLiPAPQZARZBlgKROUASkZCv1l8O8tLZBLN7njww4FfYo1wrJzmJ4LW8zr0JfEFlGsiYNSn7ZC4h83rQvBzhAG2XGIkZCkca6znJlaZAxEAt1AXxpJm84DpdtYigZDZD';
    }

    public function curl_Get($url, $args) {
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

        $response = curl_exec($curl);

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

    public function getListPost() {
        $data = array(
                // "access_token" => "EAAJJJdJny5EBAPuoju4fZBLvfLncaQIpOnPIV7PT5rWbXfDa7A3GjQ1pnTcJoK3jZA8csPMVtLddodL6YwfdeI1N11x7ZAFHcL8ZAQ6SfdItwmEmo6La7iwWnP7IqhjrJXXoAkMPXKTojHuZAD6dlqd5FZAdJb7To8NwLNmd1x2ogWuINCxMDVg6ZA1VmtrTUZAsgr0SLd4eHwZDZD"
        );
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/480604385639904/feed?access_token=" . $this->access_token, $data);
        echo "<pre>";
        print_r($response);
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

    public function getComments() {
        $data = array(
                // "access_token" => "EAAJJJdJny5EBAPuoju4fZBLvfLncaQIpOnPIV7PT5rWbXfDa7A3GjQ1pnTcJoK3jZA8csPMVtLddodL6YwfdeI1N11x7ZAFHcL8ZAQ6SfdItwmEmo6La7iwWnP7IqhjrJXXoAkMPXKTojHuZAD6dlqd5FZAdJb7To8NwLNmd1x2ogWuINCxMDVg6ZA1VmtrTUZAsgr0SLd4eHwZDZD"
        );
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/480604385639904_726942737672733/comments?access_token=" . $this->access_token, $data);
        echo "<pre>";
        print_r($response);
    }
    
    public function getConversation() {
        $data = array(
                // "access_token" => "EAAJJJdJny5EBAPuoju4fZBLvfLncaQIpOnPIV7PT5rWbXfDa7A3GjQ1pnTcJoK3jZA8csPMVtLddodL6YwfdeI1N11x7ZAFHcL8ZAQ6SfdItwmEmo6La7iwWnP7IqhjrJXXoAkMPXKTojHuZAD6dlqd5FZAdJb7To8NwLNmd1x2ogWuINCxMDVg6ZA1VmtrTUZAsgr0SLd4eHwZDZD"
        );
        $response = $this->curl_Get("https://graph.facebook.com/v3.1/me/conversations?fields=messages{to,from,message},subject&access_token=". $this->access_token, $data);
        echo "<pre>";
        print_r($response);
    }
//    get conversation_id bai viet gui tin nhan
    public function sentMessagers() {
        $data = array(
            "message" => "This+is+a+test+message"
         );
        $response = $this->curl_Update("https://graph.facebook.com/v3.1/t_1789399521185962/messages?access_token=" . $this->access_token, json_encode($data));
        echo "<pre>";
        print_r($response);
    }

}
