<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Availability;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class ItemImage extends Entity
{
    private int $id;
    private int $itemId;
    private string $type;
    private string $fileType;
    private string $path;
    private int $position;
    private string $lastUpdate;
    private string $insert;
    private string $md5Checksum;
    private float $width;
    private float $height;
    private float $size;
    private string $storageProviderId;
    private string $md5ChecksumOriginal;
    private string $cleanImageName;
    private string $url;
    private string $urlMiddle;
    private string $urlPreview;
    private string $urlSecondPreview;
    private string $documentUploadPath;
    private string $documentUploadPathPreview;
    private float $documentUploadPreviewWidth;
    private float $documentUploadPreviewHeight;
    /** @var Availability[] */
    private array $availabilities = [];
    /** @var Name[] */
    private array $names = [];

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = (int)$data['id'];
        $this->itemId = (int)$data['itemId'];
        $this->type = (string)$data['type'];
        $this->fileType = (string)$data['fileType'];
        $this->path = (string)$data['path'];
        $this->position = (int)$data['position'];
        $this->lastUpdate = (string)$data['lastUpdate'];
        $this->insert = (string)$data['insert'];
        $this->md5Checksum = (string)$data['md5Checksum'];
        $this->width = (float)$data['width'];
        $this->height = (float)$data['height'];
        $this->size = (float)$data['size'];
        $this->storageProviderId = (string)$data['storageProviderId'];
        $this->md5ChecksumOriginal = (string)$data['md5ChecksumOriginal'];
        $this->cleanImageName = (string)$data['cleanImageName'];
        $this->url = (string)$data['url'];
        $this->urlMiddle = (string)$data['urlMiddle'];
        $this->urlPreview = (string)$data['urlPreview'];
        $this->urlSecondPreview = (string)$data['urlSecondPreview'];
        $this->documentUploadPath = (string)$data['documentUploadPath'];
        $this->documentUploadPathPreview = (string)$data['documentUploadPathPreview'];
        $this->documentUploadPreviewWidth = (float)$data['documentUploadPreviewWidth'];
        $this->documentUploadPreviewHeight = (float)$data['documentUploadPreviewHeight'];

        if (!empty($data['availabilities'])) {
            foreach ($data['availabilities'] as $availability) {
                $this->availabilities[] = new Availability($availability);
            }
        }

        if (!empty($data['names'])) {
            foreach ($data['names'] as $name) {
                $this->names[] = new Name($name);
            }
        }
    }

    public function getData(): array
    {
        $availabilities = [];
        foreach ($this->availabilities as $availability) {
            $availabilities[] =  $availability->getData();
        }

        $names = [];
        foreach ($this->names as $name) {
            $names[] =  $name->getData();
        }

        return [
            'id' => $this->id,
            'itemId' => $this->itemId,
            'type' => $this->type,
            'fileType' => $this->fileType,
            'path' => $this->path,
            'position' => $this->position,
            'lastUpdate' => $this->lastUpdate,
            'insert' => $this->insert,
            'md5Checksum' => $this->md5Checksum,
            'width' => $this->width,
            'height' => $this->height,
            'size' => $this->size,
            'storageProviderId' => $this->storageProviderId,
            'md5ChecksumOriginal' => $this->md5ChecksumOriginal,
            'cleanImageName' => $this->cleanImageName,
            'url' => $this->url,
            'urlMiddle' => $this->urlMiddle,
            'urlPreview' => $this->urlPreview,
            'urlSecondPreview' => $this->urlSecondPreview,
            'documentUploadPath' => $this->documentUploadPath,
            'documentUploadPathPreview' => $this->documentUploadPathPreview,
            'documentUploadPreviewWidth' => $this->documentUploadPreviewWidth,
            'documentUploadPreviewHeight' => $this->documentUploadPreviewHeight,
            'availabilities' => $availabilities,
            'names' => $names
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getLastUpdate(): string
    {
        return $this->lastUpdate;
    }

    public function getInsert(): string
    {
        return $this->insert;
    }

    public function getMd5Checksum(): string
    {
        return $this->md5Checksum;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function getSize(): float
    {
        return $this->size;
    }

    public function getStorageProviderId(): string
    {
        return $this->storageProviderId;
    }

    public function getMd5ChecksumOriginal(): string
    {
        return $this->md5ChecksumOriginal;
    }

    public function getCleanImageName(): string
    {
        return $this->cleanImageName;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getUrlMiddle(): string
    {
        return $this->urlMiddle;
    }

    public function getUrlPreview(): string
    {
        return $this->urlPreview;
    }

    public function getUrlSecondPreview(): string
    {
        return $this->urlSecondPreview;
    }

    public function getDocumentUploadPath(): string
    {
        return $this->documentUploadPath;
    }

    public function getDocumentUploadPathPreview(): string
    {
        return $this->documentUploadPathPreview;
    }

    public function getDocumentUploadPreviewWidth(): float
    {
        return $this->documentUploadPreviewWidth;
    }

    public function getDocumentUploadPreviewHeight(): float
    {
        return $this->documentUploadPreviewHeight;
    }

    /**
     * @return Availability[]
     */
    public function getAvailabilities(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->availabilities;
    }

    /**
     * @return Name[]
     */
    public function getNames(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->names;
    }
}
