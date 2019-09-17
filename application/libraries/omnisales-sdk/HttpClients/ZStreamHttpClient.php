<?php
/**
 * Omnisales © 2018
 *
 */

namespace Omnisales\HttpClients;

use Omnisales\Http\GraphRawResponse;
use Omnisales\Exceptions\OmnisalesSDKException;
use Omnisales\HttpClients\OmnisalesHttpClientInterface;

/**
 * Class OmnisalesStreamHttpClient
 *
 * @package Omnisales
 */
class OmnisalesStreamHttpClient implements OmnisalesHttpClientInterface
{
    /**
     * @var OmnisalesStream Procedural stream wrapper as object.
     */
    protected $omnisalesStream;

    /**
     * @param OmnisalesStream|null Procedural stream wrapper as object.
     */
    public function __construct(OmnisalesStream $omnisalesStream = null)
    {
        $this->omnisalesStream = $omnisalesStream ?: new OmnisalesStream();
    }

    /**
     * @inheritdoc
     */
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => $this->compileHeader($headers),
                'content' => $body,
                'timeout' => $timeOut,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => true, // All root certificates are self-signed
                'cafile' => __DIR__ . '/certs/DigiCertHighAssuranceEVRootCA.pem',
            ],
        ];

        $this->omnisalesStream->streamContextCreate($options);
        $rawBody = $this->omnisalesStream->fileGetContents($url);
        $rawHeaders = $this->omnisalesStream->getResponseHeaders();

        if ($rawBody === false || empty($rawHeaders)) {
            throw new OmnisalesSDKException('Stream returned an empty response', 660);
        }

        $rawHeaders = implode("\r\n", $rawHeaders);

        return new GraphRawResponse($rawHeaders, $rawBody);
    }

    /**
     * Formats the headers for use in the stream wrapper.
     *
     * @param array $headers The request headers.
     *
     * @return string
     */
    public function compileHeader(array $headers)
    {
        $header = [];
        foreach ($headers as $k => $v) {
            $header[] = $k . ': ' . $v;
        }

        return implode("\r\n", $header);
    }
}
