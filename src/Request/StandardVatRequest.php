<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class StandardVatRequest extends Request
{
    public function __construct(int $plentyId)
    {
        parent::__construct(
            'GET',
            'vat/standard',
            [
                'plentyId' => $plentyId
            ]
        );
    }
}
