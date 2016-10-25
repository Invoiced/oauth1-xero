<?php

use Invoiced\OAuth1\Client\Server\Xero;

class XeroTest extends PHPUnit_Framework_TestCase
{
    public function testUrlTemporaryCredentials()
    {
        $server = $this->getServer();

        $this->assertEquals('https://api.xero.com/oauth/RequestToken', $server->urlTemporaryCredentials());
    }

    public function testUrlAuthorization()
    {
        $server = $this->getServer();

        $this->assertEquals('https://api.xero.com/oauth/Authorize', $server->urlAuthorization());
    }

    public function testUrlTokenCredentials()
    {
        $server = $this->getServer();

        $this->assertEquals('https://api.xero.com/oauth/AccessToken', $server->urlTokenCredentials());
    }

    public function testUserDetails()
    {
        $this->setExpectedException('Exception');

        $server = $this->getServer();
        $credentials = $this->getCredentials();
        $data = ''; // xero does not support this yet

        $server->userDetails($data, $credentials);
    }

    public function testUserUid()
    {
        $this->setExpectedException('Exception');

        $server = $this->getServer();
        $credentials = $this->getCredentials();
        $data = ''; // xero does not support this yet

        $server->userUid($data, $credentials);
    }

    public function testUserEmail()
    {
        $this->setExpectedException('Exception');

        $server = $this->getServer();
        $credentials = $this->getCredentials();
        $data = ''; // xero does not support this yet

        $server->userEmail($data, $credentials);
    }

    public function testUserScreenName()
    {
        $this->setExpectedException('Exception');

        $server = $this->getServer();
        $credentials = $this->getCredentials();
        $data = ''; // xero does not support this yet

        $server->userScreenName($data, $credentials);
    }

    public function testGettingUserDetails()
    {
        $this->setExpectedException('Exception');

        $server = $this->getServer();
        $credentials = $this->getCredentials();

        $user = $server->getUserDetails($credentials);
    }

    protected function getServer()
    {
        $server = Mockery::mock(
            'Invoiced\OAuth1\Client\Server\Xero[createHttpClient]',
            [$this->getClientCredentials()]
        );

        $client = Mockery::mock('stdClass');
        $server->shouldReceive('createHttpClient')
               ->andReturn($client);

        return $server;
    }

    protected function getCredentials()
    {
        $credentials = Mockery::mock('League\OAuth1\Client\Credentials\TokenCredentials');
        $credentials->shouldReceive('getIdentifier')
                    ->andReturn('tokencredentialsidentifier');
        $credentials->shouldReceive('getSecret')
                    ->andReturn('tokencredentialssecret');

        return $credentials;
    }

    protected function getClientCredentials()
    {
        return [
            'identifier' => 'app_key',
            'secret' => 'secret',
            'callback_uri' => 'https://example.com/callback',
        ];
    }
}