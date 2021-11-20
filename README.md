Amazon Cognito SSO Demo
=======================

This is a demonstration of integrating a PHP-based web service with Amazon Cognito for an OIDC SSO. This demo relates to [my Cognito tutorials](https://blog.programster.org/tag/Cognito).


## TODO
The following functionality still needs implementing

* Verify the ID token provided by the SSO on the SSO login response handler.
* implement token expiry checking and refreshing.
* implement the SSO requests in a more abstract way, using the psr 7 interface, rather than Guzzle specific
