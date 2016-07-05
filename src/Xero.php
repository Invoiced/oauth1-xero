<?php

namespace Invoiced\OAuth1\Client\Server;

use Exception;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;

class Xero extends Server
{
    protected $responseType = 'xml';

    public function urlTemporaryCredentials()
    {
        return 'https://api.xero.com/oauth/RequestToken';
    }

    public function urlAuthorization()
    {
        return 'https://api.xero.com/oauth/Authorize';
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

    protected function notSupportedByXero()
    {
        throw new Exception("Xero's API does not support retrieving the current user. Please see https://xero.uservoice.com/forums/5528-xero-accounting-api/suggestions/5688571-expose-which-user-connected-the-organization-via-o");
    }
}
