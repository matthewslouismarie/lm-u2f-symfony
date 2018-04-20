<?php

declare(strict_types=1);

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
     * @Column(nullable=true, type="boolean")
     */
    private $isRedirection;

    /**
     * @todo Check local setting is using decimal point.
     *
     * @Column(type="float")
     */
    private $microtime;

    /**
     * @Column(nullable=true)
     */
    private $pageTitle;

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
        ?int $id = null,
        ?bool $isRedirection,
        float $microtime,
        ?string $pageTitle,
        string $participantId,
        string $type,
        string $localPath
    ) {
        $this->id = $id;
        $this->isRedirection = $isRedirection;
        $this->microtime = $microtime;
        $this->pageTitle = $pageTitle;
        $this->participantId = $participantId;
        $this->type = $type;
        $this->localPath = $localPath;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMicrotime(): float
    {
        return $this->microtime;
    }

    public function getPageTitle(): ?string
    {
        return $this->pageTitle;
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

    public function isRedirection(): ?bool
    {
        return $this->isRedirection;
    }
}
