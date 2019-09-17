<?php
class f extends CI_Controller {
  function __construct() {
    parent::__construct();
    $this->load->config('worldui');
    $this->load->model('wfpbx_model');
    // $this->extension = $this->session->userdata("extension");
    // $this->agentname = $this->session->userdata('agentname');
    // Gốc
    $this->app_id = $this->config->item('FACEBOOK_APP_ID');
    $this->app_secret = $this->config->item('FACEBOOK_APP_SECRET');
    $this->username = $this->session->userdata("username");
    $this->name = $this->session->userdata('name');
    //Tạm thời
    // $this->app_id = '331881320561785';
    // $this->app_secret = "dea987df11575b4fc0397c5630e5bf31";
      // $data['title'] = ucfirst('CDR');
    $url_facebook_vendor = __DIR__."/../../libraries/facebookaccess";
    require_once($url_facebook_vendor."/Facebook/Facebook.php");
    require_once($url_facebook_vendor."/Facebook/autoload.php");

    /*$fb = new Facebook\Facebook([
      'app_id' => $this->app_id, // APP ID
      'app_secret' => $this->app_secret,//SECRET
      'default_graph_version' => 'v3.1',
    ]);*/
    // var_dump($fb->getAppSecretProof($this->app_secret));
      // $this->abc = 'sdfsd';
    /**/
    /**/
      
  }
  public function syncConversation(){
    
  }
  public function getFPages(){
    $json = array();
    $pages = $this->mongo_db->where(array('source' => 'facebook', 'created_by'  => $this->username))->order_by(array('status'=> -1))->get('pageapps');
    foreach ($pages as $key => $value) {
      $pages[$key]['avatar'] = "https://graph.facebook.com/". $value['page_id'] ."/picture?height=150&amp;width=150";
      if (isset($value['group_id'])) {
        $group_info = $this->mongo_db->where(array('_id' => new mongoId($value['group_id'])))->getOne('groups');
        if ($group_info) {
          $pages[$key]['group_name'] = $group_info['name'];
        }
      }
      
    }
    header('Content-Type: application/json');
    echo json_encode($pages);
  }

  public function getFPage() {
    $json = array();
    if ($this->input->server('REQUEST_METHOD') === 'GET') {
      $id = $this->input->get('id');
      $json = $this->mongo_db->where(array('created_by' => $this->session->userdata("username"), '_id' => new mongoId($id) ))->getOne('pageapps');

    }
    header('Content-Type: application/json');
    echo json_encode($json);
  }
  public function editFPage($version = 'v1') {
    $json = array();
    if ($this->input->server('REQUEST_METHOD') === 'POST') {
      $id = $this->input->post('id');
      $group_id = $this->input->post('group_id');
      $status = $this->input->post('status');
      $page_info = $this->mongo_db->where(array('created_by' => $this->session->userdata("username"), '_id' => new mongoId($id) ))->getOne('pageapps');
      if ($page_info) {
        $status_sub = $this->checkSubscribed_apps($page_info['page_id'], $page_info['page_info']['access_token']);
        // var_dump($status_sub);
        //Nếu muốn vô hiệu hóa và đã có sub app 
        /*if ($status==0 && $status_sub) {
          $this->deleteSubscribed_apps($page_info['page_id'], $page_info['page_info']['access_token']);
        }*/
        //Nếu muốn sub và chưa có sub app
        if ($status==1 /*&& !$status_sub*/) {
          $this->createSubscribed_apps($page_info['page_id'], $page_info['page_info']['access_token']);
        }
        $this->mongo_db->where(array( '_id' => new mongoId($id)))->set(array('group_id' => $group_id, 'status' => $status ))->update('pageapps');
        $json['success'] = 'Edit FanPage thành công';
      }
    }
    header('Content-Type: application/json');
    echo json_encode($json);
  }

  public function syncFanpage() {
    /*$accessToken_page = "EAADxcsZCyMuwBAFRz0nNRSlUBSkHm1FcXSFkrYK34uAVyZC4Uy2yjtZAEHD1Vk7bD6q9wcg7UxEN5QhUQohqrNgpQnkZClMtxed314uqgqyjcK14w0ZARqRTKjasyyR2iogcpJiWeNAzRw2saOgMkkiMkbWJ3HqUBiIMKAeySgd7D7EIDCV7r6ZAaLuWurruAZD";
    $aa = $this->getPagesByUserAccesstoken($accessToken_page);
    var_dump($aa);
    exit();*/
    $json = array();
    if ($this->input->server('REQUEST_METHOD') === 'POST') {
    $fb = new Facebook\Facebook([
      'app_id' => $this->app_id, // APP ID
      'app_secret' => $this->app_secret,//SECRET
      'default_graph_version' => 'v3.1',
    ]);

    // $helper = $fb->getRedirectLoginHelper();
   /* if (isset($_GET['state'])) {
      $helper->getPersistentDataHandler()->set('state', $_GET['state']);
    }*/
    $accessToken = $this->input->post('access_token');
   /*try {
    $user = $fb->get('/me?fields=id,name,email', $accessToken);

  } catch(Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
  } catch(Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
  }*/
 /* if (! isset($accessToken)) {
    if ($helper->getError()) {
      header('HTTP/1.0 401 Unauthorized');
      echo "Error: " . $helper->getError() . "\n";
      echo "Error Code: " . $helper->getErrorCode() . "\n";
      echo "Error Reason: " . $helper->getErrorReason() . "\n";
      echo "Error Description: " . $helper->getErrorDescription() . "\n";
    } else {
      header('HTTP/1.0 400 Bad Request');
      echo 'Bad request';
    }
  }*/

  $oAuth2Client = $fb->getOAuth2Client();
  $tokenMetadata = $oAuth2Client->debugToken($accessToken);

  $tokenMetadata->validateAppId($this->app_id);
  $tokenMetadata->validateExpiration();

    try {
      $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);    
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
      //echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
    exit;
    }

    $page_gets = $this->getPagesByUserAccesstoken($accessToken->getValue());


    $page_array = array();
    foreach ($page_gets as $page) {
      $status_sub = $this->checkSubscribed_apps($page['id'], $page['access_token']);
      /*$page_array = array(
        'created_by' => $this->username,
        'user_token'   => $accessToken->getValue(),
        'user_info'  => array('user_id' => $this->input->post('id'), 'name' => $this->input->post('name'), 'email' => $this->input->post('email')),
        'page_id'      => $page['id'],
        'page_info'    => $page,
        'status'    => $status_sub ? 1 : 0,
      );*/

      $page_array = array(
        'source'  => 'facebook',
        'created_by'    => $this->username,
        'user_id' => $this->input->post('id'),
        'user_info'   => array(
          'user_token'    => $accessToken->getValue(),
          'user_id' => $this->input->post('id'),
          'name'  => $this->input->post('name'),
          'email' => $this->input->post('email'),
        ),
        'page_id'   => $page['id'],
        'page_info'   => array(
          'name'    => $page['name'],
          'page_id'   => $page['id'],
          'category'  => $page['category'],
          'category_list'  => $page['category_list'],
          'access_token' => $page['access_token'],
        ),
        'status'    => $status_sub ? 1 : 0,
        'date_added'  => time(),
        // 'group_id'    => '',
      );

      if (!$this->getCheckExistPage($page['id'])) {
        $result = $this->mongo_db->insert('pageapps', $page_array);
      }else{
        $page_info = $this->mongo_db->where(array("page_id" => $page['id'], 'created_by' => $this->username ))->get('pageapps');
        if ($page_info) {
          $this->mongo_db->where(array( "page_id" => $page['id'] ))->set($page_array)->update('pageapps');
        }else{
          $json['error'] = 'Trùng fanpage '.$page['name'];
        }
      }
    }
    $json['success'] = 'Sync Done';
   
  }
  header('Content-Type: application/json');
  echo json_encode($json);
}

  public function getPagesByUserAccesstoken($access_token){
    $fb = new Facebook\Facebook([
      'app_id' => $this->app_id, // APP ID
      'app_secret' => $this->app_secret,//SECRET
      'default_graph_version' => 'v3.1',
    ]);

    $helper = $fb->getRedirectLoginHelper();
    if (isset($_GET['state'])) {
      $helper->getPersistentDataHandler()->set('state', $_GET['state']);
    }
    try {
      $response = $fb->get('/me/accounts',$access_token);
    } catch(Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    $graphNode = $response->getGraphEdge();
    $graphNode = $graphNode->asArray();
    return $graphNode;
  }
  public function getCheckExistPage($page_id){
    $page = $this->mongo_db->where(array("page_id" => $page_id ))->get('pageapps');
    if (empty($page)) {
     return false;
   }else{
     return true;
   }   
  }
  public function createSubscribed_apps($page_id, $access_token){
    $fb = new Facebook\Facebook([
      'app_id' => $this->app_id,
      'app_secret' => $this->app_secret,
      'default_graph_version' => 'v2.12',
    ]);
    try {
      $response = $fb->post(
        '/'.$page_id.'/subscribed_apps',
        array (),
        $access_token
      );
    } catch(FacebookExceptionsFacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(FacebookExceptionsFacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $graphNode = $response->getGraphNode();
    $graphNode = $graphNode->asArray();
    if (!empty($graphNode['success']==true)) {
      return true;
    }else{
      return false;
    }
  }

  public function checkSubscribed_apps($page_id, $access_token){
    $fb = new Facebook\Facebook([
        'app_id' => $this->app_id,
        'app_secret' => $this->app_secret,
        'default_graph_version' => 'v2.12',
    ]);

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://graph.facebook.com/v2.12/'.$page_id.'/subscribed_apps?access_token='.$access_token,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      // CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"Ok\"\r\n\r\nbaby\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
      CURLOPT_HTTPHEADER => array(
        "authorization: bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwibmFtZSI6IlBvc1RlYW0iLCJpYXQiOjE1MTYxNzkwMDl9.z_m8wUeaa4IS4WZIKywEJKoQUX4Z4N3rCkXsJsb0h_Y",
        "cache-control: no-cache",
        "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      // echo "cURL Error #:" . $err;
      return false;
    } else {
      $response = json_decode($response);
      $response = $response->{'data'};
       // var_dump($response);
      if (!empty($response)) {
        $bool = false;
        // var_dump($response);
        foreach ($response as $value) {
          if ($value->id == $this->app_id) {
            $bool = true;
          }
          
        }
        return $bool;
      }else{
        return false;
      }
    }
  }
  public function AjaxdeleteSubscribed_apps(){
    $json = array();
    $page_id = $this->input->post('page_id');

    // return;
    $page_info = $this->mongo_db->where(array("page_id" => $page_id ))->getOne('pageapps');
    if (empty($page_info)) {
      $json['error'] = 'error_page';
    }
    if (empty($json['error'])) {
      $result_sub = $this->deleteSubscribed_apps($page_id, $page_info['page_info']['access_token']);
      if ($result_sub) {
        $this->mongo_db->where( array("page_id" => $page_id ))->set(array('status' => 0))->update('pageapps');
        $json['success'] = $result_sub;
      }else{
        $json['error'] = 'Error delete app';
      }
      
    }

    header('Content-Type: application/json');
    echo json_encode($json);
  }
  public function deleteSubscribed_apps($page_id, $access_token){
    $fb = new Facebook\Facebook([
      'app_id' => $this->app_id,
      'app_secret' => $this->app_secret,
      'default_graph_version' => 'v2.12',
    ]);
    try {
      $response = $fb->delete(
        '/'.$page_id.'/subscribed_apps',
        array (),
        $access_token
      );

    } catch(FacebookExceptionsFacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(FacebookExceptionsFacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $graphNode = $response->getGraphNode();
    $graphNode = $graphNode->asArray();
    // var_dump($graphNode);
    if (!empty($graphNode['success']==true)) {
      return true;
    }else{
      return false;
    }
  }

  public function AjaxCreateSubscribed_apps(){
    $json = array();
    $_id = $this->input->post('id');

    $page_info = $this->mongo_db->where(array("_id" => new mongoId($_id),'created_by' => $this->username  ))->getOne('pageapps');
    if (empty($page_info)) {
      $json['error'] = 'error_page';
    }
    if (empty($json['error'])) {
      $result_sub = $this->createSubscribed_apps($page_info['page_id'], $page_info['page_info']['access_token']);
      if ($result_sub) {
        $this->mongo_db->where( array("_id" => new mongoId($_id) ))->set(array('status' => 1))->update('pageapps');
        $json['success'] = $result_sub;
      }
      // $json['success'] = $result_sub;
      
    }

    header('Content-Type: application/json');
    echo json_encode($json);
  }

  public function selfURL() 
   { 
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
    $protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
    return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
  } 

 public function strleft($s1, $s2) { return substr($s1, 0, strpos($s1, $s2)); }

}

?>