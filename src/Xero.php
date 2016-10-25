<?php

namespace Invoiced\OAuth1\Client\Server;

use Exception;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Server\User;

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
        return 'https://api.xero.com/api.xro/2.0/organisation';
    }

    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
		$data = json_decode(json_encode($data),TRUE)['Organisations']['Organisation'];

        if (!isset($data) || !is_array($data)) return;

        $user = new User();
        
        $user->uid = $data['APIKey'];
        $user->nickname = $data['Name'];
        $user->name = $data['LegalName'];
        $user->location = $data['CountryCode'];
        $user->description = $data['LineOfBusiness'] . ' ' . $data['OrganisationEntityType'];
        $user->imageUrl = null;
        $user->email = null;

        $used = array('APIKey', 'Name', 'LegalName', 'CountryCode', 'LineOfBusiness', 'OrganisationEntityType');

        $user->extra = array_diff_key($data, array_flip($used));
        return $user;
    }

    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        $data = json_decode(json_encode($data),TRUE)['Organisations']['Organisation'];
        return $data['APIKey'];

    }

    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return $this->notSupportedByXero();
    }

    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        $data = json_decode(json_encode($data),TRUE)['Organisations']['Organisation'];
        return $data['Name'];
    }

    protected function notSupportedByXero()
    {
        throw new Exception("Xero's API does not support retrieving the current user. Please see https://xero.uservoice.com/forums/5528-xero-accounting-api/suggestions/5688571-expose-which-user-connected-the-organization-via-o");
    }
}
