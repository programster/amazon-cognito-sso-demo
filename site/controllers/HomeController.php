<?php


class HomeController extends AbstractSlimController
{
    public static function registerRoutes($app)
    {
        // Landing page. User may or may not be logged in. Show them login button if they are not, or redirect them to dashboard.
        $app->get('/', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, $args) {
            $homeController = new HomeController($request, $response, $args);
            return $homeController->handleLandingPage();
        });

        // Show the use the dashboard. One can only get here if logged in.
        $app->get('/dashboard', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, $args) {
            $homeController = new HomeController($request, $response, $args);
            return $homeController->handleShowDashboard();
        })->add(new MiddlewareCheckLoggedIn());
    }


    private function handleShowDashboard() : Slim\Psr7\Response
    {
        $body = new ViewLoggedInDashboard(SITE_NAME, SiteSpecific::getLoggedInUser());
        return SlimLib::createHtmlResponse($this->m_response, $body);
    }


    /**
     * Handle the user going to the landing page (/).
     * IF the user is already logged in, redirect them to the dashboard. Otherwise, show the login button.
     * @return Slim\Psr7\Response
     */
    private function handleLandingPage() : Slim\Psr7\Response
    {
        if (SiteSpecific::isLoggedIn())
        {
            $response = SlimLib::createRedirectResponse("/dashboard");
        }
        else
        {
            $response = SlimLib::createHtmlResponse($this->m_response, new ViewHome());
        }

        return $response;
    }
}

