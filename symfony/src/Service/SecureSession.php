<?php

declare(strict_types=1);

namespace App\Service;

use InvalidArgumentException;
use Serializable;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use UnexpectedValueException;

/**
 * @todo Thread-safe (e.g. storeArray)? Should be.
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
        string $sid
    ): void {
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
        $sid = $this->generateNewKey();
        $this->session->set($sid, $array);
        $this->session->save();

        return $sid;
    }

    public function storeObject(Serializable $object, string $class): string
    {
        if (!is_a($object, $class)) {
            throw new UnexpectedValueException();
        }
        $sid = $this->generateNewKey();
        $this->session->set($sid, $object);
        $this->session->save();

        return $sid;
    }

    public function storeString(string $string): string
    {
        $sid = $this->generateNewKey();
        $this->session->set($sid, $string);
        $this->session->save();

        return $sid;
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

    public function getArray(string $sid): array
    {
        return $this->session->get($sid);
    }

    public function getTypedArray(string $sid, string $class): array
    {
        $array = $this
            ->session
            ->get($sid)
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

    public function getObject(string $sid, string $class)
    {
        $object = $this->session->get($sid);
        if (!is_a($object, $class)) {
            throw new UnexpectedValueException();
        }

        return $object;
    }

    public function getString(string $sid): string
    {
        return $this->session->get($sid);
    }

    public function getAndRemoveArray(string $sid): array
    {
        $value = $this->session->get($sid);
        if (!is_array($value)) {
            throw new UnexpectedValueException();
        }
        $this->session->remove($sid);

        return $value;
    }

    public function getAndRemoveObject(string $sid, string $class)
    {
        $value = $this->session->get($sid);
        if (!is_a($value, $class)) {
            throw new UnexpectedValueException();
        }
        $this->session->remove($sid);

        return $value;
    }

    public function getAndRemoveString(string $sid): string
    {
        $value = $this->session->get($sid);
        if (!is_string($value)) {
            throw new UnexpectedValueException();
        }
        $this->session->remove($sid);

        return $value;
    }

    public function generateNewKey(): string
    {
        do {
            $randomSid = bin2hex(random_bytes(self::KEY_LENGTH));
        } while ($this->session->has($randomSid));

        return $randomSid;
    }

    public function remove(string $sid): void
    {
        $this->session->remove($sid);
    }

    public function deleteObject(string $sid, string $class): void
    {
        $object = $this->session->get($sid);
        if (!is_a($object, $class)) {
            throw new UnexpectedValueException();
        }
        $this->session->remove($sid);
    }
}
