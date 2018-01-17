<?php

namespace App\Service;

use App\FormModel\ISubmission;
use App\Service\SecureSession;
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

    public function add(string $sid, ISubmission $submission): string
    {
        $submissions = $this
            ->sSession
            ->getTypedArray($sid, ISubmission::class)
        ;

        $submissions[] = $submission;

        $this->sSession->remove($sid);

        return $this
            ->sSession
            ->storeArray($submissions)
        ;
    }

    public function create(ISubmission $submission): string
    {
        return $this
            ->sSession
            ->storeArray([$submission])
        ;
    }

    public function get(
        string $sid,
        int $index,
        ?string $class = null): ISubmission
    {
        $stack = $this
            ->sSession
            ->getTypedArray($sid, ISubmission::class)
        ;
        $item = $stack[$index];
        if (null !== $class && !is_a($item, $class)) {
            throw new UnexpectedValueException();
        }

        return $item;
    }

    public function peek(string $sid): ISubmission
    {
        $stack = $this
            ->sSession
            ->getTypedArray($sid, ISubmission::class)
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
