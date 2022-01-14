<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin\Container;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin\DataProvider;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin\PluginSetEntry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin\Repository;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin\UpdateInformation;

class Plugin extends Entity
{
    private ?int $id;
    private ?string $name;
    private ?string $last_build_production;
    private ?string $last_build_stage;
    private ?string $created_at;
    private ?string $updated_at;
    private ?string $position;
    private ?bool $activeProductive;
    private ?bool $activeStage;
    private ?bool $inStage;
    private ?bool $inProductive;
    private ?string $type;
    private ?string $version;
    private ?string $description;
    private ?string $namespace;
    private ?string $author;
    private array $keywords;
    private array $require;
    private array $runOnBuild;
    private array $checkOnBuild;
    private ?string $authorIcon;
    private ?string $pluginIcon;
    private ?bool $isConnectedWithGit;
    private array $dependencies;
    private array $javaScriptFiles;
    /** @var Container[] */
    private array $containers;
    /** @var DataProvider[] */
    private array $dataProviders;
    private ?string $source;
    private ?UpdateInformation $updateInformation;
    private ?bool $isClosedSource;
    private ?string $license;
    private array $shortDescription;
    private array $categories;
    private ?float $price;
    private ?string $email;
    private ?string $phone;
    private array $marketplaceName;
    private array $subscriptionInformation;
    private ?string $versionStage;
    private ?string $versionProductive;
    private array $marketplaceVariations;
    private ?string $webhookUrl;
    private ?bool $isExternalTool;
    private array $directDownloadLinks;
    private ?string $forwardLink;
    private array $notInstalledRequirements;
    private array $notActiveStageRequirements;
    private array $notActiveProductiveRequirements;
    private array $pluginSetIds;
    private ?bool $installed;
    private ?string $branch;
    private ?string $commit;
    /** @var PluginSetEntry[] */
    private array $pluginSetEntries;
    private Repository $repository;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = $this->getIntProperty('id', $data);
        $this->name = $this->getStringProperty('name', $data);
        $this->last_build_production = $this->getStringProperty('last_build_production', $data);
        $this->last_build_stage = $this->getStringProperty('last_build_stage', $data);
        $this->created_at = $this->getStringProperty('created_at', $data);
        $this->updated_at = $this->getStringProperty('updated_at', $data);
        $this->position = $this->getStringProperty('position', $data);
        $this->activeProductive = $this->getBoolProperty('activeProductive', $data);
        $this->activeStage = $this->getBoolProperty('activeStage', $data);
        $this->inStage = $this->getBoolProperty('inStage', $data);
        $this->inProductive = $this->getBoolProperty('inProductive', $data);
        $this->type = $this->getStringProperty('type', $data);
        $this->version = $this->getStringProperty('version', $data);
        $this->description = $this->getStringProperty('description', $data);
        $this->namespace = $this->getStringProperty('namespace', $data);
        $this->author = $this->getStringProperty('author', $data);
        $this->keywords = $data['keywords'] ?? [];
        $this->require = $data['require'] ?? [];
        $this->runOnBuild = $data['runOnBuild'] ?? [];
        $this->checkOnBuild = $data['checkOnBuild'] ?? [];
        $this->authorIcon = $this->getStringProperty('authorIcon', $data);
        $this->pluginIcon = $this->getStringProperty('pluginIcon', $data);
        $this->isConnectedWithGit = $this->getBoolProperty('isConnectedWithGit', $data);
        $this->dependencies = $data['dependencies'] ?? [];
        $this->javaScriptFiles = $data['javaScriptFiles'] ?? [];
        $this->source = $this->getStringProperty('source', $data);
        $this->isClosedSource = $this->getBoolProperty('isClosedSource', $data);
        $this->license = $this->getStringProperty('license', $data);
        $this->shortDescription = $data['shortDescription'] ?? [];
        $this->categories = $data['categories'] ?? [];
        $this->price = $this->getFloatProperty('price', $data);
        $this->email = $this->getStringProperty('email', $data);
        $this->phone = $this->getStringProperty('phone', $data);
        $this->marketplaceName = $data['marketplaceName'] ?? [];
        $this->subscriptionInformation = $data['subscriptionInformation'] ?? [];
        $this->versionStage = $this->getStringProperty('versionStage', $data);
        $this->versionProductive = $this->getStringProperty('versionProductive', $data);
        $this->marketplaceVariations = $data['marketplaceVariations'] ?? [];
        $this->webhookUrl = $this->getStringProperty('webhookUrl', $data);
        $this->isExternalTool = $this->getBoolProperty('isExternalTool', $data);
        $this->directDownloadLinks = $data['directDownloadLinks'] ?? [];
        $this->forwardLink = $this->getStringProperty('forwardLink', $data);
        $this->notInstalledRequirements = $data['notInstalledRequirements'] ?? [];
        $this->notActiveStageRequirements = $data['notActiveStageRequirements'] ?? [];
        $this->notActiveProductiveRequirements = $data['notActiveProductiveRequirements'] ?? [];
        $this->pluginSetIds = $data['pluginSetIds'] ?? [];
        $this->installed = $this->getBoolProperty('installed', $data);
        $this->branch = $this->getStringProperty('branch', $data);
        $this->commit = $this->getStringProperty('commit', $data);
        $this->containers = $this->getEntities(Container::class, 'containers', $data);
        $this->dataProviders = $this->getEntities(DataProvider::class, 'dataProviders', $data);
        if (isset($data['updateInformation'])) {
            $this->updateInformation = $this->getEntity(UpdateInformation::class, $data['updateInformation']);
        }
        $this->pluginSetEntries = $this->getEntities(PluginSetEntry::class, 'pluginSetEntries', $data);
        if (isset($data['repository'])) {
            $this->repository = $this->getEntity(Repository::class, $data['repository']);
        }
    }

    public function getData(): array
    {
        $containers = [];
        foreach ($this->containers as $container) {
            $containers[] = $container->getData();
        }

        $dataProviders = [];
        foreach ($this->dataProviders as $dataProvider) {
            $dataProviders[] = $dataProvider->getData();
        }

        $pluginSetEntries = [];
        foreach ($this->pluginSetEntries as $pluginSetEntry) {
            $pluginSetEntries[] = $pluginSetEntry->getData();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_build_production' => $this->last_build_production,
            'last_build_stage' => $this->last_build_stage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'position' => $this->position,
            'activeProductive' => $this->activeProductive,
            'activeStage' => $this->activeStage,
            'inStage' => $this->inStage,
            'inProductive' => $this->inProductive,
            'type' => $this->type,
            'version' => $this->version,
            'description' => $this->description,
            'namespace' => $this->namespace,
            'author' => $this->author,
            'keywords' => $this->keywords,
            'require' => $this->require,
            'runOnBuild' => $this->runOnBuild,
            'checkOnBuild' => $this->checkOnBuild,
            'authorIcon' => $this->authorIcon,
            'pluginIcon' => $this->pluginIcon,
            'isConnectedWithGit' => $this->isConnectedWithGit,
            'dependencies' => $this->dependencies,
            'javaScriptFiles' => $this->javaScriptFiles,
            'source' => $this->source,
            'isClosedSource' => $this->isClosedSource,
            'license' => $this->license,
            'shortDescription' => $this->shortDescription,
            'categories' => $this->categories,
            'price' => $this->price,
            'email' => $this->email,
            'phone' => $this->phone,
            'marketplaceName' => $this->marketplaceName,
            'subscriptionInformation' => $this->subscriptionInformation,
            'versionStage' => $this->versionStage,
            'versionProductive' => $this->versionProductive,
            'marketplaceVariations' => $this->marketplaceVariations,
            'webhookUrl' => $this->webhookUrl,
            'isExternalTool' => $this->isExternalTool,
            'directDownloadLinks' => $this->directDownloadLinks,
            'forwardLink' => $this->forwardLink,
            'notInstalledRequirements' => $this->notInstalledRequirements,
            'notActiveStageRequirements' => $this->notActiveStageRequirements,
            'notActiveProductiveRequirements' => $this->notActiveProductiveRequirements,
            'pluginSetIds' => $this->pluginSetIds,
            'installed' => $this->installed,
            'branch' => $this->branch,
            'commit' => $this->commit,
            'updateInformation' => $this->updateInformation ? $this->updateInformation->getData() : null,
            'repository' => $this->repository ? $this->repository->getData() : null,
            'containers' => $containers,
            'dataProviders' => $dataProviders,
            'pluginSetEntries' => $pluginSetEntries
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLastBuildProduction(): ?string
    {
        return $this->last_build_production;
    }

    public function getLastBuildStage(): ?string
    {
        return $this->last_build_stage;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function getActiveProductive(): ?bool
    {
        return $this->activeProductive;
    }

    public function getActiveStage(): ?bool
    {
        return $this->activeStage;
    }

    public function getInStage(): ?bool
    {
        return $this->inStage;
    }

    public function getInProductive(): ?bool
    {
        return $this->inProductive;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @return string[]
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * @return string[]
     */
    public function getRequire(): array
    {
        return $this->require;
    }

    /**
     * @return string[]
     */
    public function getRunOnBuild(): array
    {
        return $this->runOnBuild;
    }

    /**
     * @return string[]
     */
    public function getCheckOnBuild(): array
    {
        return $this->checkOnBuild;
    }

    public function getAuthorIcon(): ?string
    {
        return $this->authorIcon;
    }

    public function getPluginIcon(): ?string
    {
        return $this->pluginIcon;
    }

    public function getIsConnectedWithGit(): ?bool
    {
        return $this->isConnectedWithGit;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function getJavaScriptFiles(): array
    {
        return $this->javaScriptFiles;
    }

    /**
     * @return Container[]
     */
    public function getContainers(): array
    {
        return $this->containers;
    }

    /**
     * @return DataProvider[]
     */
    public function getDataProviders(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->dataProviders;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getUpdateInformation(): ?UpdateInformation
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->updateInformation;
    }

    public function getIsClosedSource(): ?bool
    {
        return $this->isClosedSource;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    /**
     * @return string[]
     */
    public function getShortDescription(): array
    {
        return $this->shortDescription;
    }

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string[]
     */
    public function getMarketplaceName(): array
    {
        return $this->marketplaceName;
    }

    public function getSubscriptionInformation(): array
    {
        return $this->subscriptionInformation;
    }

    public function getVersionStage(): ?string
    {
        return $this->versionStage;
    }

    public function getVersionProductive(): ?string
    {
        return $this->versionProductive;
    }

    public function getMarketplaceVariations(): array
    {
        return $this->marketplaceVariations;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function getIsExternalTool(): ?bool
    {
        return $this->isExternalTool;
    }

    public function getDirectDownloadLinks(): array
    {
        return $this->directDownloadLinks;
    }

    public function getForwardLink(): ?string
    {
        return $this->forwardLink;
    }

    public function getNotInstalledRequirements(): array
    {
        return $this->notInstalledRequirements;
    }

    public function getNotActiveStageRequirements(): array
    {
        return $this->notActiveStageRequirements;
    }

    public function getNotActiveProductiveRequirements(): array
    {
        return $this->notActiveProductiveRequirements;
    }

    /**
     * @return string[]
     */
    public function getPluginSetIds(): array
    {
        return $this->pluginSetIds;
    }

    public function getInstalled(): ?bool
    {
        return $this->installed;
    }

    public function getBranch(): ?string
    {
        return $this->branch;
    }

    public function getCommit(): ?string
    {
        return $this->commit;
    }

    /**
     * @return PluginSetEntry[]
     */
    public function getPluginSetEntries(): array
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->pluginSetEntries;
    }

    public function getRepository(): Repository
    {
        // Undocumented - the properties may not match the received data exactly
        return $this->repository;
    }
}
