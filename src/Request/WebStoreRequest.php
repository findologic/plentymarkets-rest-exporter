<?php

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\WebstoreResponse;

class WebStoreRequest extends Request
{
    protected $method = 'GET';

    protected $endpoint = 'webstores';

    protected $responseClass = WebstoreResponse::class;
}
