<?php
/**
 * Omnisales © 2018
 *
 */

namespace Omnisales;

/*use Omnisales\Authentication\AccessToken;
use Omnisales\Authentication\OAuth2Client;
use Omnisales\Authentication\OmnisalesRedirectLoginHelper;
use Omnisales\Url\UrlDetectionInterface;
use Omnisales\Url\OmnisalesUrlDetectionHandler;
use Omnisales\HttpClients\HttpClientsFactory;
use Omnisales\Exceptions\OmnisalesSDKException;
use Omnisales\OmnisalesApp;
use Omnisales\OmnisalesOA;
use Omnisales\OmnisalesClient;
use Omnisales\OmnisalesRequest;*/
// include_once('OmnisalesApp.php');
use Omnisales\Exceptions\OmnisalesSDKException;
use Omnisales\OmnisalesApp;
use Omnisales\OmnisalesClient;
use Omnisales\OmnisalesRequest;
use Omnisales\HttpClients\HttpClientsFactory;


/**
 * Class Omnisales
 *
 * @package Omnisales
 */
class Omnisales
{
    /**
     * @const string Version number of the Omnisales PHP SDK.
     */
    const VERSION = '0.1';
    /**
     * @const string Default Graph API version for requests.
     */
    // const DEFAULT_GRAPH_VERSION = 'v2.0';
    const DEFAULT_GRAPH_VERSION = '';
    /**
     * @const string Default OAuth API version for requests.
     */
    const DEFAULT_OAUTH_VERSION = 'v3';
    /**
     * @const string Default OfficalAccount API version for requests.
     */
    const DEFAULT_OA_VERSION = 'v1';
    /**
     * @const string The name of the environment variable that contains the app ID.
     */
    const APP_ID_ENV_NAME = 'OMNISALES_APP_ID';
    /**
     * @const string The name of the environment variable that contains the app secret.
     */
    const APP_SECRET_ENV_NAME = 'OMNISALES_APP_SECRET';
    /**
     * @const string The name of the environment variable that contains the Offical Account ID.
     */
    const OA_ID_ENV_NAME = 'OMNISALES_OA_ID';
    /**
     * @const string The name of the environment variable that contains the Offical Account secret key.
     */
    const OA_SECRET_ENV_NAME = 'OMNISALES_OA_SECRET';
    /**
     * @var OmnisalesOA The OmnisalesOA entity.
     */
    protected $oaInfo;
    
    /**
     * @const int OAuth api type.
     */
    const API_TYPE_AUTHEN = 0;
    
    /**
     * @const int Graph api type.
     */
    const API_TYPE_GRAPH = 1;
    
    /**
     * @const int OfficalAccount api type.
     */
    const API_TYPE_OA = 2;
    
    /**
     * @const int OfficalAccount api onbehalf type.
     */
    const API_TYPE_OA_ONBEHALF = 3;
    
    /**
     * @var OmnisalesApp The OmnisalesApp entity.
     */
    protected $app;
    /**
     * @var OmnisalesClient The Omnisales client service.
     */
    protected $client;
    /**
     * @var OAuth2Client The OAuth 2.0 client service.
     */
    protected $oAuth2Client;
    /**
     * @var UrlDetectionInterface|null The URL detection handler.
     */
    protected $urlDetectionHandler;
    /**
     * @var AccessToken|null The default access token to use with requests.
     */
    protected $defaultAccessToken;
    /**
     * @var OmnisalesResponse|OmnisalesBatchResponse|null Stores the last request made to Graph.
     */
    protected $lastResponse;
    
    /**
     * Instantiates a new Omnisales super-class object.
     *
     * @param array $config
     *
     * @throws OmnisalesSDKException
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'app_id' => getenv(static::APP_ID_ENV_NAME),
            'app_secret' => getenv(static::APP_SECRET_ENV_NAME),
            'enable_beta_mode' => false,
            'http_client_handler' => 'curl',
            'url_detection_handler' => null,
        ], $config);
        if (!$config['app_id']) {
            throw new OmnisalesSDKException('Required "app_id" key not supplied in config and could not find fallback environment variable "' . static::APP_ID_ENV_NAME . '"');
        }
        if (!$config['app_secret']) {
            throw new OmnisalesSDKException('Required "app_secret" key not supplied in config and could not find fallback environment variable "' . static::APP_SECRET_ENV_NAME . '"');
        }
        $this->app = new OmnisalesApp($config['app_id'], $config['app_secret']);
        // var_dump($this->app);
        // $aa = $this->app->getAccessToken();
        // var_dump($aa->getValue());
        /*$this->oaInfo = new OmnisalesOA($config['oa_id'], $config['oa_secret']);
        */
        $this->client = new OmnisalesClient(
            HttpClientsFactory::createHttpClient($config['http_client_handler']),
            $config['enable_beta_mode']
        );
        /*
        $this->setUrlDetectionHandler($config['url_detection_handler'] ?: new OmnisalesUrlDetectionHandler());
        if (isset($config['default_access_token'])) {
            $this->setDefaultAccessToken($config['default_access_token']);
        }*/
    }
    /**
     * Returns the OmnisalesApp entity.
     *
     * @return OmnisalesApp
     */
    public function getApp()
    {
        return $this->app;
    }
    /**
     * Returns the OmnisalesClient service.
     *
     * @return OmnisalesClient
     */
    public function getClient()
    {
        return $this->client;
    }
    /**
     * Returns the OAuth 2.0 client service.
     *
     * @return OAuth2Client
     */
    public function getOAuth2Client()
    {
        if (!$this->oAuth2Client instanceof OAuth2Client) {
            $app = $this->getApp();
            $client = $this->getClient();
            $this->oAuth2Client = new OAuth2Client($app, $client, static::DEFAULT_OAUTH_VERSION);
        }
        return $this->oAuth2Client;
    }
    /**
     * Returns the last response returned from Graph.
     *
     * @return OmnisalesResponse|OmnisalesBatchResponse|null
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
    /**
     * Returns the URL detection handler.
     *
     * @return UrlDetectionInterface
     */
    public function getUrlDetectionHandler()
    {
        return $this->urlDetectionHandler;
    }
    /**
     * Changes the URL detection handler.
     *
     * @param UrlDetectionInterface $urlDetectionHandler
     */
    private function setUrlDetectionHandler(UrlDetectionInterface $urlDetectionHandler)
    {
        $this->urlDetectionHandler = $urlDetectionHandler;
    }
    /**
     * Returns the default AccessToken entity.
     *
     * @return AccessToken|null
     */
    public function getDefaultAccessToken()
    {
        return $this->defaultAccessToken;
    }
    /**
     * Sets the default access token to use with requests.
     *
     * @param AccessToken|string $accessToken The access token to save.
     *
     * @throws \InvalidArgumentException
     */
    public function setDefaultAccessToken($accessToken)
    {
        if (is_string($accessToken)) {
            $this->defaultAccessToken = new AccessToken($accessToken);
            return;
        }
        if ($accessToken instanceof AccessToken) {
            $this->defaultAccessToken = $accessToken;
            return;
        }
        throw new \InvalidArgumentException('The default access token must be of type "string" or Omnisales\AccessToken');
    }
    /**
     * Returns the default Graph version.
     *
     * @return string
     */
    public function getDefaultGraphVersion()
    {
        return $this->defaultGraphVersion;
    }
    
    /**
     * Returns the default OAuth version.
     *
     * @return string
     */
    public function getDefaultOAuthVersion()
    {
        return $this->defaultOAuthVersion;
    }
    
    /**
     * Sends a GET request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return OmnisalesResponse
     *
     * @throws OmnisalesSDKException
     */
    public function get($endpoint,array $params = [], $accessToken = null, $eTag = null)
    {   
        return $this->sendRequest(
            'GET',
            $endpoint,
            $params,
            $accessToken,
            $eTag
        );
    }
    /**
     * Sends a POST request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return OmnisalesResponse
     *
     * @throws OmnisalesSDKException
     */
    public function post($endpoint, $params = [], $accessToken = null, $eTag = null)
    {
        return $this->sendRequest(
            'POST',
            $endpoint,
            $params,
            $accessToken,
            $eTag
        );
    }
    /**
     * Sends a POST request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return OmnisalesResponse
     *
     * @throws OmnisalesSDKException
     */
    public function uploadVideo($endpoint, array $params = [], $accessToken = null, $eTag = null)
    {
        return $this->sendRequestUploadVideo(
            'POST',
            $endpoint,
            $params,
            $accessToken,
            $eTag
        );
    }
    /**
     * Sends a DELETE request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return OmnisalesResponse
     *
     * @throws OmnisalesSDKException
     */
    public function delete($endpoint, array $params = [], $accessToken = null, $eTag = null)
    {
        return $this->sendRequest(
            'DELETE',
            $endpoint,
            $params,
            $accessToken,
            $eTag
        );
    }
    /**
     * Sends a request to Graph and returns the result.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return OmnisalesResponse
     *
     * @throws OmnisalesSDKException
     */
    public function sendRequest($method, $endpoint, array $params = [], $accessToken = null, $eTag = null)
    {
        $request = $this->request($method, $endpoint, $params, $accessToken, $eTag);
        // var_dump($this->client);
        // var_dump($request);
        return $this->lastResponse = $this->client->sendRequest($request);
    }
    /**
     * Sends a request upload video to OA and returns the result.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return OmnisalesResponse
     *
     * @throws OmnisalesSDKException
     */
    public function sendRequestUploadVideo($method, $endpoint, array $params = [], $accessToken = null, $eTag = null)
    {
        $request = $this->request($method, $endpoint, $params, $accessToken, $eTag);
        return $this->lastResponse = $this->client->sendRequestUploadVideo($request);
    }
    /**
     * Instantiates a new OmnisalesRequest entity.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return OmnisalesRequest
     *
     * @throws OmnisalesSDKException
     */
    public function request($method, $endpoint, array $params = [], $accessToken = null, $eTag = null)
    {
        $request =  new OmnisalesRequest(
            $this->app,
            $this->oaInfo,
            $accessToken,
            $method,
            $endpoint,
            $params,
            $eTag
        );
        return $request;
    }
    
    public function getRedirectLoginHelper()
    {
        return new OmnisalesRedirectLoginHelper(
            $this->getOAuth2Client(),
            $this->urlDetectionHandler
        );
    }
}
