<?php

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\WebStoreResponse;

class WebStoreRequest extends Request
{
    protected $method = 'GET';

    protected $endpoint = 'webstores';

    protected $responseClass = WebStoreResponse::class;
}
