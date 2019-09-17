<?php
include_once('autoload.php');
use Omnisales\Omnisales;
use Omnisales\OmnisalesApp;
$data_config = array( 
	"app_id"     => '400800190000300',
	"app_secret" => '1780b9f4cfc81701f4007c11ee296c07',
);

$Omnisales = new Omnisales($data_config);

$app = new OmnisalesApp('400800190000300', '1780b9f4cfc81701f4007c11ee296c07');
$access_token = $app->getAccessToken();

///////////Test zalo message
/*$data_sending = array(
	'receiver_id' => '5bf9086eeb721d4f5f8e46df', // user id
	'page_id'     => '5bc86ed1eb721d6523f49321',
	'message'     => 'message '.date('d-m-Y H:i:s'),
);
 
$response = $Omnisales->post('me/sendmessage/text', $data_sending, $access_token);

$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);*/

//////////////Image zalo
/*$data_sending = array(
	'receiver_id' => '5bf9086eeb721d4f5f8e46df', // user id
	'page_id'     => '5bc86ed1eb721d6523f49321',
	'url'     => 'https://i-vnexpress.vnecdn.net/2018/11/22/thuthiem13-1542876045-3313-1542876053_500x300.jpg',
);

$response = $Omnisales->post('me/sendmessage/image', $data_sending, $access_token);

$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);*/

/////////// Text messenger
$data_sending = array(
	'receiver_id' => '5bf62581eb721d4d1a2717d2', // user id
	'page_id'     => '5bbed999eb721dfc761ad7e8',
	'message'     => 'message '.date('d-m-Y H:i:s'),
);

$response = $Omnisales->post('me/sendmessage/text', $data_sending, $access_token);

$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);

//////////Image messenger
/*
$data_sending = array(
	'receiver_id' => '5bf62581eb721d4d1a2717d2', // user id
	'page_id'     => '5bbed999eb721dfc761ad7e8',
	'url'     => 'https://i-vnexpress.vnecdn.net/2018/11/22/thuthiem13-1542876045-3313-1542876053_500x300.jpg',
);

$response = $Omnisales->post('me/sendmessage/image', $data_sending, $access_token);

$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);*/


///////////Comment facebook post
/*$data_sending = array(
	'receiver_id' => '5bf9086eeb721d4f5f8e46df', // comment id
	'page_id'     => '5bc86ed1eb721d6523f49321',
	'url'     => 'https://i-vnexpress.vnecdn.net/2018/11/22/thuthiem13-1542876045-3313-1542876053_500x300.jpg',
);

$response = $Omnisales->post('me/comments/asdsada5343', $data_sending, $access_token);

$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);*/

///////////Comment facebook create
/*$data_sending = array(
	'object_id' => '548221642285147_558662881241023', // comment id
	'page_id'     => '5bbed999eb721dfc761ad7e8',
	'message'     => 'test comment'.time(),
);

$response = $Omnisales->post('me/comment/create', $data_sending, $access_token);

$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);*/


/////Facebook add like

/*$data_sending = array(
	'object_id' => '548221642285147_558662881241023', // comment id
	'page_id'     => '5bbed999eb721dfc761ad7e8',
);

$response = $Omnisales->post('me/comment/likes', $data_sending, $access_token);

$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);*/


/////Facebook remove like
/*$data_sending = array(
	'object_id' => '548221642285147_558662881241023', // comment id
	'page_id'     => '5bbed999eb721dfc761ad7e8',
);

$response = $Omnisales->delete('me/comment/likes', $data_sending, $access_token);

$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);*/


/////Facebook  Hide and unline
/*$data_sending = array(
	'object_id' => '548221642285147_558662881241023', // comment id
	'page_id'   => '5bbed999eb721dfc761ad7e8',
	'is_hidden' => true,//'is_hidden' => true, false
);

$response = $Omnisales->post('me/comment/hide', $data_sending, $access_token);
$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);*/


/////Facebook remove comment
/*$data_sending = array(
	'object_id' => '548221642285147_558662881241023', // comment id
	'page_id'   => '5bbed999eb721dfc761ad7e8',
	// 'access_token'   => $access_token,
);

$response = $Omnisales->post('me/sendmessage/text', $data_sending, $access_token);
$httpcode = $response->gethttpStatusCode();
$response = $response->getDecodedBody(); 
var_dump($response);
var_dump($httpcode);*/


//Test authentication