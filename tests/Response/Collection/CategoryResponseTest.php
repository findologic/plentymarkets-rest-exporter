<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use DateTime;
use FINDOLOGIC\PlentyMarketsRestExporter\Parser\CategoryParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\CategoryResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Category\CategoryDetails;
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
                        'plentyId' => 12345,
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

    private function buildCategoryDetails(array $overrides = []): array
    {
        return array_merge([
            'categoryId' => 420,
            'lang' => 'de',
            'name' => 'BAM',
            'description' => '',
            'description2' => '',
            'shortDescription' => '',
            'metaKeywords' => '',
            'nameUrl' => 'BAM',
            'metaTitle' => '',
            'metaDescription' => '',
            'position' => 0,
            'updatedAt' => '2017-07-07T10:21:27+02:00',
            'updatedBy' => 'Jane Doe',
            'itemListView' => 'ItemViewCategoriesList',
            'singleItemView' => 'ItemViewSingleItem',
            'pageView' => 'pageDesignContent',
            'fullText' => 'N',
            'metaRobots' => 'ALL',
            'canonicalLink' => '',
            'previewUrl' => 'https://plenty-demo.com/BAM/',
            'image' => null,
            'imagePath' => null,
            'image2' => null,
            'image2Path' => null,
            'plentyId' => 46
        ], $overrides);
    }

    public function testCollectionDataCanBeFetched(): void
    {
        $expectedPage = 1;
        $expectedTotalsCount = 3;
        $expectedIsLastPage = true;
        $expectedFirstCategory = new Category([
            'id' => 420,
            'parentCategoryId' => null,
            'level' => 1,
            'type' => 'item',
            'linklist' => 'N',
            'right' => 'all',
            'sitemap' => 'N',
            'hasChildren' => true,
            'details' => [$this->buildCategoryDetails()]
        ]);

        $expectedSecondCategory = new Category([
            'id' => 421,
            'parentCategoryId' => 420,
            'level' => 2,
            'type' => 'item',
            'linklist' => 'N',
            'right' => 'all',
            'sitemap' => 'N',
            'hasChildren' => false,
            'details' => [
                $this->buildCategoryDetails([
                    'categoryId' => 421,
                    'name' => 'subcategory of BAM',
                    'nameUrl' => 'BAM/subcategory-of-BAM',
                    'previewUrl' => 'https://plenty-demo.com/BAM/subcategory-of-BAM/',
                ])
            ]
        ]);

        $expectedThirdCategory = new Category([
            'id' => 422,
            'parentCategoryId' => null,
            'level' => 1,
            'type' => 'item',
            'linklist' => 'N',
            'right' => 'all',
            'sitemap' => 'N',
            'hasChildren' => false,
            'details' => [
                $this->buildCategoryDetails([
                    'categoryId' => 422,
                    'name' => 'nice',
                    'nameUrl' => 'nice',
                    'previewUrl' => 'https://plenty-demo.com/nice/',
                ])
            ]
        ]);

        $expectedCategories = [
            $expectedFirstCategory,
            $expectedSecondCategory,
            $expectedThirdCategory
        ];

        $expectedLastPageNumber = 1;
        $expectedFirstOnPage = 1;
        $expectedLastOnPage = 1;
        $expectedItemsPerPage = 100;

        $categoryResponse = new CategoryResponse(
            $expectedPage,
            $expectedTotalsCount,
            $expectedIsLastPage,
            $expectedCategories,
            $expectedLastPageNumber,
            $expectedFirstOnPage,
            $expectedLastOnPage,
            $expectedItemsPerPage
        );

        $this->assertSame($expectedFirstCategory, $categoryResponse->first());
        $this->assertSame($expectedCategories, $categoryResponse->all());
        $this->assertSame($expectedPage, $categoryResponse->getPage());
        $this->assertSame($expectedTotalsCount, $categoryResponse->getTotalsCount());
        $this->assertSame($expectedIsLastPage, $categoryResponse->isLastPage());
        $this->assertSame($expectedLastPageNumber, $categoryResponse->getLastPageNumber());
        $this->assertSame($expectedFirstOnPage, $categoryResponse->getFirstOnPage());
        $this->assertSame($expectedLastOnPage, $categoryResponse->getLastOnPage());
        $this->assertSame($expectedItemsPerPage, $categoryResponse->getItemsPerPage());
    }

    public function testCategoryDataCanBeFetched(): void
    {
        $expectedId = 422;
        $expectedParentCategoryId = null;
        $expectedLevel = 1;
        $expectedType = 'item';
        $expectedLinkList = 'N';
        $expectedRight = 'all';
        $expectedSiteMap = 'N';
        $expectedHasChildren = false;
        $expectedDetails = [$this->buildCategoryDetails()];

        $expectedCategoryData = [
            'id' => $expectedId,
            'parentCategoryId' => $expectedParentCategoryId,
            'level' => $expectedLevel,
            'type' => $expectedType,
            'linklist' => $expectedLinkList,
            'right' => $expectedRight,
            'sitemap' => $expectedSiteMap,
            'hasChildren' => $expectedHasChildren,
            'details' => $expectedDetails
        ];

        $category = new Category($expectedCategoryData);

        // The lang should get upper cased and updatedAt is converted to a DateTime object.
        $expectedCategoryData['details'][0]['lang'] = 'DE';
        $expectedCategoryData['details'][0]['updatedAt'] = new DateTime(
            $expectedCategoryData['details'][0]['updatedAt']
        );

        $this->assertEquals($expectedCategoryData, $category->getData());
        $this->assertSame($expectedId, $category->getId());
        $this->assertSame($expectedParentCategoryId, $category->getParentCategoryId());
        $this->assertSame($expectedLevel, $category->getLevel());
        $this->assertSame($expectedType, $category->getType());
        $this->assertSame($expectedLinkList, $category->getLinklist());
        $this->assertSame($expectedRight, $category->getRight());
        $this->assertSame($expectedSiteMap, $category->getSitemap());
        $this->assertSame($expectedHasChildren, $category->hasChildren());
        $this->assertCount(1, $category->getDetails());

        $this->assertEquals([new CategoryDetails($expectedDetails[0])], $category->getDetails());
    }

    public function testCategoryDetailsCanBeFetched(): void
    {
        $expectedCategoryId = 420;
        $expectedLang = 'DE';
        $expectedName = 'cool';
        $expectedDescription = '';
        $expectedDescription2 = '';
        $expectedShortDescription = '';
        $expectedMetaKeywords = '';
        $expectedNameUrl = 'cool';
        $expectedMetaTitle = '';
        $expectedMetaDescription = '';
        $expectedPosition = 0;
        $expectedUpdatedAt = new DateTime('2017-07-07T10:21:27+02:00');
        $expectedUpdatedBy = 'Dane Joe';
        $expectedItemListView = 'ItemViewCategoriesList';
        $expectedSingleItemView = 'ItemViewSingleItem';
        $expectedPageView = 'pageDesignContent';
        $expectedFullText = 'N';
        $expectedMetaRobots = 'ALL';
        $expectedCanonicalLink = '';
        $expectedPreviewUrl = 'https://plenty-demo.com/cool/';
        $expectedImage = null;
        $expectedImagePath = null;
        $expectedImage2 = null;
        $expectedImage2Path = null;
        $expectedPlentyId = 82;

        $expectedCategoryDetails = [
            'categoryId' => $expectedCategoryId,
            'lang' => $expectedLang,
            'name' => $expectedName,
            'description' => $expectedDescription,
            'description2' => $expectedDescription2,
            'shortDescription' => $expectedShortDescription,
            'metaKeywords' => $expectedMetaKeywords,
            'nameUrl' => $expectedNameUrl,
            'metaTitle' => $expectedMetaTitle,
            'metaDescription' => $expectedMetaDescription,
            'position' => (string)$expectedPosition, // Is returned as string from the API.
            'updatedAt' => $expectedUpdatedAt->format(DATE_ATOM), // Is returned as DateTime ATOM string from the API.
            'updatedBy' => $expectedUpdatedBy,
            'itemListView' => $expectedItemListView,
            'singleItemView' => $expectedSingleItemView,
            'pageView' => $expectedPageView,
            'fullText' => $expectedFullText,
            'metaRobots' => $expectedMetaRobots,
            'canonicalLink' => $expectedCanonicalLink,
            'previewUrl' => $expectedPreviewUrl,
            'image' => $expectedImage,
            'imagePath' => $expectedImagePath,
            'image2' => $expectedImage2,
            'image2Path' => $expectedImage2Path,
            'plentyId' => $expectedPlentyId
        ];

        $categoryDetails = new CategoryDetails($expectedCategoryDetails);

        // The getter returns a DateTime object.
        $expectedCategoryDetails['updatedAt'] = $expectedUpdatedAt;

        $this->assertEquals($expectedCategoryDetails, $categoryDetails->getData());
        $this->assertSame($expectedCategoryId, $categoryDetails->getCategoryId());
        $this->assertSame($expectedLang, $categoryDetails->getLang());
        $this->assertSame($expectedName, $categoryDetails->getName());
        $this->assertSame($expectedDescription, $categoryDetails->getDescription());
        $this->assertSame($expectedDescription2, $categoryDetails->getDescription2());
        $this->assertSame($expectedShortDescription, $categoryDetails->getShortDescription());
        $this->assertSame($expectedMetaKeywords, $categoryDetails->getMetaKeywords());
        $this->assertSame($expectedNameUrl, $categoryDetails->getNameUrl());
        $this->assertSame($expectedMetaTitle, $categoryDetails->getMetaTitle());
        $this->assertSame($expectedMetaDescription, $categoryDetails->getMetaDescription());
        $this->assertSame($expectedPosition, $categoryDetails->getPosition());
        $this->assertEquals($expectedUpdatedAt, $categoryDetails->getUpdatedAt()); // Not the same object.
        $this->assertSame($expectedUpdatedBy, $categoryDetails->getUpdatedBy());
        $this->assertSame($expectedItemListView, $categoryDetails->getItemListView());
        $this->assertSame($expectedSingleItemView, $categoryDetails->getSingleItemView());
        $this->assertSame($expectedPageView, $categoryDetails->getPageView());
        $this->assertSame($expectedFullText, $categoryDetails->getFullText());
        $this->assertSame($expectedMetaRobots, $categoryDetails->getMetaRobots());
        $this->assertSame($expectedCanonicalLink, $categoryDetails->getCanonicalLink());
        $this->assertSame($expectedPreviewUrl, $categoryDetails->getPreviewUrl());
        $this->assertSame($expectedImage, $categoryDetails->getImage());
        $this->assertSame($expectedImagePath, $categoryDetails->getImagePath());
        $this->assertSame($expectedImage2, $categoryDetails->getImage2());
        $this->assertSame($expectedImage2Path, $categoryDetails->getImage2Path());
        $this->assertSame($expectedPlentyId, $categoryDetails->getPlentyId());
    }
}
