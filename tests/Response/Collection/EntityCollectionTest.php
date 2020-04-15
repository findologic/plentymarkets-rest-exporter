<?php

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CategoryResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\WebStoreResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EntityCollectionTest extends TestCase
{
    use ResponseHelper;

    public function testGettingFirstEntityOfCollectionWithoutEntitiesReturnsNull(): void
    {
        $webStoreResponse = new WebStoreResponse([]);
        $this->assertNull($webStoreResponse->first());
    }

    public function testGettingCriteriaThatDoesNotExistOnTheEntityWillBeIgnored(): void
    {
        $categoryResponse = CategoryParser::parse($this->getMockResponse('CategoryResponse/response.json'));
        $category = $categoryResponse->findOne([
            'asjkadhakjsdah' => 1,
            'saasdudziu' => 'yep',
            'this still works?' => 'totally',
            'id' => 16
        ]);

        $this->assertInstanceOf(Category::class, $category);
    }

    public function testGettingCriterionWhichIsNotIterableShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Criteria expected "id" to be of type "array". Returned a value of type "integer".'
        );

        $categoryResponse = CategoryParser::parse($this->getMockResponse('CategoryResponse/response.json'));
        $categoryResponse->findOne([
            'asjkadhakjsdah' => 1,
            'saasdudziu' => 'yep',
            'this still works?' => 'totally',
            'id' => [
                'ID is not iterable' => 16
            ]
        ]);
    }
}
