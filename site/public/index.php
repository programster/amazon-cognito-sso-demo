<?php

require_once(__DIR__ . '/../bootstrap.php');

# Manually requiring the AuthUser object here as this must be defined before any session, so that it can be used in
# the session.
require_once(__DIR__ . '/../models/AuthUser.php');

session_start();

$app = Slim\Factory\AppFactory::create();

$app->addErrorMiddleware(
    $displayErrorDetails=true,
    $logErrors=true,
    $logErrorDetails=true
);

AuthController::registerRoutes($app);
HomeController::registerRoutes($app);

$app->run();
