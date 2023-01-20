<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;

use DateTime;
use DateTimeInterface;
use Exception;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Translatable;

class CategoryDetails extends Entity implements Translatable
{
    private int $categoryId;

    private string $lang;

    private string $name;

    private string $description;

    private string $description2;

    private string $shortDescription;

    private string $metaKeywords;

    private string $metaDescription;

    private string $nameUrl;

    private string $metaTitle;

    private int $position;

    private DateTimeInterface $updatedAt;

    private string $updatedBy;

    private string $itemListView;

    private string $singleItemView;

    private string $pageView;

    private ?string $fullText;

    private string $metaRobots;

    private string $canonicalLink;

    private string $previewUrl;

    private ?string $image;

    private ?string $imagePath;

    private ?string $image2;

    private ?string $image2Path;

    private int $plentyId;

    /**
     * @throws Exception
     */
    public function __construct(array $data)
    {
        $this->categoryId = (int)$data['categoryId'];
        $this->lang = strtoupper((string)$data['lang']);
        $this->name = (string)$data['name'];
        $this->description = (string)$data['description'];
        $this->description2 = (string)$data['description2'];
        $this->shortDescription = (string)$data['shortDescription'];
        $this->metaKeywords = (string)$data['metaKeywords'];
        $this->metaDescription = (string)$data['metaDescription'];
        $this->nameUrl = (string)$data['nameUrl'];
        $this->metaTitle = (string)$data['metaTitle'];
        $this->position = (int)$data['position'];
        $this->updatedAt = new DateTime($data['updatedAt']);
        $this->updatedBy = (string)$data['updatedBy'];
        $this->itemListView = (string)$data['itemListView'];
        $this->singleItemView = (string)$data['singleItemView'];
        $this->pageView = (string)$data['pageView'];
        $this->fullText = isset($data['fullText']) ? (string)$data['fullText'] : null;
        $this->metaRobots = (string)$data['metaRobots'];
        $this->canonicalLink = (string)$data['canonicalLink'];
        $this->previewUrl = (string)$data['previewUrl'];
        $this->image = $data['image'] ? (string)$data['image'] : null;
        $this->imagePath = $data['imagePath'] ? (string)$data['imagePath'] : null;
        $this->image2 = $data['image2'] ? (string)$data['image2'] : null;
        $this->image2Path = $data['image2Path'] ? (string)$data['image2Path'] : null;
        $this->plentyId = (int)$data['plentyId'];
    }

    public function getData(): array
    {
        return [
            'categoryId' => $this->categoryId,
            'lang' => $this->lang,
            'name' => $this->name,
            'description' => $this->description,
            'description2' => $this->description2,
            'shortDescription' => $this->shortDescription,
            'metaKeywords' => $this->metaKeywords,
            'nameUrl' => $this->nameUrl,
            'metaTitle' => $this->metaTitle,
            'metaDescription' => $this->metaDescription,
            'position' => $this->position,
            'updatedAt' => $this->updatedAt,
            'updatedBy' => $this->updatedBy,
            'itemListView' => $this->itemListView,
            'singleItemView' => $this->singleItemView,
            'pageView' => $this->pageView,
            'fullText' => $this->fullText,
            'metaRobots' => $this->metaRobots,
            'canonicalLink' => $this->canonicalLink,
            'previewUrl' => $this->previewUrl,
            'image' => $this->image,
            'imagePath' => $this->imagePath,
            'image2' => $this->image2,
            'image2Path' => $this->image2Path,
            'plentyId' => $this->plentyId
        ];
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDescription2(): string
    {
        return $this->description2;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function getNameUrl(): string
    {
        return $this->nameUrl;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getUpdatedBy(): string
    {
        return $this->updatedBy;
    }

    public function getItemListView(): string
    {
        return $this->itemListView;
    }

    public function getSingleItemView(): string
    {
        return $this->singleItemView;
    }

    public function getPageView(): string
    {
        return $this->pageView;
    }

    public function getFullText(): ?string
    {
        return $this->fullText;
    }

    public function getMetaRobots(): string
    {
        return $this->metaRobots;
    }

    public function getCanonicalLink(): string
    {
        return $this->canonicalLink;
    }

    public function getPreviewUrl(): string
    {
        return $this->previewUrl;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function getImage2(): ?string
    {
        return $this->image2;
    }

    public function getImage2Path(): ?string
    {
        return $this->image2Path;
    }

    public function getPlentyId(): int
    {
        return $this->plentyId;
    }
}
