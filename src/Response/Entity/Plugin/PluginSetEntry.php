<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class PluginSetEntry extends Entity
{
    /** @var int|null */
    private $id;

    /** @var int|null */
    private $pluginId;

    /** @var int|null */
    private $pluginSetId;

    /** @var string|null */
    private $createdAt;

    /** @var string|null */
    private $updatedAt;

    /** @var string|null */
    private $deleted_at;

    /** @var string|null */
    private $branchName;

    /** @var string|null */
    private $position;

    /** @var string|null */
    private $commit;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->pluginId = $this->getIntProperty('pluginId', $data);
        $this->pluginSetId = $this->getIntProperty('pluginSetId', $data);
        $this->createdAt = $this->getStringProperty('createdAt', $data);
        $this->updatedAt = $this->getStringProperty('updatedAt', $data);
        $this->deleted_at = $this->getStringProperty('deleted_at', $data);
        $this->branchName = $this->getStringProperty('branchName', $data);
        $this->position = $this->getStringProperty('position', $data);
        $this->commit = $this->getStringProperty('commit', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'pluginId' => $this->pluginId,
            'pluginSetId' => $this->pluginSetId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deleted_at' => $this->deleted_at,
            'branchName' => $this->branchName,
            'position' => $this->position,
            'commit' => $this->commit
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPluginId(): ?int
    {
        return $this->pluginId;
    }

    public function getPluginSetId(): ?int
    {
        return $this->pluginSetId;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deleted_at;
    }

    public function getBranchName(): ?string
    {
        return $this->branchName;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function getCommit(): ?string
    {
        return $this->commit;
    }
}
