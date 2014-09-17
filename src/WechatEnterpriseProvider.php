<?php

namespace XiangMinWang\WechatEnterpriseClient;

use Guzzle\Service\Client as GuzzleClient;
use \XiangMinWang\WechatEnterpriseClient\AccessToken as AccessToken;
use \XiangMinWang\WechatEnterpriseClient\IDPException as IDPException;

class WechatEnterpriseProvider
{
    public $clientId = '';

    public $clientSecret = '';

    public $method = 'post';

    public $responseType = 'json';

    protected $httpClient;

    public function __construct($options = array())
    {
        foreach ($options as $option => $value) {
            if (isset($this->{$option})) {
                $this->{$option} = $value;
            }
        }

        $this->setHttpClient(new GuzzleClient);
    }

    public function setHttpClient(GuzzleClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    public function getHttpClient()
    {
        $client = clone $this->httpClient;

        return $client;
    }

    public function urlAccessToken()
    {
        return 'https://qyapi.weixin.qq.com/cgi-bin/gettoken';
    }

    public function urlUserDetails(AccessToken $token) {}

    public function userDetails($response, AccessToken $token)
    {
        $response = $this->fetchUserDetails($token);

        return $this->userDetails(json_decode($response), $token);
    }

    public function userUid($response, AccessToken $token)
    {
    }

    public function getAccessToken($params = array())
    {
        $defaultParams = array(
            'corpid'     => $this->clientId,
            'corpsecret' => $this->clientSecret
        );

        $requestParams = array_merge($defaultParams, $params);

        try {
            switch (strtoupper($this->method)) {
                case 'GET':
                    // @codeCoverageIgnoreStart
                    // No providers included with this library use get but 3rd parties may
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken() . '?' . $this->httpBuildQuery($requestParams, '', '&'));
                    $request = $client->send();
                    $response = $request->getBody();
                    break;
                    // @codeCoverageIgnoreEnd
                case 'POST':
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken());
                    $request = $client->post(null, null, $requestParams)->send();
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreStart
                default:
                    throw new \InvalidArgumentException('Neither GET nor POST is specified for request');
                // @codeCoverageIgnoreEnd
            }
        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $raw_response = explode("\n", $e->getResponse());
            $response = end($raw_response);
            // @codeCoverageIgnoreEnd
        }

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);
                break;
            case 'string':
                parse_str($response, $result);
                break;
        }

        if (isset($result['error']) && ! empty($result['error'])) {
            // @codeCoverageIgnoreStart
            throw new IDPException($result);
            // @codeCoverageIgnoreEnd
        }

        return new AccessToken($result);
    }

    protected function fetchUserDetails(AccessToken $token)
    {
        $url = $this->urlUserDetails($token);

        try {

            $client = $this->getHttpClient();
            $client->setBaseUrl($url);

            if ($this->headers) {
                $client->setDefaultOption('headers', $this->headers);
            }

            $request = $client->get()->send();
            $response = $request->getBody();

        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $raw_response = explode("\n", $e->getResponse());
            throw new IDPException(end($raw_response));
            // @codeCoverageIgnoreEnd
        }

        return $response;
    }

}
