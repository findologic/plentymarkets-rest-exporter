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
            'languageList' => ['de', 'en'],
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

        $configuration = $webStore->getConfiguration();
        $this->assertEquals($expectedConfiguration, $configuration->getData());

        $this->assertEquals('Standard Shop', $configuration->getName());
        $this->assertEquals('de', $configuration->getDefaultLanguage());
        $this->assertEquals('callisto_light_3_5', $configuration->getDefaultLayout());
        $this->assertEquals(1, $configuration->getDefaultShippingCountryId());
        $this->assertEquals(0, $configuration->getDisplayPriceColumn());
        $this->assertEquals(0, $configuration->getDisplayPriceNetto());
        $this->assertEquals(50, $configuration->getDoctype());
        $this->assertEquals('', $configuration->getFaviconPath());
        $this->assertEquals(11, $configuration->getFrontPageContentPageId());
        $this->assertEquals(4, $configuration->getMaxLoginAttempts());
        $this->assertEquals(11, $configuration->getError404ContentPageId());
        $this->assertEquals(3, $configuration->getCancellationRightsContentPageId());
        $this->assertEquals(5, $configuration->getHelpContentPageId());
        $this->assertEquals(0, $configuration->getItemNotFoundContentPageId());
        $this->assertEquals(6, $configuration->getPrivacyPolicyContentPageId());
        $this->assertEquals(12, $configuration->getShippingContentPageId());
        $this->assertEquals(4, $configuration->getTermsConditionsContentPageId());
        $this->assertEquals(0, $configuration->getSocialMedia());
        $this->assertEquals('http://www.facebook.com/plentyMarkets', $configuration->getUrlFacebook());
        $this->assertEquals('', $configuration->getUrlGooglePlus());
        $this->assertEquals('https://twitter.com/#!/plentymarkets', $configuration->getUrlTwitter());
        $this->assertEquals(0, $configuration->getDisplayItemOnly4Customer());
        $this->assertEquals(1, $configuration->getDisplayItemName());
        $this->assertEquals(1, $configuration->getAttributeVariantCheck());
        $this->assertEquals(1, $configuration->getAttributeSelectDefaultOption());
        $this->assertEquals('', $configuration->getUrlItemCategory());
        $this->assertEquals('', $configuration->getUrlItemContent());
        $this->assertEquals('', $configuration->getUrlTitleItemName());
        $this->assertEquals('name_cat', $configuration->getUrlTitleItemContent());
        $this->assertEquals('-', $configuration->getUrlNeedle());
        $this->assertEquals('/', $configuration->getUrlFileExtension());
        $this->assertEquals('absolute', $configuration->getUrlLinking());
        $this->assertEquals(['de', 'en'], $configuration->getLanguageList());
        $this->assertEquals('http://plenty-test.com', $configuration->getDomain());
        $this->assertEquals('https://plenty-test.com', $configuration->getDomainSsl());
        $this->assertEquals('/var/www7/m6742/1234d095/', $configuration->getRootDir());
        $this->assertEquals(0, $configuration->getLoginMode());
        $this->assertEquals(1, $configuration->getAttributesDropDown());
        $this->assertEquals(1, $configuration->getAttributeWithMarkup());
        $this->assertEquals(0, $configuration->getCategoryItemCount());
        $this->assertEquals(3, $configuration->getCategoryLevelLimit());
        $this->assertEquals(1, $configuration->getCurrencyConversion());
        $this->assertEquals(1, $configuration->getDontSplitItemBundle());
        $this->assertEquals(0, $configuration->getDhlPackstationValidation());
        $this->assertEquals(0, $configuration->getSessionLifetime());
        $this->assertEquals(0, $configuration->getUseCharacterCrossSelling());
        $this->assertEquals(0, $configuration->getUseDynamicCrossSelling());
        $this->assertEquals('EUR', $configuration->getDefaultCurrency());
        $this->assertEquals(2, $configuration->getLanguageMode());
        $this->assertEquals(1, $configuration->getItemCategorySorting1());
        $this->assertEquals(1, $configuration->getItemCategorySorting2());
        $this->assertEquals(0, $configuration->getItemSortByMonthlySales());
        $this->assertEquals('', $configuration->getItemAvailabilityDisabledList());
        $this->assertEquals('', $configuration->getItemMeasureUnit());
        $this->assertEquals(0, $configuration->getShowBasePriceActive());
        $this->assertEquals(0, $configuration->getJumpPaymentActive());
        $this->assertEquals(0, $configuration->getJumpShippingActive());
        $this->assertEquals(0, $configuration->getShowContentTermsFsk());
        $this->assertEquals(0, $configuration->getNewsletterRegistrationActive());
        $this->assertEquals(0, $configuration->getMinimumOrderValue());
        $this->assertEquals(1, $configuration->getIpAddressSaveInactive());
        $this->assertEquals(0, $configuration->getReuseOrderActive());
        $this->assertEquals(0, $configuration->getEditOrderActive());
        $this->assertEquals(0, $configuration->getCurrencySymbol());
        $this->assertEquals(1, $configuration->getDhlAllowPackstationActive());
        $this->assertEquals(0, $configuration->getDhlLimitOrderAmountForPackstation());
        $this->assertEquals(1, $configuration->getWatchlistActive());
        $this->assertEquals(1, $configuration->getItemwishlistActive());
        $this->assertEquals(1, $configuration->getCouponVisibilityActive());
        $this->assertEquals(2, $configuration->getItemlistPrice());
        $this->assertEquals(1, $configuration->getItemlistWeight());
        $this->assertEquals(0, $configuration->getSchedulerActive());
        $this->assertEquals(1, $configuration->getChangeEmailActive());
        $this->assertEquals(1, $configuration->getChangePasswordActive());
        $this->assertEquals(0, $configuration->getChangePasswordSendmail());
        $this->assertEquals(0, $configuration->getLogoutHiddenActive());
        $this->assertEquals(0, $configuration->getDisplayStatusInactive());
        $this->assertEquals(0, $configuration->getDisplayWeightInactive());
        $this->assertEquals(1, $configuration->getDisplayInvoiceDownload());
        $this->assertEquals(1, $configuration->getDisplayShippingDateActive());
        $this->assertEquals(90, $configuration->getQuickloginValidDays());
        $this->assertEquals(14, $configuration->getPaymentMethodsContentPageId());
        $this->assertEquals(13, $configuration->getContactContentPageId());
        $this->assertEquals(10, $configuration->getLegalDisclosureContentPageId());
        $this->assertEquals(2, $configuration->getBankContentPageId());
        $this->assertEquals(
            [
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
            $configuration->getBrowserLanguage()
        );
        $this->assertEquals(0, $configuration->getWebstoreId());
        $this->assertEquals('es-facets', $configuration->getItemSearchEngine());
        $this->assertEquals(5, $configuration->getItemMaxRatingPoints());
        $this->assertEquals(0, $configuration->getItemCommentsActive());
        $this->assertEquals(0, $configuration->getCustomerLoginMethod());
        $this->assertEquals(0, $configuration->getDocumentsActive());
        $this->assertEquals(0, $configuration->getDynamicExportActive());
        $this->assertEquals(0, $configuration->getRetoureMethod());
        $this->assertEquals(0, $configuration->getEditSchedulerPaymentMethodActive());
        $this->assertEquals(0, $configuration->getShowSEPAMandateDownload());
        $this->assertEquals(
            [
                'de' => 1,
                'en' => 0
            ],
            $configuration->getDefaultShippingCountryList()
        );
        $this->assertEquals(0, $configuration->getUseDefaultShippingCountryAsShopCountry());
        $this->assertEquals(101, $configuration->getDefaultParcelServiceId());
        $this->assertEquals(6, $configuration->getDefaultParcelServicePresetId());
        $this->assertEquals(0, $configuration->getDefaultMethodOfPaymentId());
        $this->assertEquals(0, $configuration->getIgnoreCouponMinOrderValueActive());
        $this->assertEquals(0, $configuration->getExternalVatCheckInactive());
        $this->assertEquals(0, $configuration->getCustomerRegistrationCheck());
        $this->assertEquals(1, $configuration->getDefaultAccountingLocation());
        $this->assertEquals('', $configuration->getEbayAccount());
        $this->assertEquals(0, $configuration->getItemRatingActive());
        $this->assertEquals('active', $configuration->getItemNewFeedbackVisibility());
        $this->assertEquals(1, $configuration->getItemCustomerNameVisibility());
        $this->assertEquals(0, $configuration->getCategoryRatingActive());
        $this->assertEquals(5, $configuration->getCategoryMaxRatingPoints());
        $this->assertEquals(0, $configuration->getCategoryCommentsActive());
        $this->assertEquals('active', $configuration->getCategoryNewFeedbackVisibility());
        $this->assertEquals(1, $configuration->getCategoryCustomerNameVisibility());
        $this->assertEquals(0, $configuration->getBlogRatingActive());
        $this->assertEquals(5, $configuration->getBlogMaxRatingPoints());
        $this->assertEquals(0, $configuration->getBlogCommentsActive());
        $this->assertEquals('active', $configuration->getBlogNewFeedbackVisibility());
        $this->assertEquals(1, $configuration->getBlogCustomerNameVisibility());
        $this->assertEquals(0, $configuration->getFeedbackRatingActive());
        $this->assertEquals(5, $configuration->getFeedbackMaxRatingPoints());
        $this->assertEquals(0, $configuration->getFeedbackCommentsActive());
        $this->assertEquals('active', $configuration->getFeedbackNewFeedbackVisibility());
        $this->assertEquals(1, $configuration->getFeedbackCustomerNameVisibility());
        $this->assertEquals(0, $configuration->getUrlTrailingSlash());
    }
}
