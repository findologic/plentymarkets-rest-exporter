<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\WebStore;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;

class Configuration extends Entity
{
    private ?string $name;

    private ?string $defaultLanguage;

    private ?string $defaultLayout;

    private ?int $defaultShippingCountryId;

    private ?int $displayPriceColumn;

    private ?int $displayPriceNetto;

    private ?int $doctype;

    private ?string $faviconPath;

    private ?int $frontPageContentPageId;

    private ?int $maxLoginAttempts;

    private ?int $error404ContentPageId;

    private ?int $cancellationRightsContentPageId;

    private ?int $helpContentPageId;

    private ?int $itemNotFoundContentPageId;

    private ?int $privacyPolicyContentPageId;

    private ?int $shippingContentPageId;

    private ?int $termsConditionsContentPageId;

    private ?int $socialMedia;

    private ?string $urlFacebook;

    private ?string $urlGooglePlus;

    private ?string $urlTwitter;

    private ?int $displayItemOnly4Customer;

    private ?int $displayItemName;

    private ?int $attributeVariantCheck;

    private ?int $attributeSelectDefaultOption;

    private ?string $urlItemCategory;

    private ?string $urlItemContent;

    private ?string $urlTitleItemName;

    private ?string $urlTitleItemContent;

    private ?string $urlNeedle;

    private ?string $urlFileExtension;

    private ?string $urlLinking;

    /** @var string[] */
    private array $languageList;

    private ?string $domain;

    private ?string $domainSsl;

    private ?string $rootDir;

    private ?string $loginMode;

    private ?int $attributesDropDown;

    private ?int $attributeWithMarkup;

    private ?int $categoryItemCount;

    private ?int $categoryLevelLimit;

    private ?int $currencyConversion;

    private ?int $dontSplitItemBundle;

    private ?int $dhlPackstationValidation;

    private ?int $sessionLifetime;

    private ?int $useCharacterCrossSelling;

    private ?int $useDynamicCrossSelling;

    private ?string $defaultCurrency;

    private ?int $languageMode;

    private ?int $itemCategorySorting1;

    private ?int $itemCategorySorting2;

    private ?int $itemSortByMonthlySales;

    private ?string $itemAvailabilityDisabledList;

    /** @var string|ItemMeasureUnit[]|null */
    private mixed $itemMeasureUnit;

    private ?int $showBasePriceActive;

    private ?int $jumpPaymentActive;

    private ?int $jumpShippingActive;

    private ?int $showContentTermsFsk;

    private ?int $newsletterRegistrationActive;

    private ?int $minimumOrderValue;

    private ?int $ipAddressSaveInactive;

    private ?int $reuseOrderActive;

    private ?int $editOrderActive;

    private ?int $currencySymbol;

    private ?int $dhlAllowPackstationActive;

    private ?int $dhlLimitOrderAmountForPackstation;

    private ?int $watchlistActive;

    private ?int $itemwishlistActive;

    private ?int $couponVisibilityActive;

    private ?int $itemlistPrice;

    private ?int $itemlistWeight;

    private ?int $schedulerActive;

    private ?int $changeEmailActive;

    private ?int $changePasswordActive;

    private ?int $changePasswordSendmail;

    private ?int $logoutHiddenActive;

    private ?int $displayStatusInactive;

    private ?int $displayWeightInactive;

    private ?int $displayInvoiceDownload;

    private ?int $displayShippingDateActive;

    private ?int $quickloginValidDays;

    private ?int $paymentMethodsContentPageId;

    private ?int $contactContentPageId;

    private ?int $legalDisclosureContentPageId;

    private ?int $bankContentPageId;

    private array $browserLanguage;

    private ?int $webstoreId;

    private ?string $itemSearchEngine;

    private ?int $itemMaxRatingPoints;

    private ?int $itemCommentsActive;

    private ?int $customerLoginMethod;

    private ?int $documentsActive;

    private ?int $dynamicExportActive;

    private ?int $retoureMethod;

    private ?int $editSchedulerPaymentMethodActive;

    private ?int $showSEPAMandateDownload;

    private array $defaultShippingCountryList;

    private ?int $useDefaultShippingCountryAsShopCountry;

    private ?int $defaultParcelServiceId;

    private ?int $defaultParcelServicePresetId;

    private ?int $defaultMethodOfPaymentId;

    private ?int $ignoreCouponMinOrderValueActive;

    private ?int $externalVatCheckInactive;

    private ?int $customerRegistrationCheck;

    private ?int $defaultAccountingLocation;

    private ?array $ebayAccount;

    private ?int $itemRatingActive;

    private ?string $itemNewFeedbackVisibility;

    private ?int $itemCustomerNameVisibility;

    private ?int $categoryRatingActive;

    private ?int $categoryMaxRatingPoints;

    private ?int $categoryCommentsActive;

    private ?string $categoryNewFeedbackVisibility;

    private ?int $categoryCustomerNameVisibility;

    private ?int $blogRatingActive;

    private ?int $blogMaxRatingPoints;

    private ?int $blogCommentsActive;

    private ?string $blogNewFeedbackVisibility;

    private ?int $blogCustomerNameVisibility;

    private ?int $feedbackRatingActive;

    private ?int $feedbackMaxRatingPoints;

    private ?int $feedbackCommentsActive;

    private ?string $feedbackNewFeedbackVisibility;

    private ?int $feedbackCustomerNameVisibility;

    private ?int $urlTrailingSlash;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->name = $this->getStringProperty('name', $data);
        $this->defaultLanguage = $this->getStringProperty('defaultLanguage', $data);
        $this->defaultLayout = $this->getStringProperty('defaultLayout', $data);
        $this->defaultShippingCountryId = $this->getIntProperty('defaultShippingCountryId', $data);
        $this->displayPriceColumn = $this->getIntProperty('displayPriceColumn', $data);
        $this->displayPriceNetto = $this->getIntProperty('displayPriceNetto', $data);
        $this->doctype = $this->getIntProperty('doctype', $data);
        $this->faviconPath = $this->getStringProperty('faviconPath', $data);
        $this->frontPageContentPageId = $this->getIntProperty('frontPageContentPageId', $data);
        $this->maxLoginAttempts = $this->getIntProperty('maxLoginAttempts', $data);
        $this->error404ContentPageId = $this->getIntProperty('error404ContentPageId', $data);
        $this->cancellationRightsContentPageId = $this->getIntProperty('cancellationRightsContentPageId', $data);
        $this->helpContentPageId = $this->getIntProperty('helpContentPageId', $data);
        $this->itemNotFoundContentPageId = $this->getIntProperty('itemNotFoundContentPageId', $data);
        $this->privacyPolicyContentPageId = $this->getIntProperty('privacyPolicyContentPageId', $data);
        $this->shippingContentPageId = $this->getIntProperty('shippingContentPageId', $data);
        $this->termsConditionsContentPageId = $this->getIntProperty('termsConditionsContentPageId', $data);
        $this->socialMedia = $this->getIntProperty('socialMedia', $data);
        $this->urlFacebook = $this->getStringProperty('urlFacebook', $data);
        $this->urlGooglePlus = $this->getStringProperty('urlGooglePlus', $data);
        $this->urlTwitter = $this->getStringProperty('urlTwitter', $data);
        $this->displayItemOnly4Customer = $this->getIntProperty('displayItemOnly4Customer', $data);
        $this->displayItemName = $this->getIntProperty('displayItemName', $data, 1);
        $this->attributeVariantCheck = $this->getIntProperty('attributeVariantCheck', $data);
        $this->attributeSelectDefaultOption = $this->getIntProperty('attributeSelectDefaultOption', $data);
        $this->urlItemCategory = $this->getStringProperty('urlItemCategory', $data);
        $this->urlItemContent = $this->getStringProperty('urlItemContent', $data);
        $this->urlTitleItemName = $this->getStringProperty('urlTitleItemName', $data);
        $this->urlTitleItemContent = $this->getStringProperty('urlTitleItemContent', $data);
        $this->urlNeedle = $this->getStringProperty('urlNeedle', $data);
        $this->urlFileExtension = $this->getStringProperty('urlFileExtension', $data);
        $this->urlLinking = $this->getStringProperty('urlLinking', $data);

        $languageList = $data['languageList'] ?? [];
        if (!is_array($languageList)) {
            $languageList = explode(',', $data['languageList']);
            $languageList = array_map('trim', $languageList);
        }
        $this->languageList = $languageList;

        $this->domain = $this->getStringProperty('domain', $data);
        $this->domainSsl = $this->getStringProperty('domainSsl', $data);
        $this->rootDir = $this->getStringProperty('rootDir', $data);
        $this->loginMode = $this->getStringProperty('loginMode', $data);
        $this->attributesDropDown = $this->getIntProperty('attributesDropDown', $data);
        $this->attributeWithMarkup = $this->getIntProperty('attributeWithMarkup', $data);
        $this->categoryItemCount = $this->getIntProperty('categoryItemCount', $data);
        $this->categoryLevelLimit = $this->getIntProperty('categoryLevelLimit', $data);
        $this->currencyConversion = $this->getIntProperty('currencyConversion', $data);
        $this->dontSplitItemBundle = $this->getIntProperty('dontSplitItemBundle', $data);
        $this->dhlPackstationValidation = $this->getIntProperty('dhlPackstationValidation', $data);
        $this->sessionLifetime = $this->getIntProperty('sessionLifetime', $data);
        $this->useCharacterCrossSelling = $this->getIntProperty('useCharacterCrossSelling', $data);
        $this->useDynamicCrossSelling = $this->getIntProperty('useDynamicCrossSelling', $data);
        $this->defaultCurrency = $this->getStringProperty('defaultCurrency', $data);
        $this->languageMode = $this->getIntProperty('languageMode', $data);
        $this->itemCategorySorting1 = $this->getIntProperty('itemCategorySorting1', $data);
        $this->itemCategorySorting2 = $this->getIntProperty('itemCategorySorting2', $data);
        $this->itemSortByMonthlySales = $this->getIntProperty('itemSortByMonthlySales', $data);
        $this->itemAvailabilityDisabledList = $this->getStringProperty('itemAvailabilityDisabledList', $data);
        $this->itemMeasureUnit = $this->fetchItemMeasureUnit($data);
        $this->showBasePriceActive = $this->getIntProperty('showBasePriceActive', $data);
        $this->jumpPaymentActive = $this->getIntProperty('jumpPaymentActive', $data);
        $this->jumpShippingActive = $this->getIntProperty('jumpShippingActive', $data);
        $this->showContentTermsFsk = $this->getIntProperty('showContentTermsFsk', $data);
        $this->newsletterRegistrationActive = $this->getIntProperty('newsletterRegistrationActive', $data);
        $this->minimumOrderValue = $this->getIntProperty('minimumOrderValue', $data);
        $this->ipAddressSaveInactive = $this->getIntProperty('ipAddressSaveInactive', $data);
        $this->reuseOrderActive = $this->getIntProperty('reuseOrderActive', $data);
        $this->editOrderActive = $this->getIntProperty('editOrderActive', $data);
        $this->currencySymbol = $this->getIntProperty('currencySymbol', $data);
        $this->dhlAllowPackstationActive = $this->getIntProperty('dhlAllowPackstationActive', $data);
        $this->dhlLimitOrderAmountForPackstation = $this->getIntProperty('dhlLimitOrderAmountForPackstation', $data);
        $this->watchlistActive = $this->getIntProperty('watchlistActive', $data);
        $this->itemwishlistActive = $this->getIntProperty('itemwishlistActive', $data);
        $this->couponVisibilityActive = $this->getIntProperty('couponVisibilityActive', $data);
        $this->itemlistPrice = $this->getIntProperty('itemlistPrice', $data);
        $this->itemlistWeight = $this->getIntProperty('itemlistWeight', $data);
        $this->schedulerActive = $this->getIntProperty('schedulerActive', $data);
        $this->changeEmailActive = $this->getIntProperty('changeEmailActive', $data);
        $this->changePasswordActive = $this->getIntProperty('changePasswordActive', $data);
        $this->changePasswordSendmail = $this->getIntProperty('changePasswordSendmail', $data);
        $this->logoutHiddenActive = $this->getIntProperty('logoutHiddenActive', $data);
        $this->displayStatusInactive = $this->getIntProperty('displayStatusInactive', $data);
        $this->displayWeightInactive = $this->getIntProperty('displayWeightInactive', $data);
        $this->displayInvoiceDownload = $this->getIntProperty('displayInvoiceDownload', $data);
        $this->displayShippingDateActive = $this->getIntProperty('displayShippingDateActive', $data);
        $this->quickloginValidDays = $this->getIntProperty('quickloginValidDays', $data);
        $this->paymentMethodsContentPageId = $this->getIntProperty('paymentMethodsContentPageId', $data);
        $this->contactContentPageId = $this->getIntProperty('contactContentPageId', $data);
        $this->legalDisclosureContentPageId = $this->getIntProperty('legalDisclosureContentPageId', $data);
        $this->bankContentPageId = $this->getIntProperty('bankContentPageId', $data);
        $this->browserLanguage = $data['browserLanguage'] ?? [];
        $this->webstoreId = $this->getIntProperty('webstoreId', $data);
        $this->itemSearchEngine = $this->getStringProperty('itemSearchEngine', $data);
        $this->itemMaxRatingPoints = $this->getIntProperty('itemMaxRatingPoints', $data);
        $this->itemCommentsActive = $this->getIntProperty('itemCommentsActive', $data);
        $this->customerLoginMethod = $this->getIntProperty('customerLoginMethod', $data);
        $this->documentsActive = $this->getIntProperty('documentsActive', $data);
        $this->dynamicExportActive = $this->getIntProperty('dynamicExportActive', $data);
        $this->retoureMethod = $this->getIntProperty('retoureMethod', $data);
        $this->editSchedulerPaymentMethodActive = $this->getIntProperty('editSchedulerPaymentMethodActive', $data);
        $this->showSEPAMandateDownload = $this->getIntProperty('showSEPAMandateDownload', $data);
        $this->defaultShippingCountryList = $this->getArrayProperty('defaultShippingCountryList', $data, []);
        $this->useDefaultShippingCountryAsShopCountry = $this->getIntProperty(
            'useDefaultShippingCountryAsShopCountry',
            $data
        );
        $this->defaultParcelServiceId = $this->getIntProperty('defaultParcelServiceId', $data);
        $this->defaultParcelServicePresetId = $this->getIntProperty('defaultParcelServicePresetId', $data);
        $this->defaultMethodOfPaymentId = $this->getIntProperty('defaultMethodOfPaymentId', $data);
        $this->ignoreCouponMinOrderValueActive = $this->getIntProperty('ignoreCouponMinOrderValueActive', $data);
        $this->externalVatCheckInactive = $this->getIntProperty('externalVatCheckInactive', $data);
        $this->customerRegistrationCheck = $this->getIntProperty('customerRegistrationCheck', $data);
        $this->defaultAccountingLocation = $this->getIntProperty('defaultAccountingLocation', $data);
        $this->ebayAccount = $this->getArrayProperty('ebayAccount', $data, []);
        $this->itemRatingActive = $this->getIntProperty('itemRatingActive', $data);
        $this->itemNewFeedbackVisibility = $this->getStringProperty('itemNewFeedbackVisibility', $data);
        $this->itemCustomerNameVisibility = $this->getIntProperty('itemCustomerNameVisibility', $data);
        $this->categoryRatingActive = $this->getIntProperty('categoryRatingActive', $data);
        $this->categoryMaxRatingPoints = $this->getIntProperty('categoryMaxRatingPoints', $data);
        $this->categoryCommentsActive = $this->getIntProperty('categoryCommentsActive', $data);
        $this->categoryNewFeedbackVisibility = $this->getStringProperty('categoryNewFeedbackVisibility', $data);
        $this->categoryCustomerNameVisibility = $this->getIntProperty('categoryCustomerNameVisibility', $data);
        $this->blogRatingActive = $this->getIntProperty('blogRatingActive', $data);
        $this->blogMaxRatingPoints = $this->getIntProperty('blogMaxRatingPoints', $data);
        $this->blogCommentsActive = $this->getIntProperty('blogCommentsActive', $data);
        $this->blogNewFeedbackVisibility = $this->getStringProperty('blogNewFeedbackVisibility', $data);
        $this->blogCustomerNameVisibility = $this->getIntProperty('blogCustomerNameVisibility', $data);
        $this->feedbackRatingActive = $this->getIntProperty('feedbackRatingActive', $data);
        $this->feedbackMaxRatingPoints = $this->getIntProperty('feedbackMaxRatingPoints', $data);
        $this->feedbackCommentsActive = $this->getIntProperty('feedbackCommentsActive', $data);
        $this->feedbackNewFeedbackVisibility = $this->getStringProperty('feedbackNewFeedbackVisibility', $data);
        $this->feedbackCustomerNameVisibility = $this->getIntProperty('feedbackCustomerNameVisibility', $data);
        $this->urlTrailingSlash = $this->getIntProperty('urlTrailingSlash', $data);
    }

    public function getData(): array
    {
        return [
            'name' => $this->name,
            'defaultLanguage' => $this->defaultLanguage,
            'defaultLayout' => $this->defaultLayout,
            'defaultShippingCountryId' => $this->defaultShippingCountryId,
            'displayPriceColumn' => $this->displayPriceColumn,
            'displayPriceNetto' => $this->displayPriceNetto,
            'doctype' => $this->doctype,
            'faviconPath' => $this->faviconPath,
            'frontPageContentPageId' => $this->frontPageContentPageId,
            'maxLoginAttempts' => $this->maxLoginAttempts,
            'error404ContentPageId' => $this->error404ContentPageId,
            'cancellationRightsContentPageId' => $this->cancellationRightsContentPageId,
            'helpContentPageId' => $this->helpContentPageId,
            'itemNotFoundContentPageId' => $this->itemNotFoundContentPageId,
            'privacyPolicyContentPageId' => $this->privacyPolicyContentPageId,
            'shippingContentPageId' => $this->shippingContentPageId,
            'termsConditionsContentPageId' => $this->termsConditionsContentPageId,
            'socialMedia' => $this->socialMedia,
            'urlFacebook' => $this->urlFacebook,
            'urlGooglePlus' => $this->urlGooglePlus,
            'urlTwitter' => $this->urlTwitter,
            'displayItemOnly4Customer' => $this->displayItemOnly4Customer,
            'displayItemName' => $this->displayItemName,
            'attributeVariantCheck' => $this->attributeVariantCheck,
            'attributeSelectDefaultOption' => $this->attributeSelectDefaultOption,
            'urlItemCategory' => $this->urlItemCategory,
            'urlItemContent' => $this->urlItemContent,
            'urlTitleItemName' => $this->urlTitleItemName,
            'urlTitleItemContent' => $this->urlTitleItemContent,
            'urlNeedle' => $this->urlNeedle,
            'urlFileExtension' => $this->urlFileExtension,
            'urlLinking' => $this->urlLinking,
            'languageList' => $this->languageList,
            'domain' => $this->domain,
            'domainSsl' => $this->domainSsl,
            'rootDir' => $this->rootDir,
            'loginMode' => $this->loginMode,
            'attributesDropDown' => $this->attributesDropDown,
            'attributeWithMarkup' => $this->attributeWithMarkup,
            'categoryItemCount' => $this->categoryItemCount,
            'categoryLevelLimit' => $this->categoryLevelLimit,
            'currencyConversion' => $this->currencyConversion,
            'dontSplitItemBundle' => $this->dontSplitItemBundle,
            'dhlPackstationValidation' => $this->dhlPackstationValidation,
            'sessionLifetime' => $this->sessionLifetime,
            'useCharacterCrossSelling' => $this->useCharacterCrossSelling,
            'useDynamicCrossSelling' => $this->useDynamicCrossSelling,
            'defaultCurrency' => $this->defaultCurrency,
            'languageMode' => $this->languageMode,
            'itemCategorySorting1' => $this->itemCategorySorting1,
            'itemCategorySorting2' => $this->itemCategorySorting2,
            'itemSortByMonthlySales' => $this->itemSortByMonthlySales,
            'itemAvailabilityDisabledList' => $this->itemAvailabilityDisabledList,
            'itemMeasureUnit' => $this->itemMeasureUnit,
            'showBasePriceActive' => $this->showBasePriceActive,
            'jumpPaymentActive' => $this->jumpPaymentActive,
            'jumpShippingActive' => $this->jumpShippingActive,
            'showContentTermsFsk' => $this->showContentTermsFsk,
            'newsletterRegistrationActive' => $this->newsletterRegistrationActive,
            'minimumOrderValue' => $this->minimumOrderValue,
            'ipAddressSaveInactive' => $this->ipAddressSaveInactive,
            'reuseOrderActive' => $this->reuseOrderActive,
            'editOrderActive' => $this->editOrderActive,
            'currencySymbol' => $this->currencySymbol,
            'dhlAllowPackstationActive' => $this->dhlAllowPackstationActive,
            'dhlLimitOrderAmountForPackstation' => $this->dhlLimitOrderAmountForPackstation,
            'watchlistActive' => $this->watchlistActive,
            'itemwishlistActive' => $this->itemwishlistActive,
            'couponVisibilityActive' => $this->couponVisibilityActive,
            'itemlistPrice' => $this->itemlistPrice,
            'itemlistWeight' => $this->itemlistWeight,
            'schedulerActive' => $this->schedulerActive,
            'changeEmailActive' => $this->changeEmailActive,
            'changePasswordActive' => $this->changePasswordActive,
            'changePasswordSendmail' => $this->changePasswordSendmail,
            'logoutHiddenActive' => $this->logoutHiddenActive,
            'displayStatusInactive' => $this->displayStatusInactive,
            'displayWeightInactive' => $this->displayWeightInactive,
            'displayInvoiceDownload' => $this->displayInvoiceDownload,
            'displayShippingDateActive' => $this->displayShippingDateActive,
            'quickloginValidDays' => $this->quickloginValidDays,
            'paymentMethodsContentPageId' => $this->paymentMethodsContentPageId,
            'contactContentPageId' => $this->contactContentPageId,
            'legalDisclosureContentPageId' => $this->legalDisclosureContentPageId,
            'bankContentPageId' => $this->bankContentPageId,
            'browserLanguage' => $this->browserLanguage,
            'webstoreId' => $this->webstoreId,
            'itemSearchEngine' => $this->itemSearchEngine,
            'itemMaxRatingPoints' => $this->itemMaxRatingPoints,
            'itemCommentsActive' => $this->itemCommentsActive,
            'customerLoginMethod' => $this->customerLoginMethod,
            'documentsActive' => $this->documentsActive,
            'dynamicExportActive' => $this->dynamicExportActive,
            'retoureMethod' => $this->retoureMethod,
            'editSchedulerPaymentMethodActive' => $this->editSchedulerPaymentMethodActive,
            'showSEPAMandateDownload' => $this->showSEPAMandateDownload,
            'defaultShippingCountryList' => $this->defaultShippingCountryList,
            'useDefaultShippingCountryAsShopCountry' => $this->useDefaultShippingCountryAsShopCountry,
            'defaultParcelServiceId' => $this->defaultParcelServiceId,
            'defaultParcelServicePresetId' => $this->defaultParcelServicePresetId,
            'defaultMethodOfPaymentId' => $this->defaultMethodOfPaymentId,
            'ignoreCouponMinOrderValueActive' => $this->ignoreCouponMinOrderValueActive,
            'externalVatCheckInactive' => $this->externalVatCheckInactive,
            'customerRegistrationCheck' => $this->customerRegistrationCheck,
            'defaultAccountingLocation' => $this->defaultAccountingLocation,
            'ebayAccount' => $this->ebayAccount,
            'itemRatingActive' => $this->itemRatingActive,
            'itemNewFeedbackVisibility' => $this->itemNewFeedbackVisibility,
            'itemCustomerNameVisibility' => $this->itemCustomerNameVisibility,
            'categoryRatingActive' => $this->categoryRatingActive,
            'categoryMaxRatingPoints' => $this->categoryMaxRatingPoints,
            'categoryCommentsActive' => $this->categoryCommentsActive,
            'categoryNewFeedbackVisibility' => $this->categoryNewFeedbackVisibility,
            'categoryCustomerNameVisibility' => $this->categoryCustomerNameVisibility,
            'blogRatingActive' => $this->blogRatingActive,
            'blogMaxRatingPoints' => $this->blogMaxRatingPoints,
            'blogCommentsActive' => $this->blogCommentsActive,
            'blogNewFeedbackVisibility' => $this->blogNewFeedbackVisibility,
            'blogCustomerNameVisibility' => $this->blogCustomerNameVisibility,
            'feedbackRatingActive' => $this->feedbackRatingActive,
            'feedbackMaxRatingPoints' => $this->feedbackMaxRatingPoints,
            'feedbackCommentsActive' => $this->feedbackCommentsActive,
            'feedbackNewFeedbackVisibility' => $this->feedbackNewFeedbackVisibility,
            'feedbackCustomerNameVisibility' => $this->feedbackCustomerNameVisibility,
            'urlTrailingSlash' => $this->urlTrailingSlash
        ];
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDefaultLanguage(): ?string
    {
        return $this->defaultLanguage;
    }

    public function getDefaultLayout(): ?string
    {
        return $this->defaultLayout;
    }

    public function getDefaultShippingCountryId(): ?int
    {
        return $this->defaultShippingCountryId;
    }

    public function getDisplayPriceColumn(): ?int
    {
        return $this->displayPriceColumn;
    }

    public function getDisplayPriceNetto(): ?int
    {
        return $this->displayPriceNetto;
    }

    public function getDoctype(): ?int
    {
        return $this->doctype;
    }

    public function getFaviconPath(): ?string
    {
        return $this->faviconPath;
    }

    public function getFrontPageContentPageId(): ?int
    {
        return $this->frontPageContentPageId;
    }

    public function getMaxLoginAttempts(): ?int
    {
        return $this->maxLoginAttempts;
    }

    public function getError404ContentPageId(): ?int
    {
        return $this->error404ContentPageId;
    }

    public function getCancellationRightsContentPageId(): ?int
    {
        return $this->cancellationRightsContentPageId;
    }

    public function getHelpContentPageId(): ?int
    {
        return $this->helpContentPageId;
    }

    public function getItemNotFoundContentPageId(): ?int
    {
        return $this->itemNotFoundContentPageId;
    }

    public function getPrivacyPolicyContentPageId(): ?int
    {
        return $this->privacyPolicyContentPageId;
    }

    public function getShippingContentPageId(): ?int
    {
        return $this->shippingContentPageId;
    }

    public function getTermsConditionsContentPageId(): ?int
    {
        return $this->termsConditionsContentPageId;
    }

    public function getSocialMedia(): ?int
    {
        return $this->socialMedia;
    }

    public function getUrlFacebook(): ?string
    {
        return $this->urlFacebook;
    }

    public function getUrlGooglePlus(): ?string
    {
        return $this->urlGooglePlus;
    }

    public function getUrlTwitter(): ?string
    {
        return $this->urlTwitter;
    }

    public function getDisplayItemOnly4Customer(): ?int
    {
        return $this->displayItemOnly4Customer;
    }

    public function getDisplayItemName(): ?int
    {
        return $this->displayItemName;
    }

    public function getAttributeVariantCheck(): ?int
    {
        return $this->attributeVariantCheck;
    }

    public function getAttributeSelectDefaultOption(): ?int
    {
        return $this->attributeSelectDefaultOption;
    }

    public function getUrlItemCategory(): ?string
    {
        return $this->urlItemCategory;
    }

    public function getUrlItemContent(): ?string
    {
        return $this->urlItemContent;
    }

    public function getUrlTitleItemName(): ?string
    {
        return $this->urlTitleItemName;
    }

    public function getUrlTitleItemContent(): ?string
    {
        return $this->urlTitleItemContent;
    }

    public function getUrlNeedle(): ?string
    {
        return $this->urlNeedle;
    }

    public function getUrlFileExtension(): ?string
    {
        return $this->urlFileExtension;
    }

    public function getUrlLinking(): ?string
    {
        return $this->urlLinking;
    }

    /**
     * @return string[]
     */
    public function getLanguageList(): array
    {
        return $this->languageList;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getDomainSsl(): ?string
    {
        return $this->domainSsl;
    }

    public function getRootDir(): ?string
    {
        return $this->rootDir;
    }

    public function getLoginMode(): ?string
    {
        return $this->loginMode;
    }

    public function getAttributesDropDown(): ?int
    {
        return $this->attributesDropDown;
    }

    public function getAttributeWithMarkup(): ?int
    {
        return $this->attributeWithMarkup;
    }

    public function getCategoryItemCount(): ?int
    {
        return $this->categoryItemCount;
    }

    public function getCategoryLevelLimit(): ?int
    {
        return $this->categoryLevelLimit;
    }

    public function getCurrencyConversion(): ?int
    {
        return $this->currencyConversion;
    }

    public function getDontSplitItemBundle(): ?int
    {
        return $this->dontSplitItemBundle;
    }

    public function getDhlPackstationValidation(): ?int
    {
        return $this->dhlPackstationValidation;
    }

    public function getSessionLifetime(): ?int
    {
        return $this->sessionLifetime;
    }

    public function getUseCharacterCrossSelling(): ?int
    {
        return $this->useCharacterCrossSelling;
    }

    public function getUseDynamicCrossSelling(): ?int
    {
        return $this->useDynamicCrossSelling;
    }

    public function getDefaultCurrency(): ?string
    {
        return $this->defaultCurrency;
    }

    public function getLanguageMode(): ?int
    {
        return $this->languageMode;
    }

    public function getItemCategorySorting1(): ?int
    {
        return $this->itemCategorySorting1;
    }

    public function getItemCategorySorting2(): ?int
    {
        return $this->itemCategorySorting2;
    }

    public function getItemSortByMonthlySales(): ?int
    {
        return $this->itemSortByMonthlySales;
    }

    public function getItemAvailabilityDisabledList(): ?string
    {
        return $this->itemAvailabilityDisabledList;
    }

    /**
     * @return string|ItemMeasureUnit[]|null
     */
    public function getItemMeasureUnit(): array|string|null
    {
        return $this->itemMeasureUnit;
    }

    public function getShowBasePriceActive(): ?int
    {
        return $this->showBasePriceActive;
    }

    public function getJumpPaymentActive(): ?int
    {
        return $this->jumpPaymentActive;
    }

    public function getJumpShippingActive(): ?int
    {
        return $this->jumpShippingActive;
    }

    public function getShowContentTermsFsk(): ?int
    {
        return $this->showContentTermsFsk;
    }

    public function getNewsletterRegistrationActive(): ?int
    {
        return $this->newsletterRegistrationActive;
    }

    public function getMinimumOrderValue(): ?int
    {
        return $this->minimumOrderValue;
    }

    public function getIpAddressSaveInactive(): ?int
    {
        return $this->ipAddressSaveInactive;
    }

    public function getReuseOrderActive(): ?int
    {
        return $this->reuseOrderActive;
    }

    public function getEditOrderActive(): ?int
    {
        return $this->editOrderActive;
    }

    public function getCurrencySymbol(): ?int
    {
        return $this->currencySymbol;
    }

    public function getDhlAllowPackstationActive(): ?int
    {
        return $this->dhlAllowPackstationActive;
    }

    public function getDhlLimitOrderAmountForPackstation(): ?int
    {
        return $this->dhlLimitOrderAmountForPackstation;
    }

    public function getWatchlistActive(): ?int
    {
        return $this->watchlistActive;
    }

    public function getItemwishlistActive(): ?int
    {
        return $this->itemwishlistActive;
    }

    public function getCouponVisibilityActive(): ?int
    {
        return $this->couponVisibilityActive;
    }

    public function getItemlistPrice(): ?int
    {
        return $this->itemlistPrice;
    }

    public function getItemlistWeight(): ?int
    {
        return $this->itemlistWeight;
    }

    public function getSchedulerActive(): ?int
    {
        return $this->schedulerActive;
    }

    public function getChangeEmailActive(): ?int
    {
        return $this->changeEmailActive;
    }

    public function getChangePasswordActive(): ?int
    {
        return $this->changePasswordActive;
    }

    public function getChangePasswordSendmail(): ?int
    {
        return $this->changePasswordSendmail;
    }

    public function getLogoutHiddenActive(): ?int
    {
        return $this->logoutHiddenActive;
    }

    public function getDisplayStatusInactive(): ?int
    {
        return $this->displayStatusInactive;
    }

    public function getDisplayWeightInactive(): ?int
    {
        return $this->displayWeightInactive;
    }

    public function getDisplayInvoiceDownload(): ?int
    {
        return $this->displayInvoiceDownload;
    }

    public function getDisplayShippingDateActive(): ?int
    {
        return $this->displayShippingDateActive;
    }

    public function getQuickloginValidDays(): ?int
    {
        return $this->quickloginValidDays;
    }

    public function getPaymentMethodsContentPageId(): ?int
    {
        return $this->paymentMethodsContentPageId;
    }

    public function getContactContentPageId(): ?int
    {
        return $this->contactContentPageId;
    }

    public function getLegalDisclosureContentPageId(): ?int
    {
        return $this->legalDisclosureContentPageId;
    }

    public function getBankContentPageId(): ?int
    {
        return $this->bankContentPageId;
    }

    public function getBrowserLanguage(): array
    {
        return $this->browserLanguage;
    }

    public function getWebstoreId(): ?int
    {
        return $this->webstoreId;
    }

    public function getItemSearchEngine(): ?string
    {
        return $this->itemSearchEngine;
    }

    public function getItemMaxRatingPoints(): ?int
    {
        return $this->itemMaxRatingPoints;
    }

    public function getItemCommentsActive(): ?int
    {
        return $this->itemCommentsActive;
    }

    public function getCustomerLoginMethod(): ?int
    {
        return $this->customerLoginMethod;
    }

    public function getDocumentsActive(): ?int
    {
        return $this->documentsActive;
    }

    public function getDynamicExportActive(): ?int
    {
        return $this->dynamicExportActive;
    }

    public function getRetoureMethod(): ?int
    {
        return $this->retoureMethod;
    }

    public function getEditSchedulerPaymentMethodActive(): ?int
    {
        return $this->editSchedulerPaymentMethodActive;
    }

    public function getShowSEPAMandateDownload(): ?int
    {
        return $this->showSEPAMandateDownload;
    }

    public function getDefaultShippingCountryList(): array
    {
        return $this->defaultShippingCountryList;
    }

    public function getUseDefaultShippingCountryAsShopCountry(): ?int
    {
        return $this->useDefaultShippingCountryAsShopCountry;
    }

    public function getDefaultParcelServiceId(): ?int
    {
        return $this->defaultParcelServiceId;
    }

    public function getDefaultParcelServicePresetId(): ?int
    {
        return $this->defaultParcelServicePresetId;
    }

    public function getDefaultMethodOfPaymentId(): ?int
    {
        return $this->defaultMethodOfPaymentId;
    }

    public function getIgnoreCouponMinOrderValueActive(): ?int
    {
        return $this->ignoreCouponMinOrderValueActive;
    }

    public function getExternalVatCheckInactive(): ?int
    {
        return $this->externalVatCheckInactive;
    }

    public function getCustomerRegistrationCheck(): ?int
    {
        return $this->customerRegistrationCheck;
    }

    public function getDefaultAccountingLocation(): ?int
    {
        return $this->defaultAccountingLocation;
    }

    public function getEbayAccount(): ?array
    {
        return $this->ebayAccount;
    }

    public function getItemRatingActive(): ?int
    {
        return $this->itemRatingActive;
    }

    public function getItemNewFeedbackVisibility(): ?string
    {
        return $this->itemNewFeedbackVisibility;
    }

    public function getItemCustomerNameVisibility(): ?int
    {
        return $this->itemCustomerNameVisibility;
    }

    public function getCategoryRatingActive(): ?int
    {
        return $this->categoryRatingActive;
    }

    public function getCategoryMaxRatingPoints(): ?int
    {
        return $this->categoryMaxRatingPoints;
    }

    public function getCategoryCommentsActive(): ?int
    {
        return $this->categoryCommentsActive;
    }

    public function getCategoryNewFeedbackVisibility(): ?string
    {
        return $this->categoryNewFeedbackVisibility;
    }

    public function getCategoryCustomerNameVisibility(): ?int
    {
        return $this->categoryCustomerNameVisibility;
    }

    public function getBlogRatingActive(): ?int
    {
        return $this->blogRatingActive;
    }

    public function getBlogMaxRatingPoints(): ?int
    {
        return $this->blogMaxRatingPoints;
    }

    public function getBlogCommentsActive(): ?int
    {
        return $this->blogCommentsActive;
    }

    public function getBlogNewFeedbackVisibility(): ?string
    {
        return $this->blogNewFeedbackVisibility;
    }

    public function getBlogCustomerNameVisibility(): ?int
    {
        return $this->blogCustomerNameVisibility;
    }

    public function getFeedbackRatingActive(): ?int
    {
        return $this->feedbackRatingActive;
    }

    public function getFeedbackMaxRatingPoints(): ?int
    {
        return $this->feedbackMaxRatingPoints;
    }

    public function getFeedbackCommentsActive(): ?int
    {
        return $this->feedbackCommentsActive;
    }

    public function getFeedbackNewFeedbackVisibility(): ?string
    {
        return $this->feedbackNewFeedbackVisibility;
    }

    public function getFeedbackCustomerNameVisibility(): ?int
    {
        return $this->feedbackCustomerNameVisibility;
    }

    public function getUrlTrailingSlash(): ?int
    {
        return $this->urlTrailingSlash;
    }

    /**
     * @return ItemMeasureUnit[]|string|null
     */
    protected function fetchItemMeasureUnit(array $data): array|string|null
    {
        $itemMeasureUnitData = $data['itemMeasureUnit'] ?? null;

        $measureUnit = null;
        if (is_string($itemMeasureUnitData)) {
            $measureUnit = $this->getStringProperty('itemMeasureUnit', $data);
        } elseif (is_array($itemMeasureUnitData)) {
            /** @var ItemMeasureUnit[] $measureUnit */
            $measureUnit =  $this->getEntities(ItemMeasureUnit::class, 'itemMeasureUnit', $data);
        }

        return $measureUnit;
    }
}
