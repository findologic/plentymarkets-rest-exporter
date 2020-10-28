<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper;

use GuzzleHttp\Psr7\Response;

trait ResponseHelper
{
    public function getMockResponse(string $file): Response
    {
        $response = $this->getFileContents($file);

        return new Response(200, [], $response);
    }

    private function getFileContents(string $file): string
    {
        return file_get_contents(__DIR__ . '/../MockData/' . $file);
    }
}
