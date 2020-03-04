<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

abstract class Entity
{
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
