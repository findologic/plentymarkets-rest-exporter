<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\UnitParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Unit\Name;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class UnitResponseTest extends TestCase
{
    use ResponseHelper;

    private $response;

    private $unitResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('UnitResponse/response.json');
        $this->unitResponse = UnitParser::parse($this->response);
    }

    public function criteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'unitOfMeasurement' => 'KGM'
                ],
                'expectedId' => 2
            ],
            'criteria with sub-criteria' => [
                'criteria' => [
                    'names' => [
                        'name' => 'Kilogramm'
                    ]
                ],
                'expectedId' => 2
            ],
            'criteria with multiple sub-criteria' => [
                'criteria' => [
                    'names' => [
                        'lang' => 'en',
                        'name' => 'gram'
                    ]
                ],
                'expectedId' => 3
            ],
            'simple criteria with multiple sub-criteria' => [
                'criteria' => [
                    'unitOfMeasurement' => 'MGM',
                    'names' => [
                        'lang' => 'en',
                        'name' => 'milligram'
                    ]
                ],
                'expectedId' => 4
            ]
        ];
    }

    /**
     * @dataProvider criteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $unit = $this->unitResponse->findOne($criteria);

        $this->assertEquals($expectedId, $unit->getId());
    }

    public function testGetAllReturnsCorrectNumberOfItems()
    {
        self::assertCount(4, $this->unitResponse->all());
    }

    public function testFindReturnsCorrectNumberOfItems()
    {
        $criteria = [
            'isDecimalPlacesAllowed' => true
        ];

        self::assertCount(2, $this->unitResponse->find($criteria));
    }

    public function testDataCanBeFetched(): void
    {
        $responseData = json_decode((string)$this->response->getBody(), true);
        $unit = $this->unitResponse->first();

        $this->assertEqualsCanonicalizing($responseData['entries'][0], $unit->getData());

        $this->assertEquals(1, $unit->getId());
        $this->assertEquals(1, $unit->getPosition());
        $this->assertEquals('C62', $unit->getUnitOfMeasurement());
        $this->assertEquals(false, $unit->isDecimalPlacesAllowed());
        $this->assertEquals('2016-09-05T12:24:57+01:00', $unit->getUpdatedAt());
        $this->assertEquals('2016-09-05T12:24:57+01:00', $unit->getCreatedAt());

        $names = $unit->getNames();
        $this->assertCount(2, $names);
        /** @var Name $name */
        $name = reset($names);
        $this->assertEquals(1, $name->getUnitId());
        $this->assertEquals('de', $name->getLang());
        $this->assertEquals('Stück', $name->getName());
    }
}
