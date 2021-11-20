<?php

/*
 * A class to represent a logged in user.
 * This is like a normal user object, but we expect additional information like their cognito auth token.
 */

class AuthUser
{
    private string $m_accessToken;
    private string $m_refreshToken;
    private string $m_idToken;
    private string $m_email;


    public function __construct(string $idToken, string $accessToken, string $refreshToken, string $email)
    {
        $this->m_idToken = $idToken;
        $this->m_accessToken = $accessToken;
        $this->m_refreshToken = $refreshToken;
        $this->m_email = $email;
    }


    # Accessors
    public function getIdToken() : string { return $this->m_idToken; }
    public function getAccessToken() : string { return $this->m_accessToken; }
    public function getRefreshToken() : string { return $this->m_refreshToken; }
    public function getEmail() : string { return $this->m_email; }
}