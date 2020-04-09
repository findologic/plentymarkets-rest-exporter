<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

interface IterableRequestInterface
{
    public function getParams(): array;

    public function setPage(int $page);

    public function getPage(): int;
}
