<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class CategoryResponseTest extends TestCase
{
    use ResponseHelper;

    public function categoryCriteriaProvider(): array
    {
        return [
            'simple criteria' => [
                'criteria' => [
                    'id' => 16
                ],
                'expectedId' => 16
            ],
            'criteria with sub-criteria' => [
                'criteria' => [
                    'details' => [
                        'name' => 'dfbd'
                    ]
                ],
                'expectedId' => 16
            ],
            'criteria with multiple sub-criteria' => [
                'criteria' => [
                    'details' => [
                        'name' => 'dfbd',
                        'description' => '',
                        'updatedBy' => 'Max Mustermann'
                    ]
                ],
                'expectedId' => 16
            ],
            'simple criteria with multiple sub-criteria' => [
                'criteria' => [
                    'hasChildren' => false,
                    'details' => [
                        'categoryId' => 370,
                        'name' => 'Registrierung',
                        'updatedBy' => 'Max Musterfrau'
                    ]
                ],
                'expectedId' => 370
            ],
            'complex criteria with multiple sub-criteria' => [
                'criteria' => [
                    'details' => [
                        'lang' => 'DE',
                        'name' => 'Sofas'
                    ]
                ],
                'expectedId' => 18
            ],
            'complex criteria with many sub-criteria' => [
                'criteria' => [
                    'details' => [
                        'updatedBy' => 'Max Musterfrau',
                        'image' => null,
                        'plentyId' => 34185,
                        'name' => 'Bestellvorgang',
                        'nameUrl' => 'bestellvorgang'
                    ]
                ],
                'expectedId' => 381
            ],
        ];
    }

    /**
     * @dataProvider categoryCriteriaProvider
     */
    public function testCriteriaSearchWorksAsExpected(array $criteria, int $expectedId): void
    {
        $response = $this->getMockResponse('CategoryResponse/response.json');

        $categoryResponse = CategoryParser::parse($response);
        $category = $categoryResponse->findOne($criteria);

        $this->assertEquals($expectedId, $category->getId());
    }
}
