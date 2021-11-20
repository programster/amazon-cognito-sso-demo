<?php


abstract class AbstractSlimController
{
    protected Psr\Http\Message\RequestInterface $m_request;
    protected \Psr\Http\Message\ResponseInterface $m_response;
    protected $m_args;


    public function __construct(\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {
        $this->m_request = $request;
        $this->m_response = $response;
        $this->m_args = $args;
    }


    // this one is optional - refer to Slim3 - Simplifying Routing At Scale
    // https://blog.programster.org/slim3-simplifying-routing-at-scale
    abstract public static function registerRoutes($app);
}