<?php

/*
 * A class of random useful functions that might one day have a better home.
 */

class SiteSpecific
{
    /**
     * Fetching the client for interfacing with Amazon Cognito.
     * @staticvar type $client
     * @return \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient
     */
    public static function getCognitoIdentityProviderClient() : \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient
    {
        static $client = null;

        if ($client === null)
        {
            $client = new \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient([
                'version' => 'latest',
                'region' => AWS_REGION,
                'credentials' => [
                    'key'    => AWS_KEY_ID,
                    'secret' => AWS_KEY_SECRET,
                ],
            ]);
        }

        return $client;
    }


    /**
     * Retrieve the client for AWS SSO OIDC
     * @return \Aws\SSOOIDC\SSOOIDCClient
     */
    public static function getCognitoSsoOidcClient() : \Aws\SSOOIDC\SSOOIDCClient
    {
        static $ssoClient = null;

        if ($ssoClient === null)
        {
            $ssoClient = new \Aws\SSOOIDC\SSOOIDCClient([
                'version' => 'latest',
                'region' => AWS_REGION,
                'credentials' => [
                    'key'    => AWS_KEY_ID,
                    'secret' => AWS_KEY_SECRET,
                ],
            ]);
        }

        return $ssoClient;
    }


    /**
     * Retrieve the logged in user.
     * @return AuthUser
     * @throws Exception
     */
    public static function getLoggedInUser() : AuthUser
    {
        if (SiteSpecific::isLoggedIn() === false)
        {
            throw new ExceptionUserNotLoggedIn("User is not logged in.");
        }

        return $_SESSION['user'];
    }


    public static function setLoggedInUser(AuthUser $user)
    {
        $_SESSION['user'] = $user;
    }


    public static function removeLoggedInUser()
    {
        unset($_SESSION['user']);
    }


    public static function isLoggedIn() : bool
    {
        return (isset($_SESSION['user']));
    }
}