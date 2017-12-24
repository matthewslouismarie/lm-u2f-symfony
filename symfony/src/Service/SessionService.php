<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\Session;

class SessionService
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
        $this->session->start();
    }

    public function get(string $key)
    {
        return $this->session->get($key);
    }

    public function set(string $key, string $value)
    {
        $this->session->set($key, $value);
    }

    public function remove(string $key)
    {
        $this->session->remove($key);
    }
}