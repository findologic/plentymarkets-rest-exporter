<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response;

abstract class IterableResponse extends Response
{
    /** @var int */
    protected $page;

    /** @var int */
    protected $totalsCount;

    /** @var bool */
    protected $isLastPage;

    /** @var int */
    protected $lastPageNumber;

    /** @var int */
    protected $firstOnPage;

    /** @var int */
    protected $lastOnPage;

    /** @var int */
    protected $itemsPerPage;

    public function getPage(): int
    {
        return $this->page;
    }

    public function getTotalsCount(): int
    {
        return $this->totalsCount;
    }

    public function isLastPage(): bool
    {
        return $this->isLastPage;
    }

    public function getLastPageNumber(): int
    {
        return $this->lastPageNumber;
    }

    public function getFirstOnPage(): int
    {
        return $this->firstOnPage;
    }

    public function getLastOnPage(): int
    {
        return $this->lastOnPage;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
}
