<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data;

use BadMethodCallException;
use DOMDocument;
use FINDOLOGIC\Export\Data\Property as LibFlexportProperty;
use FINDOLOGIC\Export\Helpers\Serializable;

class Property extends LibFlexportProperty implements Serializable
{
    /**
     * @inheritDoc
     */
    public function getDomSubtree(DOMDocument $document)
    {
        throw new BadMethodCallException('Properties can not be serialized without any additional data.');
    }

    /**
     * @inheritDoc
     */
    public function getCsvFragment(array $availableProperties = [])
    {
        throw new BadMethodCallException('Properties can not be serialized without any additional data.');
    }
}
