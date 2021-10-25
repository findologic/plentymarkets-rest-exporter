<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data;

use FINDOLOGIC\Export\Helpers\UsergroupAwareNumericValue;

class InsteadPrice extends UsergroupAwareNumericValue
{
    public function __construct()
    {
        parent::__construct('insteads', 'instead');
    }

    /**
     * @inheritDoc
     */
    public function getValueName(): string
    {
        return 'instead';
    }
}
