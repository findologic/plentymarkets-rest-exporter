<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

trait IterableRequest
{
    protected int $page = 1;
    protected int $itemsPerPage = 100;

    public function getParams(): array
    {
        $params = parent::getParams();
        $params['page'] = $this->page;
        $params['itemsPerPage'] = $this->itemsPerPage;

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

    public function getItemsPerPage(): int 
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }
}
