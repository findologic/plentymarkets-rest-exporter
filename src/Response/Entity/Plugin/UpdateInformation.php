<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class UpdateInformation extends Entity
{
    /** @var bool|null */
    private $hasUpdate;

    /** @var mixed|null */
    private $updateVariationId;

    /** @var mixed|null */
    private $updateVersion;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->hasUpdate = $this->getBoolProperty('hasUpdate', $data);
        $this->updateVariationId = $data['updateVariationId'] ?? null;
        $this->updateVersion = $data['updateVersion'] ?? null;
    }

    public function getData(): array
    {
        return [
            'hasUpdate' => $this->hasUpdate,
            'updateVariationId' => $this->updateVariationId,
            'updateVersion' => $this->updateVersion
        ];
    }

    public function getHasUpdate(): ?bool
    {
        return $this->hasUpdate;
    }

    /**
     * @return mixed|null
     */
    public function getUpdateVariationId()
    {
        return $this->updateVariationId;
    }

    /**
     * @return mixed|null
     */
    public function getUpdateVersion()
    {
        return $this->updateVersion;
    }
}
