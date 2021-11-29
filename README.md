Amazon Cognito SSO Demo
=======================

This is a demonstration of integrating a PHP-based web service with Amazon Cognito for an OIDC SSO.
This demo relates to [my Cognito tutorials](https://blog.programster.org/tag/Cognito).


## Usage
1. [Set up a Amazon Cognito user pool](https://blog.programster.org/creating-an-amazon-cognito-user-pool).
1. Copy the `.env.example` file to `.env`
1. Fill in the variables appropriately (using the details for the pool you set up).
1. Build the docker image with `docker-compose build`
1. Run the web services with `docker-comopose up`
1. Navigate to the sites on `localhost:8000` and `localhost:8080`, with both sites making use of the same SSO, so you
only really have to log in once.


## TODO
The following functionality still needs implementing

* Verify the ID token provided by the SSO on the SSO login response handler.
* implement token expiry checking and refreshing.
* implement the SSO requests in a more abstract way, using the psr 7 interface, rather than Guzzle specific


