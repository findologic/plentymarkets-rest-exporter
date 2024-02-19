<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Request;

use FINDOLOGIC\PlentyMarketsRestExporter\Request\CategoryRequest;
use PHPUnit\Framework\TestCase;

class CategoryRequestTest extends TestCase
{
    public function testIterableRequestParamsAreSetProperly(): void
    {
        $expectedStoreIdentifier = 1337;
        $expectedItemsPerPage = 83;
        $expectedPage = 55;

        $request = new CategoryRequest($expectedStoreIdentifier);
        $request->setItemsPerPage($expectedItemsPerPage);
        $request->setPage($expectedPage);

        $this->assertSame([
            'type' => 'item', // Set by default.
            'with' => ['details', 'tags'], // Set by default.
            'plentyId' => $expectedStoreIdentifier,
            'page' => $expectedPage,
            'itemsPerPage' => $expectedItemsPerPage,
        ], $request->getParams());
    }
}
