<?php

namespace App\Service;

use Serializable;
use UnexpectedValueException;

/**
 * @todo Add more precise type-hinting?
 * @todo Rename to SubmissionStackManager?
 */
class SubmissionStack
{
    private $sSession;

    public function __construct(SecureSession $sSession)
    {
        $this->sSession = $sSession;
    }

    public function add(string $sid, Serializable $submission): void
    {
        $submissions = $this
            ->sSession
            ->getTypedArray($sid, Serializable::class)
        ;

        $submissions[] = $submission;

        $this
            ->sSession
            ->storeTypedArray($submissions, Serializable::class, $sid)
        ;
    }

    public function set(string $sid, int $index, Serializable $submission): void
    {
        $submissions = $this
            ->sSession
            ->getTypedArray($sid, Serializable::class)
        ;

        $submissions[$index] = $submission;

        $this
            ->sSession
            ->storeTypedArray($submissions, Serializable::class, $sid)
        ;
    }

    public function create(?Serializable $submission = null): string
    {
        if (null === $submission) {
            return $this
                ->sSession
                ->storeArray([])
            ;
        } else {
            return $this
                ->sSession
                ->storeArray([$submission])
            ;
        }
    }

    public function get(
        string $sid,
        int $index,
        ?string $class = null): Serializable
    {
        $stack = $this
            ->sSession
            ->getTypedArray($sid, Serializable::class)
        ;
        $item = $stack[$index];
        if (null !== $class && !is_a($item, $class)) {
            throw new UnexpectedValueException();
        }

        return $item;
    }

    public function peek(string $sid): Serializable
    {
        $stack = $this
            ->sSession
            ->getTypedArray($sid, Serializable::class)
        ;

        return $stack[count($stack)];
    }

    /**
     * @todo
     */
    public function isValid(string $sid): bool
    {
        return true;
    }
}
