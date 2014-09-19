<?php

namespace XiangMinWang\WechatEnterpriseClient;

class Client
{
    protected static $baseUrl = 'https://qyapi.weixin.qq.com/cgi-bin/';

    protected $guzzleClient;

    public $accessToken;

    public function __construct($accessToken, $agentId = 0)
    {
        $this->guzzleClient = new \GuzzleHttp\Client(array('base_url' => static::$baseUrl));
        $this->accessToken = $accessToken;
        $this->agentId = $agentId;
    }

    /**
     * Set a custom Guzzle client for http requests
     * @param GuzzleHttpClient $client Custom Guzzle client
     */
    public function setGuzzleClient(\GuzzleHttp\Client $client)
    {
        $this->guzzleClient = $client;
        return $this;
    }

    /**
     * Fetch all departments of my enterprise.
     * @return Array
     */
    public function getDepartments()
    {
        $api = sprintf('department/list?access_token=%s', $this->accessToken);

        return $this->output($this->guzzleClient->get($api), true);
    }

    /**
     * Fetch all departments of my enterprise.
     * @return Array
     */
    public function getEmployees($departmentId, $fetchChild = 0, $status = 0)
    {
        $api = sprintf(
            'user/simplelist?access_token=%s&department_id=%d&fetch_child=%d&status=%d',
            $this->accessToken, $departmentId, $fetchChild, $status
        );

        return $this->output($this->guzzleClient->get($api), true);
    }

    public function getEmployeeDetail($userId)
    {
        $api = sprintf(
            'user/get?access_token=%s&userid=%s',
            $this->accessToken, $userId
        );

        return $this->output($this->guzzleClient->get($api), true);
    }

    /**
     * Fetch 'userid' through OAuth2 auth code.
     * @param $code
     * @return Array|Guzzle\Http\Message\Response
     */
    public function getUserId($code)
    {
        $api = sprintf(
            'user/getuserinfo?access_token=%s&code=%s&agentid=%d',
            $this->accessToken, $code, $this->agentId
        );

        return $this->output($this->guzzleClient->get($api), true);
    }

    /**
     * Output
     *
     * @param  Guzzle\Http\Message\Response $response Guzzle response object
     * @param  boolean $body
     * @return Array|Guzzle\Http\Message\Response Guzzle response body|object
     */
    protected function output($response, $body)
    {
        return $body ? $response->json() : $response;
    }
}