<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data;

use FINDOLOGIC\Export\Helpers\UsergroupAwareSimpleValue;

class TaxRate extends UsergroupAwareSimpleValue
{
    public function __construct()
    {
        parent::__construct('taxrates', 'taxrate');
    }

    /**
     * @inheritDoc
     */
    public function getValueName(): string
    {
        return 'taxrate';
    }

    public function getSimpleValue(string $usergroup = '')
    {
        return (float)$this->values[$usergroup];
    }
}
