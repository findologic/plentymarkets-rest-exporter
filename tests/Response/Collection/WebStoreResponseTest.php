<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\WebStoreParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class WebStoreResponseTest extends TestCase
{
    use ResponseHelper;

    private function getDefaultWebStoreConfiguration(): array
    {
        return [
            'name' => 'Standard Shop',
            'defaultLanguage' => 'de',
            'defaultLayout' => 'callisto_light_3_5',
            'defaultShippingCountryId' => 1,
            'displayPriceColumn' => 0,
            'displayPriceNetto' => 0,
            'doctype' => 50,
            'faviconPath' => '',
            'frontPageContentPageId' => 11,
            'maxLoginAttempts' => 4,
            'error404ContentPageId' => 11,
            'cancellationRightsContentPageId' => 3,
            'helpContentPageId' => 5,
            'itemNotFoundContentPageId' => 0,
            'privacyPolicyContentPageId' => 6,
            'shippingContentPageId' => 12,
            'termsConditionsContentPageId' => 4,
            'socialMedia' => 0,
            'urlFacebook' => 'http://www.facebook.com/plentyMarkets',
            'urlGooglePlus' => '',
            'urlTwitter' => 'https://twitter.com/#!/plentymarkets',
            'displayItemOnly4Customer' => 0,
            'displayItemName' => 1,
            'attributeVariantCheck' => 1,
            'attributeSelectDefaultOption' => 1,
            'urlItemCategory' => '',
            'urlItemContent' => '',
            'urlTitleItemName' => '',
            'urlTitleItemContent' => 'name_cat',
            'urlNeedle' => '-',
            'urlFileExtension' => '/',
            'urlLinking' => 'absolute',
            'languageList' => 'de, en',
            'domain' => 'http://plenty-test.com',
            'domainSsl' => 'https://plenty-test.com',
            'rootDir' => '/var/www7/m6742/1234d095/',
            'loginMode' => 0,
            'attributesDropDown' => 1,
            'attributeWithMarkup' => 1,
            'categoryItemCount' => 0,
            'categoryLevelLimit' => 3,
            'currencyConversion' => 1,
            'dontSplitItemBundle' => 1,
            'dhlPackstationValidation' => 0,
            'sessionLifetime' => 0,
            'useCharacterCrossSelling' => 0,
            'useDynamicCrossSelling' => 0,
            'defaultCurrency' => 'EUR',
            'languageMode' => 2,
            'itemCategorySorting1' => 1,
            'itemCategorySorting2' => 1,
            'itemSortByMonthlySales' => 0,
            'itemAvailabilityDisabledList' => '',
            'itemMeasureUnit' => '',
            'showBasePriceActive' => 0,
            'jumpPaymentActive' => 0,
            'jumpShippingActive' => 0,
            'showContentTermsFsk' => 0,
            'newsletterRegistrationActive' => 0,
            'minimumOrderValue' => 0,
            'ipAddressSaveInactive' => 1,
            'reuseOrderActive' => 0,
            'editOrderActive' => 0,
            'currencySymbol' => 0,
            'dhlAllowPackstationActive' => 1,
            'dhlLimitOrderAmountForPackstation' => 0,
            'watchlistActive' => 1,
            'itemwishlistActive' => 1,
            'couponVisibilityActive' => 1,
            'itemlistPrice' => 2,
            'itemlistWeight' => 1,
            'schedulerActive' => 0,
            'changeEmailActive' => 1,
            'changePasswordActive' => 1,
            'changePasswordSendmail' => 0,
            'logoutHiddenActive' => 0,
            'displayStatusInactive' => 0,
            'displayWeightInactive' => 0,
            'displayInvoiceDownload' => 1,
            'displayShippingDateActive' => 1,
            'quickloginValidDays' => 90,
            'paymentMethodsContentPageId' => 14,
            'contactContentPageId' => 13,
            'legalDisclosureContentPageId' => 10,
            'bankContentPageId' => 2,
            'browserLanguage' => [
                'other' => 'de',
                'de' => 'de',
                'en' => 'en',
                'bg' => '',
                'fr' => '',
                'it' => '',
                'es' => '',
                'tr' => '',
                'nl' => '',
                'pl' => '',
                'pt' => '',
                'nn' => '',
                'ro' => '',
                'da' => '',
                'se' => '',
                'cz' => '',
                'ru' => '',
                'sk' => '',
                'cn' => '',
                'vn' => '',
            ],
            'webstoreId' => 0,
            'itemSearchEngine' => 'es-facets',
            'itemMaxRatingPoints' => 5,
            'itemCommentsActive' => 0,
            'customerLoginMethod' => 0,
            'documentsActive' => 0,
            'dynamicExportActive' => 0,
            'retoureMethod' => 0,
            'editSchedulerPaymentMethodActive' => 0,
            'showSEPAMandateDownload' => 0,
            'defaultShippingCountryList' => [
                'de' => 1,
                'en' => 0
            ],
            'useDefaultShippingCountryAsShopCountry' => 0,
            'defaultParcelServiceId' => 101,
            'defaultParcelServicePresetId' => 6,
            'defaultMethodOfPaymentId' => 0,
            'ignoreCouponMinOrderValueActive' => 0,
            'externalVatCheckInactive' => 0,
            'customerRegistrationCheck' => 0,
            'defaultAccountingLocation' => 1,
            'ebayAccount' => '',
            'itemRatingActive' => 0,
            'itemNewFeedbackVisibility' => 'active',
            'itemCustomerNameVisibility' => 1,
            'categoryRatingActive' => 0,
            'categoryMaxRatingPoints' => 5,
            'categoryCommentsActive' => 0,
            'categoryNewFeedbackVisibility' => 'active',
            'categoryCustomerNameVisibility' => 1,
            'blogRatingActive' => 0,
            'blogMaxRatingPoints' => 5,
            'blogCommentsActive' => 0,
            'blogNewFeedbackVisibility' => 'active',
            'blogCustomerNameVisibility' => 1,
            'feedbackRatingActive' => 0,
            'feedbackMaxRatingPoints' => 5,
            'feedbackCommentsActive' => 0,
            'feedbackNewFeedbackVisibility' => 'active',
            'feedbackCustomerNameVisibility' => 1,
            'urlTrailingSlash' => 0
        ];
    }

    public function testWebStoreResponseCanBeFetched(): void
    {
        $expectedId = 0;
        $expectedType = 'plentymarkets';
        $expectedStoreIdentifier = 1234;
        $expectedName = 'Standard Shop';
        $expectedPluginSetId = 7;
        $expectedConfiguration = $this->getDefaultWebStoreConfiguration();

        $expectedWebStore = new WebStore([
            'id' => $expectedId,
            'type' => $expectedType,
            'storeIdentifier' => $expectedStoreIdentifier,
            'name' => $expectedName,
            'pluginSetId' => $expectedPluginSetId,
            'configuration' => $expectedConfiguration
        ]);

        $webStoreResponse = WebStoreParser::parse($this->getMockResponse('WebStoreResponse/response.json'));
        $webStore = $webStoreResponse->first();

        $this->assertCount(1, $webStoreResponse->getWebStores());
        $this->assertCount(1, $webStoreResponse->all());
        $this->assertSame($webStoreResponse->getWebStores(), $webStoreResponse->all());
        $this->assertEquals($expectedWebStore, $webStore);
        $this->assertEquals($expectedWebStore, $webStoreResponse->getWebStoreByStoreIdentifier(1234));
        $this->assertEquals($expectedWebStore, $webStoreResponse->find([])[0]);
        $this->assertEquals($expectedWebStore, $webStoreResponse->findOne([]));

        $this->assertSame($expectedId, $webStore->getId());
        $this->assertSame($expectedType, $webStore->getType());
        $this->assertSame($expectedStoreIdentifier, $webStore->getStoreIdentifier());
        $this->assertSame($expectedName, $webStore->getName());
        $this->assertSame($expectedPluginSetId, $webStore->getPluginSetId());
        $this->assertEquals($expectedConfiguration, $webStore->getConfiguration());
    }
}
