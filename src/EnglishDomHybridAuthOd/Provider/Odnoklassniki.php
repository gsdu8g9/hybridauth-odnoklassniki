<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
* (c) 2015 Hybridauth authors | https://hybridauth.github.io/license.html
*/
namespace EnglishDomHybridAuthOd\Provider;

use Hybridauth\Adapter\Template\OAuth2\Tokens;
use Hybridauth\Exception;
use Hybridauth\Http\Request;
use Hybridauth\Adapter\Template\OAuth2\OAuth2Template;
use EnglishDomHybridAuthOd\Entity\Odnoklassniki\Profile;
use EnglishDomHybridAuthOd\Entity\Odnoklassniki\Page;
use EnglishDomHybridAuthOd\Entity\Odnoklassniki\Event;

/**
 *
 */
class Odnoklassniki extends OAuth2Template
{
    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'http://api.odnoklassniki.ru/fb.do';
    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://www.odnoklassniki.ru/oauth/authorize';
    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.odnoklassniki.ru/oauth/token.do';

    public function getOdnoklassnikiAccessToken()
    {
        /* @var $tokens Tokens */
        $tokens = $this->getTokens();
        return $tokens->accessToken;
    }

    /**
     * Internal: Initialize Odnoklassniki adapter. This method isn't intended for public consumption.
     *
     * Basically on initializers we feed defaults values to \OAuth2\Template::initialize()
     *
     * let*() methods are similar to set, but 'let' will not overwrite the value if its already set
     */
    public function initialize()
    {
        parent::initialize();

        $this->letApplicationId($this->getAdapterConfig('keys', 'id'));
        $this->setApplicationKey($this->getAdapterConfig('keys', 'key'));
        $this->letApplicationSecret($this->getAdapterConfig('keys', 'secret'));

        // @ todo create a way to track scope & request addtl scope as needed
        $scope = $this->getAdapterConfig('scope')
            ? $this->getAdapterConfig('scope')
            : '';

        $this->letApplicationScope($scope);

        $this->letEndpointRedirectUri($this->getHybridauthEndpointUri());
        $this->letEndpointBaseUri($this->apiBaseUrl);
        $this->letEndpointAuthorizeUri($this->authorizeUrl);
        $this->letEndpointRequestTokenUri($this->accessTokenUrl);

//        $this->letEndpointAuthorizeUriAdditionalParameters(array('display' => 'page'));
    }

    // --------------------------------------------------------------------

    /**
     * Returns user profile
     *
     * Examples:
     *
     *	$data = $hybridauth->authenticate("Odnoklassniki")->getUserProfile();
     */
    public function getUserProfile($user = null)
    {
        $fields = [
            'uid', 'locale', 'first_name', 'last_name', 'name', 'gender', 'age', 'birthday',
            'has_email', 'current_status', 'current_status_id', 'current_status_date','online',
            'photo_id', 'pic_1', 'pic_2', 'pic1024x768', 'location', 'email'
        ];
        $appKey = $this->getApplicationKey();
        $secret = $this->getApplicationSecret();
        $token = $this->getOdnoklassnikiAccessToken();
        $sig = md5(
            'application_key=' . $appKey .
            'fields=' . implode(',', $fields) .
            'method=users.getCurrentUser' .
            md5($token . $secret)
        );
        $parameters = [
            'application_key' => $appKey,
            'method'          => 'users.getCurrentUser',
            'fields'          => implode(',', $fields),
            'sig'             => $sig,
        ];

        // request user infos
        $response = $this->signedRequest(
            '',
            Request::GET,
            $parameters
        );
        $response = json_decode($response);

        if (!isset($response->uid) || isset($response->error)) {
            throw new
            Exception(
                'User profile request failed: Provider returned an invalid response. ' .
                'HTTP client state:(' . $this->httpClient->getState() . ')',
                Exception::USER_PROFILE_REQUEST_FAILED,
                $this
            );
        }

        return Profile::generateFromResponse($response, $this);
    }

    // --------------------------------------------------------------------

    /**
     * Returns user contacts list
     *
     * Examples:
     *
     *	$data = $hybridauth->authenticate("Odnoklassniki")->getUserContacts();
     */
    public function getUserContacts()
    {
        return [];
    }

    // --------------------------------------------------------------------

    /**
     * Returns user profile
     *
     * Examples:
     *
     *	$data = $hybridauth->authenticate("Odnoklassniki")->getUserPages();
     */
    public function getUserPages($user = null)
    {
        return [];
    }

    public function getPage($page_id)
    {
        // request user infos
        $response = $this->signedRequest($page_id);
        $response = json_decode($response);

        if (! isset($response->id) || isset($response->error)) {
            throw new
            Exception(
                'User page listing request failed: Provider returned an invalid response. ' .
                'HTTP client state:(' . $this->httpClient->getState() . ')',
                Exception::USER_PROFILE_REQUEST_FAILED,
                $this
            );
        }

        return Page::generateFromResponse($response, $this);
    }

    public function getEvent($eventIdentifier)
    {
        $response = $this->signedRequest($eventIdentifier);
        $response = json_decode($response);

        if (! isset($response->id) || isset($response->error)) {
            throw new
            Exception(
                'Event listing request failed: Provider returned an invalid response. ' .
                'HTTP client state:(' . $this->httpClient->getState() . ')',
                Exception::USER_PROFILE_REQUEST_FAILED,
                $this
            );
        }
        return Event::generateFromResponse($response, $this);
    }

    // --------------------------------------------------------------------

    /**
     * Updates user status
     *
     * Examples:
     *
     *	$data = $hybridauth->authenticate("Odnoklassniki")->setUserStatus(_STATUS_);
     *
     *	$data = $hybridauth->authenticate("Odnoklassniki")->setUserStatus(_PARAMS_);
     */
    public function setUserStatus($status)
    {
        throw new Exception("Unsupported", Exception::UNSUPPORTED_FEATURE, null, $this);
    }
}