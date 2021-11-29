<?php

/*
 * A controller to define and handle all of the logging in and logging out endpoints.
 */


class AuthController extends AbstractSlimController
{
    public static function registerRoutes($app)
    {
        // Handle the user requesting to login
        $app->get('/login', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, $args) {
            $controller = new AuthController($request, $response, $args);
            return $controller->handleUserLoginRequest();
        });


        // handle the response back from Cognito, which should hopefully have the user's JWT token
        $app->get('/login-response', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, $args) {
            $controller = new AuthController($request, $response, $args);
            return $controller->handleCognitoLoginResponse();
        });


        // handle a user requesting to log out of everything
        $app->get('/logout-everything', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, $args) {
            $controller = new AuthController($request, $response, $args);
            return $controller->handleUserLogoutOfEverythingRequest();
        })->add(new MiddlewareCheckLoggedIn());


        // handle a user requesting to log out of just this service
        $app->get('/logout', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, $args) {
            $controller = new AuthController($request, $response, $args);
            return $controller->handleUserLogoutRequest();
        })->add(new MiddlewareCheckLoggedIn());


        // handle the response back from Cognito that the user logged out.
        $app->get('/logout-response', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, $args) {
            $controller = new AuthController($request, $response, $args);
            return $controller->handleCognitoLogoutResponse();
        })->add(new MiddlewareCheckLoggedIn());
    }


    private function handleCognitoLogoutResponse() : \Psr\Http\Message\ResponseInterface
    {
        die("You are now successfully logged out. Do you wish to <a href='/login'>log back in</a>?");
    }


    /**
     * Handle a user requesting to log in.
     * Send them over to the SSO to log in.
     * @return Slim\Psr7\Response
     */
    private function handleUserLoginRequest() : Slim\Psr7\Response
    {
        $parameters = [
            'client_id' => COGNITO_CLIENT_ID,
            'response_type' => 'code',
            'scope' => 'openid',
            'redirect_uri' => $this->getLoginRedirectUrl(),
        ];

        $url = COGNITO_HOSTED_UI_URL . "/login?" . http_build_query($parameters);
        return SlimLib::createRedirectResponse($url);
    }


    /**
     * Get the endpoint of this system that will handle a response from the login endpoint on the SSO.
     * This endpoint should expect a code from the SSO, which it will then send a request to the SSO directly
     * in order to exchange it for a the user's auth tokens.
     * @return string - the URI
     */
    private function getLoginRedirectUrl() : string
    {
        return \Programster\CoreLibs\Core::getHostname() . "/login-response";
    }


    private function handleCognitoLoginResponse()
    {
        try
        {
            // Using Authorization code grant, so expecting a "code" that we exchange for an auth token on the backend
            // this way the user never gets exposed to their actual auth token:
            // https://docs.aws.amazon.com/cognito/latest/developerguide/cognito-user-pools-app-idp-settings.html
            if (!isset($_GET["code"]))
            {
                throw new Exception("Missing required 'code' from SSO.");
            }

            $code = $_GET["code"];
            $getTokenResponse = $this->sendTokenRequest($code);

            if ($getTokenResponse->getStatusCode() !== 200)
            {
                throw new Exception("Failed to retrieve user auth token.");
            }

            $getTokenResponseJsonBody = $getTokenResponse->getBody()->getContents();
            $getTokenResponseArray = json_decode($getTokenResponseJsonBody, true);

            // read about token types here: https://blog.programster.org/oidc-token-types
            $idToken = $getTokenResponseArray['id_token']; // JWT with identity information about the user inside:
            $accessToken = $getTokenResponseArray['access_token']; // can be used to get user info
            $refreshToken = $getTokenResponseArray['refresh_token']; // can be used to obtain new access tokens:
            $expiryTimestamp = time() + $getTokenResponseArray['expires_in'];
            $tokenType = $getTokenResponseArray['token_type']; // Bearer

            $this->verifyIdToken($idToken);

            // Fetch the user information from Cognito, using the token they gave us
            $getUserInfoResponse = $this->sendGetUserRequest($accessToken);


            if ($getUserInfoResponse->getStatusCode() !== 200)
            {
                throw new Exception("Failed to retrieve user information.");
            }

            $getUserInfoResponseJsonBody = $getUserInfoResponse->getBody()->getContents();
            $getUserInfoResponseArray = json_decode($getUserInfoResponseJsonBody, true);


            // Retrieve the information from the response.
            // Some of these keys will be specific to my setup using AWS Cognito
            $subject = $getUserInfoResponseArray['sub']; // UUID
            $emailVerified = $getUserInfoResponseArray['email_verified'];
            $name = $getUserInfoResponseArray['name'];
            $email = $getUserInfoResponseArray['email'];
            $username = $getUserInfoResponseArray['username']; // same as $subject UUID

            // create the auth user, and set them as the logged in user for the session.
            $authUser = new AuthUser($idToken, $accessToken, $refreshToken, $email);
            SiteSpecific::setLoggedInUser($authUser);

            // Redirect the user to the dashboard now that they are "logged in".
            $response = SlimLib::createRedirectResponse("/dashboard");
        }
        catch (\Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException $e)
        {
            $responseView = new ViewError(
                "Access Token Error",
                "Failed to vlaidate the access token: {$e->getMessage()}"
            );

            $response = SlimLib::createHtmlResponse($this->m_response, $responseView);
        }
        catch (\Aws\Exception\CredentialsException $e)
        {
            $responseView = new ViewError(
                "Server Credentials Error",
                "There was an issue with the server credentials for AWS. Please contact an administrator."
            );

            $response = SlimLib::createHtmlResponse($this->m_response, $responseView);
        }
        catch (ExceptionInvalidToken)
        {
            $responseView = new ViewError(
                "Invalid Token",
                "The ID token recieved from the SSO was invalid. Please contact the website administrator."
            );

            $response = SlimLib::createHtmlResponse($this->m_response, $responseView);
        }
        catch (Exception $ex)
        {
            $responseView = new ViewError(
                "Whoops! Something went wrong.",
                "Something unforseen went wrong: {$ex->getMessage()}"
            );

            $response = SlimLib::createHtmlResponse($this->m_response, $responseView);
        }

        return $response;
    }


    /**
     * Send a request to the SSO to retrieve information about the user.
     * @param string $accessToken
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function sendGetUserRequest(string $accessToken) : \Psr\Http\Message\ResponseInterface
    {
        $url = COGNITO_HOSTED_UI_URL . '/oauth2/userInfo';
        $client = new GuzzleHttp\Client();

        $options =  [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
            ]
        ];

        return $client->request('GET', $url, $options);
    }


    /**
     * Verify that the ID token received is valid and hasn't been tampered with.
     * @param string $idToken
     * @throws ExceptionInvalidToken
     */
    private function verifyIdToken(string $idToken)
    {
        // @todo - verify the token. E.g. check signature etc.
        // https://github.com/firebase/php-jwt
        // https://aws.amazon.com/premiumsupport/knowledge-center/decode-verify-cognito-json-token/

        // throw exception if token is invalid.
        if (false)
        {
            throw new ExceptionInvalidToken($idToken);
        }
    }


    /**
     * Handle a user requesting to logout, by navigating to the logout page.
     */
    private function handleUserLogoutOfEverythingRequest() : \Psr\Http\Message\ResponseInterface
    {
        // Revoke the refresh token and all tokens associated with it.
        $url = COGNITO_HOSTED_UI_URL . "/oauth2/revoke";
        $accessToken = SiteSpecific::getLoggedInUser()->getAccessToken();
        $client = new GuzzleHttp\Client();

        $options =  [
            'headers' => [
                'Authorization' => "Basic " . base64_encode(COGNITO_CLIENT_ID . ":" . COGNITO_CLIENT_SECRET),
            ],
            'form_params' => [
                'token' => SiteSpecific::getLoggedInUser()->getRefreshToken(),
            ]
        ];

        $revokationResponse = $client->post($url, $options);

        if ($revokationResponse->getStatusCode() !== 200)
        {
            throw new Exception("Failed to revoke SSO tokens.");
        }

        // successful responses to a revokation have no body.

        // remove the user from the session, making them not logged in on this site.
        SiteSpecific::removeLoggedInUser();

        // Send the user to the logout page.
        $logoutParameters = [
            'client_id' => COGNITO_CLIENT_ID,
            'logout_uri' => Programster\CoreLibs\Core::getHostname() . '/logout-response',
        ];

        $logoutUrl = COGNITO_HOSTED_UI_URL . "/logout?" . http_build_query($logoutParameters);
        return SlimLib::createRedirectResponse($logoutUrl);
    }


    /**
     * Handle a user requesting to logout, by navigating to the logout page.
     */
    private function handleUserLogoutRequest() : \Psr\Http\Message\ResponseInterface
    {
        // remove the user from the session, making them not logged in on this site.
        SiteSpecific::removeLoggedInUser();

        return SlimLib::createRedirectResponse("/");
    }


    /**
     * Send a request to the SSO to exchange the auth code for user tokens.
     * @param string $authCode
     */
    private function sendTokenRequest(string $authCode) : \Psr\Http\Message\ResponseInterface
    {
        $url = COGNITO_HOSTED_UI_URL . '/oauth2/token';
        $client = new GuzzleHttp\Client();

        $options =  [
            'form_params' => [
                'code' => $authCode,
                'client_id' => COGNITO_CLIENT_ID,
                'grant_type' => 'authorization_code',

                // The docs don't mention this, but one needs to provide the client_secret if your pool has one, which
                // is the default: https://bit.ly/323j6yS
                // This is okay because we are sending direct from server, and this is not exposed to user at all.
                'client_secret' => COGNITO_CLIENT_SECRET,

                // for some reason this needs to be provided and the same as the url that was passed to get the code.
                'redirect_uri' => $this->getLoginRedirectUrl(),
            ]
        ];

        return $client->request('POST', $url, $options);
    }
}

