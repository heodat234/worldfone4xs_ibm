<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Googleplus {
	public $option = 'googleplus';

	public function __construct($config=array()) {
		
		$CI =& get_instance();
		if($config)
			$this->initialize($config);
		$CI->config->load('googleplus');
		
		require APPPATH .'third_party/google-login-api/apiClient.php';
		require APPPATH .'third_party/google-login-api/contrib/apiOauth2Service.php';
		
		$this->client = new apiClient();
		$this->client->setApplicationName($CI->config->item('application_name', $this->option));
		$this->client->setClientId($CI->config->item('client_id', $this->option));
		$this->client->setClientSecret($CI->config->item('client_secret', $this->option));
		$this->client->setRedirectUri($CI->config->item('redirect_uri', $this->option));
		$this->client->setDeveloperKey($CI->config->item('api_key', $this->option));
		$this->client->setScopes($CI->config->item('scopes', $this->option));
		$this->client->setAccessType('online');
		$this->client->setApprovalPrompt('auto');
		$this->oauth2 = new apiOauth2Service($this->client);

	}

	public function initialize(array $config = array())
	{
		foreach ($config as $key => $val)
		{
			if (isset($this->$key))
			{
				$this->$key = $val;
			}
		}
		return $this;
	}
	
	public function loginURL() {
        return $this->client->createAuthUrl();
    }
	
	public function getAuthenticate() {
        return $this->client->authenticate();
    }
	
	public function getAccessToken() {
        return $this->client->getAccessToken();
    }
	
	public function setAccessToken() {
        return $this->client->setAccessToken();
    }
	
	public function revokeToken() {
        return $this->client->revokeToken();
    }
	
	public function getUserInfo() {
        return $this->oauth2->userinfo->get();
    }
}
?>