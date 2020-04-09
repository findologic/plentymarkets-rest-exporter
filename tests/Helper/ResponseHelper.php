<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper;

use GuzzleHttp\Psr7\Response;

trait ResponseHelper
{
    public function getMockResponse(string $file): Response
    {
        $response = file_get_contents(__DIR__ . '/../MockData/' . $file);

        return new Response(200, [], $response);
    }
}
