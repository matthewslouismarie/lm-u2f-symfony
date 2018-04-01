<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

/**
 * @Entity()
 */
class PageMetric
{
    const REQUEST = "TYPE_REQUEST";

    const RESPONSE = "TYPE_RESPONSE";

    /**
     * @Column(type="integer")
     * @GeneratedValue()
     * @Id
     */
    private $id;

    /**
     * @todo Check local setting is using decimal point.
     *
     * @Column(type="float")
     */
    private $microtime;

    /**
     * @Column()
     */
    private $type;

    /**
     * @Column()
     */
    private $uri;

    public function __construct(
        float $microtime,
        string $type,
        string $uri,
        ?int $id = null)
    {
        $this->microtime = $microtime;
        $this->type = $type;
        $this->uri = $uri;
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMicrotime(): float
    {
        return $this->microtimeSpent;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}
