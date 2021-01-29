<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use Carbon\Carbon;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PropertyGroupParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class PropertyGroupResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $propertyGroupResponse;

    public function setUp(): void
    {
        // Set timezone for proper comparison of timestaps in the export.
        date_default_timezone_set('Europe/Vienna');
        $this->response = $this->getMockResponse('PropertyGroupResponse/response.json');
        $this->propertyGroupResponse = PropertyGroupParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'id' => 1
                ],
                'expectedId' => 1
            ],
        ];
    }

    /**
     * @dataProvider criteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $propertyGroup = $this->propertyGroupResponse->findOne($criteria);

        $this->assertEquals($expectedId, $propertyGroup->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(11, $this->propertyGroupResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'isSurchargePercental' => false
        ];

        self::assertCount(10, $this->propertyGroupResponse->find($criteria));
    }

    public function testPropertyGroupDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $propertyGroup = $this->propertyGroupResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $propertyGroup->getData());

        $this->assertEquals(1, $propertyGroup->getId());
        $this->assertEquals('Mein Paket', $propertyGroup->getBackendName());
        $this->assertEquals('none', $propertyGroup->getOrderPropertyGroupingType());
        $this->assertEquals(false, $propertyGroup->isSurchargePercental());
        $this->assertEquals(0, $propertyGroup->getOttoComponent());
        $this->assertEquals(
            Carbon::createFromTimeString($responseData['entries'][0]['updatedAt']),
            $propertyGroup->getUpdatedAt()
        );

        $names = $propertyGroup->getNames();

        $this->assertCount(1, $names);
        $name = $names[0];

        $this->assertSame('My package', $name->getName());
        $this->assertSame('en', $name->getLang());
        $this->assertSame(1, $name->getPropertyGroupId());
        $this->assertSame('', $name->getDescription());
        $this->assertEqualsCanonicalizing($responseData['entries'][0]['names'][0], $name->getData());
    }
}
