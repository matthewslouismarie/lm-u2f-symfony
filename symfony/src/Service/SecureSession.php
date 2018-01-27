<?php

namespace App\Service;

use InvalidArgumentException;
use UnexpectedValueException;
use Serializable;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @todo interface for session ids?
 * @todo Sids shouldn't be nullable.
 * @todo Not thread-safe (e.g. storeArray).
 * @todo Prevent any modification to stored variables?
 */
class SecureSession
{
    private const KEY_LENGTH = 32;

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function setObject(string $sid, $object, string $class): void
    {
        if (!is_a($object, $class)) {
            throw new InvalidArgumentException();
        }
        $this->session->set($sid, $object);
        $this->session->save();
    }

    public function storeTypedArray(
        array $array,
        string $class,
        string $sid): void
    {
        foreach ($array as $item) {
            if (!is_a($item, $class)) {
                throw new InvalidArgumentException();
            }
        }
        $this
            ->session
            ->set($sid, $array)
        ;
        $this
            ->session
            ->save()
        ;
    }

    public function storeArray(array $array): string
    {
        $key = $this->generateNewKey();
        $this->session->set($key, $array);
        $this->session->save();

        return $key;
    }

    public function storeObject(Serializable $object, string $class): string
    {
        if (!is_a($object, $class)) {
            throw new UnexpectedValueException();
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

    public function isTypedArray(string $sid, string $itemClass): bool
    {
        $valueToCheck = $this
            ->session
            ->get($sid)
        ;
        if (!is_array($valueToCheck)) {
            return false;
        }
        foreach ($valueToCheck as $item) {
            if (!is_a($item, $itemClass)) {
                return false;
            }
        }

        return true;
    }

    public function getArray(?string $key): array
    {
        return $this->session->get($key);
    }

    public function getTypedArray(string $key, string $class): array
    {
        $array = $this
            ->session
            ->get($key)
        ;
        if (!is_array($array)) {
            throw new UnexpectedValueException();
        }
        foreach ($array as $item) {
            if (!is_a($item, $class)) {
                throw new UnexpectedValueException();
            }
        }

        return $array;
    }

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

    public function deleteObject(string $key, string $class): void
    {
        $object = $this->session->get($key);
        if (!is_a($object, $class)) {
            throw new UnexpectedValueException();
        }
        $this->session->remove($key);
    }
}
