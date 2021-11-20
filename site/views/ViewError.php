<?php

/*
 * An error page to show to the user.
 */

class ViewError extends \Programster\AbstractView\AbstractView
{
    private string $m_title;
    private string $m_body;


    public function __construct(string $title, string $body)
    {
        $this->m_title = $title;
        $this->m_body = $body;
    }


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
        <h1><?= $this->m_title; ?></h1>
        <p><?= $this->m_body; ?></p>
    </body>
</html>

<?php
    }
}
