<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Repository extends Entity
{
    private ?int $id;

    private ?string $remoteUrl;

    private ?string $username;

    private ?string $branch;

    private ?string $webhookToken;

    private ?string $autoFetch;

    private ?string $createdAt;

    private ?string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $this->getIntProperty('id', $data);
        $this->remoteUrl = $this->getStringProperty('remoteUrl', $data);
        $this->username = $this->getStringProperty('username', $data);
        $this->branch = $this->getStringProperty('branch', $data);
        $this->webhookToken = $this->getStringProperty('webhookToken', $data);
        $this->autoFetch = $this->getStringProperty('autoFetch', $data);
        $this->createdAt = $this->getStringProperty('createdAt', $data);
        $this->updatedAt = $this->getStringProperty('updatedAt', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'remoteUrl' => $this->remoteUrl,
            'username' => $this->username,
            'branch' => $this->branch,
            'webhookToken' => $this->webhookToken,
            'autoFetch' => $this->autoFetch,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemoteUrl(): ?string
    {
        return $this->remoteUrl;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getWebhookToken(): ?string
    {
        return $this->webhookToken;
    }

    public function getAutoFetch(): ?string
    {
        return $this->autoFetch;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getBranch(): ?string
    {
        return $this->branch;
    }
}
