<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @todo interface for session ids?
 * @todo type hinting for get(…) and store(…)
 */
class SecureSession
{
    private const KEY_LENGTH = 32;

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function storeArray(array $array): string
    {
        $key = $this->generateNewKey();
        $this->session->set($key, $array);
        $this->session->save();

        return $key;
    }

    public function storeObject(\Serializable $object, string $class): string
    {
        if (!is_a($object, $class)) {
            throw new \UnexpectedValueException();
        }
        $key = $this->generateNewKey();
        $this->session->set($key, $object);
        $this->session->save();

        return $key;
    }

    public function storeString(string $string): string
    {
        $key = $this->generateNewKey();
        $this->session->set($key, $string);
        $this->session->save();

        return $key;
    }

    public function getArray(?string $key): array
    {
        return $this->session->get($key);
    }

    /**
     * @todo Make it return IObject?
     */
    public function getObject(?string $key, string $class)
    {
        $object = $this->session->get($key);
        if (!is_a($object, $class)) {
            throw new \UnexpectedValueException();
        }

        return $object;
    }

    public function getString(?string $key): string
    {
        return $this->session->get($key);
    }

    public function getAndRemoveArray(?string $key): array
    {
        $value = $this->session->get($key);
        if (!is_array($value)) {
            throw new \UnexpectedValueException();
        }
        $this->session->remove($key);

        return $value;
    }

    public function getAndRemoveObject(?string $key, string $class)
    {
        $value = $this->session->get($key);
        if (!is_a($value, $class)) {
            throw new \UnexpectedValueException();
        }
        $this->session->remove($key);

        return $value;
    }

    public function getAndRemoveString(?string $key): string
    {
        $value = $this->session->get($key);
        if (!is_string($value)) {
            throw new \UnexpectedValueException();
        }
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

    public function remove(?string $key): void
    {
        $this->session->remove($key);
    }
}
