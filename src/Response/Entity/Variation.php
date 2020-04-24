<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

class Variation extends Entity
{

    public function jsonSerialize(): array
    {
        return [];
    }
}
