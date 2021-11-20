<?php


class ViewHome extends \Programster\AbstractView\AbstractView
{

    protected function renderContent()
    {
?>


<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400&display=swap" rel="stylesheet">
        <style>
            body{
                font-family: 'Roboto', sans-serif;
            }
        </style>
    </head>
    <body>

        <h1>Cognito SSO Demo</h1>

        <script>
            var url_str = window.location.href;

            // On successful authentication, AWS Cognito will redirect to Call-back URL and pass the access_token as a request parameter.
            // If you notice the URL, a “#” symbol is used to separate the query parameters instead of the “?” symbol.
            // So we need to replace the “#” with “?” in the URL and call the page again.

            if (url_str.includes("#"))
            {
                var url_str_hash_replaced = url_str.replace("#", "?");
                window.location.href = url_str_hash_replaced;
            }

        </script>

        <p>Welcome to <?= SITE_NAME ?>. Do you wish to <a href="/login">log in</a> using the SSO?</p>
    </body>
</html>


<?php
    }
}

