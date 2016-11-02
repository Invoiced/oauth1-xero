<?php

namespace Invoiced\OAuth1\Client\Server;

use Exception;
use GuzzleHttp\Client as GuzzleHttpClient;
use InvalidArgumentException;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Signature\SignatureInterface;

class Xero extends Server
{
    /**
     * @var string
     */
    protected $responseType = 'xml';

    /**
     * @var bool
     */
    protected $usePartnerApi = false;

    /**
     * @var array
     */
    protected $httpClientOptions = [];

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
     * Sets whether the Xero partner API should be used.
     *
     * @param bool $enable
     *
     * @return self
     */
    public function usePartnerApi($enable = true)
    {
        $this->usePartnerApi = $enable;

        return $this;
    }

    /**
     * Checks if the Xero partner API is used.
     *
     * @return bool
     */
    public function getUsePartnerApi()
    {
        return $this->usePartnerApi;
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
        if ($this->usePartnerApi) {
            return 'https://api-partner.network.xero.com/oauth/RequestToken';
        }

        return 'https://api.xero.com/oauth/RequestToken';
    }

    public function urlAuthorization()
    {
        return 'https://api.xero.com/oauth/Authorize';
    }

    public function urlTokenCredentials()
    {
        if ($this->usePartnerApi) {
            return 'https://api-partner.network.xero.com/oauth/AccessToken';
        }

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

    protected function notSupportedByXero()
    {
        throw new Exception("Xero's API does not support retrieving the current user. Please see https://xero.uservoice.com/forums/5528-xero-accounting-api/suggestions/5688571-expose-which-user-connected-the-organization-via-o");
    }

    /**
     * Parse configuration array to set attributes.
     *
     * @param array $configuration
     */
    private function parseConfiguration(array $configuration = array())
    {
        $configToPropertyMap = array(
            'partner' => 'usePartnerApi',
            'http_client' => 'httpClientOptions',
        );
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
        $keys = array('identifier', 'secret');

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
}
