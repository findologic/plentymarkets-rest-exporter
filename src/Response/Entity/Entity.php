<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

abstract class Entity
{
    abstract public function getData(): array;
}
