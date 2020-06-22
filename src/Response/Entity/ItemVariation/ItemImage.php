<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Availability;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation\ItemImage\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class ItemImage extends Entity
{
    /** @var int */
    private $id;

    /** @var int */
    private $itemId;

    /** @var string */
    private $type;

    /** @var string */
    private $fileType;

    /** @var string */
    private $path;

    /** @var int */
    private $position;

    /** @var string */
    private $lastUpdate;

    /** @var string */
    private $insert;

    /** @var string */
    private $md5Checksum;

    /** @var float */
    private $width;

    /** @var float */
    private $height;

    /** @var float */
    private $size;

    /** @var string */
    private $storageProviderId;

    /** @var string */
    private $md5ChecksumOriginal;

    /** @var string */
    private $cleanImageName;

    /** @var string */
    private $url;

    /** @var string */
    private $urlMiddle;

    /** @var string */
    private $urlPreview;

    /** @var string */
    private $urlSecondPreview;

    /** @var string */
    private $documentUploadPath;

    /** @var string */
    private $documentUploadPathPreview;

    /** @var float */
    private $documentUploadPreviewWidth;

    /** @var float */
    private $documentUploadPreviewHeight;

    /** @var Availability[] */
    private $availabilities = [];

    /** @var Name[] */
    private $names = [];

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
