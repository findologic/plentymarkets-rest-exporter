<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection;

interface IterableResponseInterface
{
    public function getPage(): int;

    public function getTotalsCount(): int;

    public function isLastPage(): bool;

    public function getLastPageNumber(): int;

    public function getFirstOnPage(): int;

    public function getLastOnPage(): int;

    public function getItemsPerPage(): int;
}
