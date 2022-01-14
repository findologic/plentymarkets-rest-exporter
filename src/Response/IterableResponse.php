<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response;

abstract class IterableResponse extends Response
{
    protected int $page;
    protected int $totalsCount;
    protected bool $isLastPage;
    protected int $lastPageNumber;
    protected int $firstOnPage;
    protected int $lastOnPage;
    protected int $itemsPerPage;

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
