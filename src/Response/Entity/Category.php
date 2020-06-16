<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category\CategoryDetails;

class Category extends Entity
{
    private int $id;

    private ?int $parentCategoryId;

    private int $level;

    private string $type;

    private string $linklist;

    private string $right;

    private string $sitemap;

    private bool $hasChildren;

    /** @var CategoryDetails[] */
    private array $details = [];

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->parentCategoryId = $data['parentCategoryId'] ? (int)$data['parentCategoryId'] : null;
        $this->level = (int)$data['level'];
        $this->type = (string)$data['type'];
        $this->linklist = (string)$data['linklist'];
        $this->right = (string)$data['right'];
        $this->sitemap = (string)$data['sitemap'];
        $this->hasChildren = (bool)$data['hasChildren'];

        if (!empty($data['details'])) {
            foreach ($data['details'] as $categoryDetails) {
                $this->details[] = new CategoryDetails($categoryDetails);
            }
        }
    }

    public function getData(): array
    {
        $details = [];
        foreach ($this->details as $categoryDetails) {
            $details[] = $categoryDetails->getData();
        }

        return [
            'id' => $this->id,
            'parentCategoryId' => $this->parentCategoryId,
            'level' => $this->level,
            'type' => $this->type,
            'linklist' => $this->linklist,
            'right' => $this->right,
            'sitemap' => $this->sitemap,
            'hasChildren' => $this->hasChildren,
            'details' => $details
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParentCategoryId(): ?int
    {
        return $this->parentCategoryId;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLinklist(): string
    {
        return $this->linklist;
    }

    public function getRight(): string
    {
        return $this->right;
    }

    public function getSitemap(): string
    {
        return $this->sitemap;
    }

    public function hasChildren(): bool
    {
        return $this->hasChildren;
    }

    /**
     * @return CategoryDetails[]
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
