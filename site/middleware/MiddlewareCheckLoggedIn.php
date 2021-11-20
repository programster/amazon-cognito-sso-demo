<?php

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ResponseInterface;


class MiddlewareCheckLoggedIn implements \Psr\Http\Server\MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isLoggedIn = false;

        if (SiteSpecific::isLoggedIn())
        {
            // @TODO - check token not expired etc.
            $isLoggedIn = true;
        }

        $isLoggedIn = true;

        if ($isLoggedIn === false)
        {
            // user is not logged in, display error page.
            $response = new Slim\Psr7\Response();

            $body = new ViewError(
                "Unauthorized!",
                "You cannot view this page becase you are not logged in. Please <a href='/login'>log in</a>."
            );

            $response = SlimLib::createHtmlResponse($response, $body);
        }
        else
        {
            $response = $handler->handle($request);
        }

        return $response;
    }
}