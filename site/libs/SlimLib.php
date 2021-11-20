<?php

/*
 * A library of helper functions specific to the Slim framework.
 */

class SlimLib
{
    public static function createHtmlResponse(
        \Slim\Psr7\Response $existingResponse,
        string|Stringable $body,
        int $httpStatusCode=200
    ) : Psr\Http\Message\ResponseInterface
    {
        $responseBody = $existingResponse->getBody();
        $responseBody->write((string) $body); // returns number of bytes written
        $newResponse = $existingResponse->withBody($responseBody)->withStatus($httpStatusCode);
        return $newResponse;
    }


    /**
     * Create a response that will redirect the user to the specified location.
     * @param string $redirectTo - where to redirect the user to.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function createRedirectResponse(string $redirectTo) : \Psr\Http\Message\ResponseInterface
    {
        $response = new Slim\Psr7\Response(302);
        $response = $response->withHeader('Location', $redirectTo);
        return $response;
    }
}