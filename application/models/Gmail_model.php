<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gmail_model extends CI_Model {

	private $size = 10;
	private $client;
	private $gmail;

    function __construct() {
        $this->load->config("googleplus");
		$config = $this->config->item("googleplus");
		$this->client = new Google_Client($config);
		$this->load->library("session");
		$google_access = $this->session->userdata("google_access");
		$this->client->setAccessToken($google_access);
		if ($this->client->isAccessTokenExpired()) {
			if ($this->client->getRefreshToken()) {
	            $this->client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
	        }  else {
	            // Request authorization from the user.
	            throw new Exception("Sign in google");
	            // redirect(base_url("page/signout"), true);
	        }
		}
		$this->gmail = new Google_Service_Gmail($this->client);
		$this->load->helper("gmail");
    }

    function size($size) {
    	$this->size = $size;
    	return $this;
    }

    function listEmail($optParamsGet2) {
    	$gmail = $this->gmail;

    	$list = $gmail->users_messages->listUsersMessages('me', ['maxResults' => $this->size]);

    	$data = array();

	    if($list->getMessages() != null) {

	        foreach ($list->getMessages() as $mlist) {

	            $message_id = $mlist->id;
	            $optParamsGet2['format'] = 'full';
	            $single_message = $gmail->users_messages->get('me', $message_id, $optParamsGet2);
	            //var_dump($single_message);
	            $payload = $single_message->getPayload();
	            //echo "<br>"; var_dump($payload); echo "<br>";
	            // With no attachment, the payload might be directly in the body, encoded.
	            $body = $payload->getBody();
	            $FOUND_BODY = decodeBody($body['data']);

	            // If we didn't find a body, let's look for the parts
	            if(!$FOUND_BODY) {
	                $parts = $payload->getParts();
	                foreach ($parts  as $part) {
	                    if($part['body']) {
	                        $FOUND_BODY = decodeBody($part['body']->data);
	                        break;
	                    }
	                    // Last try: if we didn't find the body in the first parts, 
	                    // let's loop into the parts of the parts (as @Tholle suggested).
	                    if($part['parts'] && !$FOUND_BODY) {
	                        foreach ($part['parts'] as $p) {
	                            // replace 'text/html' by 'text/plain' if you prefer
	                            if($p['mimeType'] === 'text/html' && $p['body']) {
	                                $FOUND_BODY = decodeBody($p['body']->data);
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
	            $headers = $payload->getHeaders();
	            //var_dump($headers);
	            $header_arr = array();
	            foreach ($headers as $header) {
	            	$header_arr[$header["name"]] = $header["value"];
	            }
	            /*pre($single_message);
	            exit();*/
	            $doc = array(
	            	"message_id" 	=> $message_id,
	            	"headers"		=> $header_arr,
	            	"snippet"		=> $single_message->getSnippet(),
	            	"labels"		=> $single_message->getLabelIds(),
	            	"content"		=> $FOUND_BODY
	            );
	            $data[] = $doc;
	        }

	        return $data;
	        /*if ($list->getNextPageToken() != null) {
	            $pageToken = $list->getNextPageToken();
	            $list = $gmail->users_messages->listUsersMessages('me', ['pageToken' => $pageToken, 'maxResults' => 1000]);
	        } else {
	            break;
	        }*/
	    }
    } 
}
