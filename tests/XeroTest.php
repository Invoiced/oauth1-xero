<?php

use Invoiced\OAuth1\Client\Server\RsaClientCredentials;
use Invoiced\OAuth1\Client\Server\RsaSha1Signature;
use Invoiced\OAuth1\Client\Server\Xero;

class XeroTest extends PHPUnit_Framework_TestCase
{
    public function testUsePartnerApi()
    {
        $server = $this->getServer();

        $this->assertFalse($server->getUsePartnerApi());
        $this->assertEquals($server, $server->usePartnerApi());
        $this->assertTrue($server->getUsePartnerApi());
    }

    public function testCreateHttpClient()
    {
        $server = new Xero($this->getClientCredentials());

        $client = $server->createHttpClient();
        $this->assertInstanceOf('GuzzleHttp\Client', $client);
        $this->assertEquals('/path/cert.pem', $client->getConfig('cert'));
        $this->assertEquals('/path/key.pem', $client->getConfig('ssl_key'));
        $this->assertFalse($client->getConfig('verify'));
    }

    public function testCredentialsWithRsa()
    {
        $config = [
            'identifier' => 'app_key',
            'secret' => 'secret',
            'callback_uri' => 'https://example.com/callback',
            'rsa_public_key' => __DIR__.'/test_rsa_publickey.pem',
            'rsa_private_key' => __DIR__.'/test_rsa_privatekey.pem',
        ];
        $server = new Xero($config);

        $credentials = $server->getClientCredentials();
        $this->assertInstanceOf(RsaClientCredentials::class, $credentials);
        $this->assertTrue(is_resource($credentials->getRsaPrivateKey()));
        $this->assertTrue(is_resource($credentials->getRsaPublicKey()));

        $signature = $server->getSignature();
        $this->assertInstanceOf(RsaSha1Signature::class, $signature);
    }

    public function testUrlTemporaryCredentials()
    {
        $server = $this->getServer();

        $this->assertEquals('https://api.xero.com/oauth/RequestToken', $server->urlTemporaryCredentials());

        $server->usePartnerApi();
        $this->assertEquals('https://api-partner.network.xero.com/oauth/RequestToken', $server->urlTemporaryCredentials());
    }

    public function testUrlAuthorization()
    {
        $server = $this->getServer();

        $this->assertEquals('https://api.xero.com/oauth/Authorize', $server->urlAuthorization());

        $server->usePartnerApi();
        $this->assertEquals('https://api.xero.com/oauth/Authorize', $server->urlAuthorization());
    }

    public function testUrlTokenCredentials()
    {
        $server = $this->getServer();

        $this->assertEquals('https://api.xero.com/oauth/AccessToken', $server->urlTokenCredentials());

        $server->usePartnerApi();
        $this->assertEquals('https://api-partner.network.xero.com/oauth/AccessToken', $server->urlTokenCredentials());
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
            Xero::class.'[createHttpClient]',
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
            'http_client' => [
                'cert' => '/path/cert.pem',
                'ssl_key' => '/path/key.pem',
                'verify' => false,
            ],
        ];
    }
}
