<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Request;

use FINDOLOGIC\PlentyMarketsRestExporter\Request\PimVariationRequest;
use PHPUnit\Framework\TestCase;

class PimVariationRequestTest extends TestCase
{
    public function testParamsAreProperlySet(): void
    {
        $expectedParamKey = 'blub';
        $expectedParamValue = 'heh';
        $expectedWithParams = ['attributes', 'properties'];
        $expectedPage = 5;
        $expectedItemsPerPage = 99;

        $request = new PimVariationRequest();
        $request->setParam($expectedParamKey, $expectedParamValue);
        $request->setWith($expectedWithParams);
        $request->setPage($expectedPage);
        $request->setItemsPerPage($expectedItemsPerPage);

        $this->assertSame([
            $expectedParamKey => $expectedParamValue,
            'with' => $expectedWithParams,
            'page' => $expectedPage,
            'itemsPerPage' => $expectedItemsPerPage
        ], $request->getParams());
    }
}
