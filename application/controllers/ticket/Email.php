<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email extends WFF_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		exit("Đang phát triển");
		// Get the API client and construct the service object.
		// $client = $this->getClient();
		$client = new Google_Client();
		$this->load->library("session");
		$google_access = $this->session->userdata("google_access");
		$client->setAccessToken($google_access);
		$service = new Google_Service_Gmail($client);
		$messages = $this->listMessages($service, "107603334117827701912");
		echo "<pre>";
		echo print_r($messages);
	}

	function test()
	{
		$this->load->model("gmail_model");
		$list = $this->gmail_model->listEmail(array("format" => "full"));
		pre($list);
	}

	function getlistgmail()
	{
		// Get the API client and construct the service object.
		// $client = $this->getClient();
		$this->load->config("googleplus");
		$config = $this->config->item("googleplus");
		$client = new Google_Client($config);
		$this->load->library("session");
		$google_access = $this->session->userdata("google_access");
		$client->setAccessToken($google_access);
		if ($client->isAccessTokenExpired()) {
			if ($client->getRefreshToken()) {
	            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
	        }  else {
	            // Request authorization from the user.
	            redirect(base_url("page/signout"), true);
	        }
		}
		$gmail = new Google_Service_Gmail($client);
		$userId = "107603334117827701912";
		$messageId = "1687d9cf5556c892";
		//$messages = $this->getMessage($service, $userId, $messageId);
		//$messages = $service->users_messages->get($userId, $messageId);
		//pre($messages);

		$list = $gmail->users_messages->listUsersMessages('me', ['maxResults' => 1]);
		try{
		    while ($list->getMessages() != null) {

		        foreach ($list->getMessages() as $mlist) {

		            $message_id = $mlist->id;
		            $optParamsGet2['format'] = 'full';
		            $single_message = $gmail->users_messages->get('me', $message_id);
		            $payload = $single_message->getPayload();

		            // With no attachment, the payload might be directly in the body, encoded.
		            $body = $payload->getBody();
		            $FOUND_BODY = $this->decodeBody($body['data']);

		            // If we didn't find a body, let's look for the parts
		            if(!$FOUND_BODY) {
		                $parts = $payload->getParts();
		                foreach ($parts  as $part) {
		                    if($part['body']) {
		                        $FOUND_BODY = $this->decodeBody($part['body']->data);
		                        break;
		                    }
		                    // Last try: if we didn't find the body in the first parts, 
		                    // let's loop into the parts of the parts (as @Tholle suggested).
		                    if($part['parts'] && !$FOUND_BODY) {
		                        foreach ($part['parts'] as $p) {
		                            // replace 'text/html' by 'text/plain' if you prefer
		                            if($p['mimeType'] === 'text/html' && $p['body']) {
		                                $FOUND_BODY = $this->decodeBody($p['body']->data);
		                                break;
		                            }
		                        }
		                    }
		                    if($FOUND_BODY) {
		                        break;
		                    }
		                }
		            }
		            // Finally, print the message ID and the body
		            echo "DUNGHEAD: ";
		            print_r($message_id . " : " . $FOUND_BODY);
		            echo "<br>";
		        }

		        if ($list->getNextPageToken() != null) {
		            $pageToken = $list->getNextPageToken();
		            $list = $gmail->users_messages->listUsersMessages('me', ['pageToken' => $pageToken, 'maxResults' => 1000]);
		        } else {
		            break;
		        }
		    }
		} catch (Exception $e) {
		    echo $e->getMessage();
		}
	}

	function decodeBody($body) {
	    $rawData = $body;
	    $sanitizedData = strtr($rawData,'-_', '+/');
	    $decodedMessage = base64_decode($sanitizedData);
	    if(!$decodedMessage){
	        $decodedMessage = FALSE;
	    }
	    return $decodedMessage;
	}


	function getMessage($service, $userId, $messageId) {
	  try {
	    $message = $service->users_messages->get($userId, $messageId);
	    return $message;
	  } catch (Exception $e) {
	    print 'An error occurred: ' . $e->getMessage();
	  }
	}

	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */
	function getClient()
	{
	    $client = new Google_Client();
	    $client->setApplicationName('Gmail API PHP Quickstart');
	    $client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
	    $client->setAuthConfig('credentials.json');
	    $client->setAccessType('offline');
	    $client->setPrompt('select_account consent');
	    // Load previously authorized token from a file, if it exists.
	    // The file token.json stores the user's access and refresh tokens, and is
	    // created automatically when the authorization flow completes for the first
	    // time.
	    $tokenPath = 'token.json';
	    if (file_exists($tokenPath)) {
	        $accessToken = json_decode(file_get_contents($tokenPath), true);
	        $client->setAccessToken($accessToken);
	    }
	    // If there is no previous token or it's expired.
	    if ($client->isAccessTokenExpired()) {
	        // Refresh the token if possible, else fetch a new one.
	        if ($client->getRefreshToken()) {
	            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
	        } else {
	            // Request authorization from the user.
	            $authUrl = $client->createAuthUrl();
	            printf("Open the following link in your browser:\n%s\n", $authUrl);
	            print 'Enter verification code: ';
	            $authCode = trim(fgets(STDIN));
	            // Exchange authorization code for an access token.
	            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
	            $client->setAccessToken($accessToken);
	            // Check to see if there was an error.
	            if (array_key_exists('error', $accessToken)) {
	                throw new Exception(join(', ', $accessToken));
	            }
	        }
	        // Save the token to a file.
	        if (!file_exists(dirname($tokenPath))) {
	            mkdir(dirname($tokenPath), 0700, true);
	        }
	        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
	    }
	    return $client;
	}


	private function listMessages($service, $userId) {
		  $pageToken = NULL;
		  $messages = array();
		  $opt_param = array();
		  do {
		    try {
		      if ($pageToken) {
		        $opt_param['pageToken'] = $pageToken;
		      }
		      $messagesResponse = $service->users_messages->listUsersMessages($userId, $opt_param);
		      if ($messagesResponse->getMessages()) {
		        $messages = array_merge($messages, $messagesResponse->getMessages());
		        $pageToken = $messagesResponse->getNextPageToken();
		      }
		    } catch (Exception $e) {
		      print 'An error occurred: ' . $e->getMessage();
		    }
		  } while ($pageToken);

		  return $messages;
	}
}
