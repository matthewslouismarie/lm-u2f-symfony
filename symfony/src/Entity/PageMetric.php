<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

/**
 * @Entity(repositoryClass="App\Repository\PageMetricRepository")
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
    private $participantId;

    /**
     * @Column()
     */
    private $type;

    /**
     * @Column()
     */
    private $localPath;

    public function __construct(
        float $microtime,
        string $participantId,
        string $type,
        string $localPath,
        ?int $id = null
    ) {
        $this->microtime = $microtime;
        $this->participantId = $participantId;
        $this->type = $type;
        $this->localPath = $localPath;
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMicrotime(): float
    {
        return $this->microtime;
    }

    public function getParticipantId(): string
    {
        return $this->participantId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLocalPath(): string
    {
        return $this->localPath;
    }
}
