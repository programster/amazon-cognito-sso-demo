<?php



class ViewLoggedInDashboard extends \Programster\AbstractView\AbstractView
{
    private string $m_siteName;
    private string $m_loggedInUserEmail;


    public function __construct(string $siteName, AuthUser $loggedInUser)
    {
        $this->m_siteName = $siteName;
        $this->m_loggedInUserEmail = $loggedInUser->getEmail();
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

    <h1><?= $this->m_siteName; ?> Dashboard</h1>

    <p>Congratulations! You're logged in as <?= $this->m_loggedInUserEmail; ?>. This page represents the dashboard that only logged in users can access.</p>

    <p><a href="/logout">Logout of just this site.</a> This will log you out of just this service.</p>

    <p><a href="/logout-everything">Logout of everything</a>. This will log you out of this service, and the SSO, and revoke the refresh token so that other services that use it will require you to log in as well.</p>

<?php
    }

}

