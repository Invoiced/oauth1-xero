<?php

namespace Invoiced\OAuth1\Client\Server;

use Exception;
use InvalidArgumentException;
use League\OAuth1\Client\Server\Server;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\BadResponseException;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Signature\SignatureInterface;
use League\OAuth1\Client\Credentials\ClientCredentials;

class Xero extends Server
{
    /**
     * @var string
     */
    protected $responseType = 'xml';

    /**
     * @var array
     */
    protected $httpClientOptions = [];

    /**
     * @var array
     */
    protected $lastTokenCredentialsResponse;

    /**
     * @var array
     */
    protected $scope = [];

    /**
     * @var bool
     */
    protected $redirectOnError = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($clientCredentials, SignatureInterface $signature = null)
    {
        if (is_array($clientCredentials)) {
            $this->parseConfiguration($clientCredentials);

            $clientCredentials = $this->createClientCredentials($clientCredentials);

            if (!$signature && $clientCredentials instanceof RsaClientCredentials) {
                $signature = new RsaSha1Signature($clientCredentials);
            }
        }

        parent::__construct($clientCredentials, $signature);
    }

    /**
     * Sets the value of the scope parameter used during authorization.
     *
     * @param array $scope Enumerated array where each element is a string
     *                     containing a single privilege value (e.g. 'payroll.employees')
     */
    public function setScope(array $scope)
    {
        $this->scope = $scope;
    }

    /**
     * Sets the redirect on error parameter used during authorization.
     *
     * @param boolean $redirect Boolean to toggle this parameter.
     * 
     * @return void
     */
    public function setRedirectOnError($redirect)
    {
        $this->redirectOnError = $redirect;
    }

    /**
     * Gets the current setting for redirect on error.
     *
     * @return boolean
     */
    public function getRedirectOnError()
    {
        return $this->redirectOnError;
    }

    /**
     * Creates a Guzzle HTTP client for the given URL.
     *
     * @return GuzzleHttpClient
     */
    public function createHttpClient()
    {
        return new GuzzleHttpClient($this->httpClientOptions);
    }

    public function urlTemporaryCredentials()
    {
        return 'https://api.xero.com/oauth/RequestToken';
    }

    public function urlAuthorization()
    {
        return 'https://api.xero.com/oauth/Authorize'
            . $this->buildUrlAuthorizationQueryString();
    }

    /**
     * @return string
     */
    protected function buildUrlAuthorizationQueryString()
    {
        if (!$this->scope && !$this->redirectOnError) {
            return '';
        }

        if ($this->scope) {
            $parameters[] = 'scope=' . implode(',', $this->scope);
        }

        if ($this->redirectOnError) {
            $parameters[] = 'redirectOnError=true';
        }

        return '?' . implode('&', $parameters);
    }

    public function urlTokenCredentials()
    {
        return 'https://api.xero.com/oauth/AccessToken';
    }

    public function urlUserDetails()
    {
        return $this->notSupportedByXero();
    }

    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        return $this->notSupportedByXero();
    }

    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $this->notSupportedByXero();
    }

    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return $this->notSupportedByXero();
    }

    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return $this->notSupportedByXero();
    }

    /**
     * Gets the response of the last access token call. This might
     * be useful for partner applications to retrieve additional
     * OAuth parameters passed in by Xero.
     *
     * @return array|null
     */
    public function getLastTokenCredentialsResponse()
    {
        return $this->lastTokenCredentialsResponse;
    }

    /**
     * Refreshes an access token. Can be used by partner applications.
     *
     * @param TokenCredentials $tokenCredentials
     * @param string           $sessionHandle    Xero session handle
     *
     * @throws \League\OAuth1\Client\Credentials\CredentialsException when the access token cannot be refreshed
     *
     * @return TokenCredentials
     */
    public function refreshToken(TokenCredentials $tokenCredentials, $sessionHandle)
    {
        $client = $this->createHttpClient();
        $url = $this->urlTokenCredentials();

        $parameters = [
            'oauth_session_handle' => $sessionHandle,
        ];

        $headers = $this->getHeaders($tokenCredentials, 'POST', $url, $parameters);

        try {
            $response = $client->post($url, [
                'headers' => $headers,
                'form_params' => $parameters,
            ]);
        } catch (BadResponseException $e) {
            $this->handleTokenCredentialsBadResponse($e);
        }

        return $this->createTokenCredentials((string) $response->getBody());
    }

    protected function notSupportedByXero()
    {
        throw new Exception("Xero's API does not support retrieving the current user. Please see https://xero.uservoice.com/forums/5528-xero-accounting-api/suggestions/5688571-expose-which-user-connected-the-organization-via-o");
    }

    /**
     * Parse configuration array to set attributes.
     *
     * @param array $configuration
     */
    private function parseConfiguration(array $configuration = [])
    {
        $configToPropertyMap = [
            'http_client' => 'httpClientOptions',
        ];
        foreach ($configToPropertyMap as $config => $property) {
            if (isset($configuration[$config])) {
                $this->$property = $configuration[$config];
            }
        }
    }

    /**
     * Creates a client credentials instance from an array of credentials.
     *
     * @param array $clientCredentials
     *
     * @return ClientCredentials
     */
    protected function createClientCredentials(array $clientCredentials)
    {
        $keys = ['identifier', 'secret'];

        foreach ($keys as $key) {
            if (!isset($clientCredentials[$key])) {
                throw new InvalidArgumentException("Missing client credentials key [$key] from options.");
            }
        }

        if (isset($clientCredentials['rsa_private_key']) && isset($clientCredentials['rsa_public_key'])) {
            $_clientCredentials = new RsaClientCredentials();
            $_clientCredentials->setRsaPrivateKey($clientCredentials['rsa_private_key']);
            $_clientCredentials->setRsaPublicKey($clientCredentials['rsa_public_key']);
        } else {
            $_clientCredentials = new ClientCredentials();
        }

        $_clientCredentials->setIdentifier($clientCredentials['identifier']);
        $_clientCredentials->setSecret($clientCredentials['secret']);

        if (isset($clientCredentials['callback_uri'])) {
            $_clientCredentials->setCallbackUri($clientCredentials['callback_uri']);
        }

        return $_clientCredentials;
    }

    /**
     * Creates token credentials from the body response.
     *
     * @param string $body
     *
     * @return TokenCredentials
     */
    protected function createTokenCredentials($body)
    {
        parse_str($body, $data);
        $this->lastTokenCredentialsResponse = $data;

        return parent::createTokenCredentials($body);
    }
}
