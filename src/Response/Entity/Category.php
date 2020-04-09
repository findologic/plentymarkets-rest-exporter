<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

class Category extends Entity
{
    /** @var int */
    private $id;

    /** @var int|null */
    private $parentCategoryId;

    /** @var int */
    private $level;

    /** @var string */
    private $type;

    /** @var string */
    private $linklist;

    /** @var string */
    private $right;

    /** @var string */
    private $sitemap;

    /** @var bool */
    private $hasChildren;

    /** @var CategoryDetails[] */
    private $details = [];

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

        foreach ($data['details'] as $categoryDetails) {
            $this->details[] = new CategoryDetails($categoryDetails);
        }
    }

    public function jsonSerialize(): array
    {
        $details = [];
        foreach ($this->details as $categoryDetails) {
            $details[] = $categoryDetails->jsonSerialize();
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
