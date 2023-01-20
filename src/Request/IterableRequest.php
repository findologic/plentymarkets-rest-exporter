<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

trait IterableRequest
{
    /** Maximum count of entities per page. */
    public static int $ITEMS_PER_PAGE = 100;
    protected int $page = 1;

    public function getParams(): array
    {
        $params = parent::getParams();
        $params['page'] = $this->page;
        $params['itemsPerPage'] = static::$ITEMS_PER_PAGE;

        return $params;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setItemsPerPage(int $itemsPerPage): self
    {
        static::$ITEMS_PER_PAGE = $itemsPerPage;

        return $this;
    }
}
