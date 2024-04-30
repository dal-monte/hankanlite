<?php

class Router
{
    public $router;

    public function __construct($routes)
    {
        $this->router = $routes;
    }

    public function resolve($pathInfo)
    {
        foreach ($this->router as $path => $pattern) {
            if ($path === $pathInfo) {
                return $pattern;
            }
        }

        return false;
    }
}
