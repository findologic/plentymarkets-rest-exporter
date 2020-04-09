<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class WebStoreRequest extends Request
{
    public function __construct()
    {
        parent::__construct('GET', 'webstores');
    }
}
