<?php

namespace Invoiced\OAuth1\Client\Server;

use Exception;
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
     * {@inheritdoc}
     */
    public function __construct($clientCredentials, SignatureInterface $signature = null)
    {
        parent::__construct($clientCredentials, $signature);

        if (is_array($clientCredentials)) {
            $this->parseConfiguration($clientCredentials);
        }
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
        );
        foreach ($configToPropertyMap as $config => $property) {
            if (isset($configuration[$config])) {
                $this->$property = $configuration[$config];
            }
        }
    }
}
