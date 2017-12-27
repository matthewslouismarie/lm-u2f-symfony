<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @todo interface for session ids?
 * @todo type hinting for get(…) and store(…)
 */
class SecureSessionService
{
    private const KEY_LENGTH = 32;
    private $session;

    public function __construct()
    {
        $this->session = new Session();
        $this->session->start();
    }

    public function store($value): string
    {
        $key = $this->generateNewKey();
        $this->session->set($key, $value);
        return $key;
    }

    public function get(string $key)
    {
        return $this->session->get($key);
    }

    public function getAndRemove(string $key)
    {
        $value = $this->session->get($key);
        $this->session->remove($key);
        return $value;
    }

    private function generateNewKey(): string
    {
        do {
            $random_key = bin2hex(random_bytes(self::KEY_LENGTH));
        } while ($this->session->has($random_key));
        return $random_key;
    }
}