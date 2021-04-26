<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Response\Collection;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\PluginsFromSetParser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PluginFromSetResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin\Container;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin\DataProvider;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin\PluginSetEntry;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper\ResponseHelper;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;

class PluginFromSetResponseTest extends TestCase
{
    use ResponseHelper;

    /**
     * @var GuzzleResponse
     */
    private $response;

    /**
     * @var PluginFromSetResponse
     */
    private $pluginResponse;

    public function setUp(): void
    {
        $this->response = $this->getMockResponse('PluginFromSetResponse/response.json');
        $this->pluginResponse = PluginsFromSetParser::parse($this->response);
    }

    public function testMethodsForEntitySearchInResponse(): void
    {
        $plugin = $this->pluginResponse->first();
        $this->assertEquals(5, $plugin->getId());

        $plugin = $this->pluginResponse->findOne(['name' => 'Findologic']);
        $this->assertEquals(15, $plugin->getId());

        $plugins = $this->pluginResponse->find(['author' => 'plentysystems AG']);
        $this->assertCount(2, $plugins);

        $plugins = $this->pluginResponse->all();
        $this->assertCount(3, $plugins);
    }

    public function testDataCanBeFetched(): void
    {
        $plugin = $this->pluginResponse->findOne(['name' => 'Findologic']);

        $this->assertEquals($this->getExpectedData(), $plugin->getData());

        $this->assertEquals(15, $plugin->getId());
        $this->assertEquals('Findologic', $plugin->getName());
        $this->assertEquals(null, $plugin->getLastBuildProduction());
        $this->assertEquals(null, $plugin->getLastBuildStage());
        $this->assertEquals('2019-03-05 11:53:09', $plugin->getCreatedAt());
        $this->assertEquals('2019-03-05 11:53:09', $plugin->getUpdatedAt());
        $this->assertEquals('2', $plugin->getPosition());
        $this->assertEquals(true, $plugin->getActiveProductive());
        $this->assertEquals(false, $plugin->getActiveStage());
        $this->assertEquals(false, $plugin->getInStage());
        $this->assertEquals(false, $plugin->getInProductive());
        $this->assertEquals('template', $plugin->getType());
        $this->assertEquals('3.3.0', $plugin->getVersion());
        $this->assertEquals('The official Findologic plugin for plentymarkets Ceres.', $plugin->getDescription());
        $this->assertEquals('Findologic', $plugin->getNamespace());
        $this->assertEquals('FINDOLOGIC GmbH', $plugin->getAuthor());
        $this->assertEquals([], $plugin->getKeywords());
        $this->assertEquals(['Ceres' => '~5.0', 'IO' => '~5.0'], $plugin->getRequire());
        $this->assertEquals([], $plugin->getRunOnBuild());
        $this->assertEquals([], $plugin->getCheckOnBuild());
        $this->assertEquals('', $plugin->getAuthorIcon());
        $this->assertEquals('', $plugin->getPluginIcon());
        $this->assertEquals(true, $plugin->getIsConnectedWithGit());
        $this->assertEquals(['findologic/http_request2' => '2.3.1'], $plugin->getDependencies());
        $this->assertEquals([], $plugin->getJavaScriptFiles());
        $this->assertEquals('git', $plugin->getSource());
        $this->assertEquals(false, $plugin->getIsClosedSource());
        $this->assertEquals('AGPL-3.0', $plugin->getLicense());
        $this->assertEquals(
            [
                'de' => 'Das offizielle Findologic plugin für plentymarkets Ceres',
                'en' => 'The official Findologic plugin for plentymarkets Ceres.'
            ],
            $plugin->getShortDescription()
        );
        $this->assertEquals([4090], $plugin->getCategories());
        $this->assertEquals(0.0, $plugin->getPrice());
        $this->assertEquals('plugins@findologic.com', $plugin->getEmail());
        $this->assertEquals('+43 662 45 67 08', $plugin->getPhone());
        $this->assertEquals(
            [
                'de' => 'Findologic - Search & Navigation Platform',
                'en' => 'Findologic - Search & Navigation Platform'
            ],
            $plugin->getMarketplaceName()
        );
        $this->assertEquals([], $plugin->getSubscriptionInformation());
        $this->assertEquals('', $plugin->getVersionStage());
        $this->assertEquals('', $plugin->getVersionProductive());
        $this->assertEquals([], $plugin->getMarketplaceVariations());
        $this->assertEquals('', $plugin->getWebhookUrl());
        $this->assertEquals(false, $plugin->getIsExternalTool());
        $this->assertEquals([], $plugin->getDirectDownloadLinks());
        $this->assertEquals('', $plugin->getForwardLink());
        $this->assertEquals([], $plugin->getNotInstalledRequirements());
        $this->assertEquals([], $plugin->getNotActiveStageRequirements());
        $this->assertEquals([], $plugin->getNotActiveProductiveRequirements());
        $this->assertEquals(['13'], $plugin->getPluginSetIds());
        $this->assertEquals(true, $plugin->getInstalled());
        $this->assertEquals(null, $plugin->getBranch());
        $this->assertEquals(null, $plugin->getCommit());

        $containers = $plugin->getContainers();
        $this->assertCount(2, $containers);
        /** @var Container $container */
        $container = reset($containers);
        $this->assertEquals('Findologic::CategoryItem.Promotion', $container->getKey());
        $this->assertEquals('Category item list: Add content to main container', $container->getName());
        $this->assertEquals(
            'Provides content for promotion banners (search and category pages only)',
            $container->getDescription()
        );
        $this->assertEquals(false, $container->getMultiple());

        $dataProviders = $plugin->getDataProviders();
        $this->assertCount(4, $dataProviders);
        /** @var DataProvider $dataProvider */
        $dataProvider = reset($dataProviders);
        $this->assertEquals('Findologic\Containers\SearchFilterContainer', $dataProvider->getKey());
        $this->assertEquals('Filters', $dataProvider->getName());
        $this->assertEquals('Display Findologic filters', $dataProvider->getDescription());

        $updateInformation = $plugin->getUpdateInformation();
        $this->assertEquals(false, $updateInformation->getHasUpdate());
        $this->assertEquals(null, $updateInformation->getUpdateVariationId());
        $this->assertEquals(null, $updateInformation->getUpdateVersion());

        $pluginSetEntries = $plugin->getPluginSetEntries();
        $this->assertCount(1, $pluginSetEntries);
        /** @var PluginSetEntry $pluginSetEntry */
        $pluginSetEntry = reset($pluginSetEntries);
        $this->assertEquals(199, $pluginSetEntry->getId());
        $this->assertEquals(15, $pluginSetEntry->getPluginId());
        $this->assertEquals(13, $pluginSetEntry->getPluginSetId());
        $this->assertEquals('2021-01-27T11:19:15+00:00', $pluginSetEntry->getCreatedAt());
        $this->assertEquals('2021-02-02T14:25:29+00:00', $pluginSetEntry->getUpdatedAt());
        $this->assertEquals(null, $pluginSetEntry->getDeletedAt());
        $this->assertEquals('branchname', $pluginSetEntry->getBranchName());
        $this->assertEquals('2', $pluginSetEntry->getPosition());
        $this->assertEquals('0d5efe17a8e51b2478641631d926d1fffc25a63f', $pluginSetEntry->getCommit());

        $repository = $plugin->getRepository();
        $this->assertEquals(130, $repository->getId());
        $this->assertEquals(
            'https://github.com/findologic/plugin-plentymarkets-ceres-api',
            $repository->getRemoteUrl()
        );
        $this->assertEquals('github-user', $repository->getUsername());
        $this->assertEquals('', $repository->getBranch());
        $this->assertEquals('1231321564dfs4g6sd1g', $repository->getWebhookToken());
        $this->assertEquals('0', $repository->getAutoFetch());
        $this->assertEquals('2021-01-27 11:18:50', $repository->getCreatedAt());
        $this->assertEquals('2021-01-27 11:18:50', $repository->getUpdatedAt());
    }

    private function getExpectedData(): array
    {
        return [
            'id' => 15,
            'name' => 'Findologic',
            'last_build_production' => null,
            'last_build_stage' => null,
            'created_at' => '2019-03-05 11:53:09',
            'updated_at' => '2019-03-05 11:53:09',
            'position' => '2',
            'activeProductive' => true,
            'activeStage' => false,
            'inStage' => false,
            'inProductive' => false,
            'type' => 'template',
            'version' => '3.3.0',
            'description' => 'The official Findologic plugin for plentymarkets Ceres.',
            'namespace' => 'Findologic',
            'author' => 'FINDOLOGIC GmbH',
            'keywords' => [
            ],
            'require' => [
                'Ceres' => '~5.0',
                'IO' => '~5.0',
            ],
            'runOnBuild' => [
            ],
            'checkOnBuild' => [
            ],
            'authorIcon' => '',
            'pluginIcon' => '',
            'isConnectedWithGit' => true,
            'dependencies' => [
                'findologic/http_request2' => '2.3.1',
            ],
            'javaScriptFiles' => [
            ],
            'source' => 'git',
            'isClosedSource' => false,
            'license' => 'AGPL-3.0',
            'shortDescription' => [
                'de' => 'Das offizielle Findologic plugin für plentymarkets Ceres',
                'en' => 'The official Findologic plugin for plentymarkets Ceres.',
            ],
            'categories' => ['4090'],
            'price' => 0,
            'email' => 'plugins@findologic.com',
            'phone' => '+43 662 45 67 08',
            'marketplaceName' => [
                'de' => 'Findologic - Search & Navigation Platform',
                'en' => 'Findologic - Search & Navigation Platform',
            ],
            'subscriptionInformation' => [
            ],
            'versionStage' => '',
            'versionProductive' => '',
            'marketplaceVariations' => [
            ],
            'webhookUrl' => '',
            'isExternalTool' => false,
            'directDownloadLinks' => [
            ],
            'forwardLink' => '',
            'notInstalledRequirements' => [
            ],
            'notActiveStageRequirements' => [
            ],
            'notActiveProductiveRequirements' => [
            ],
            'pluginSetIds' => ['13'],
            'installed' => true,
            'branch' => null,
            'commit' => null,
            'updateInformation' => [
                'hasUpdate' => false,
                'updateVariationId' => null,
                'updateVersion' => null,
            ],
            'repository' => [
                'id' => 130,
                'remoteUrl' => 'https://github.com/findologic/plugin-plentymarkets-ceres-api',
                'username' => 'github-user',
                'branch' => '',
                'webhookToken' => '1231321564dfs4g6sd1g',
                'autoFetch' => '0',
                'createdAt' => '2021-01-27 11:18:50',
                'updatedAt' => '2021-01-27 11:18:50',
            ],
            'containers' => [
                [
                    'key' => 'Findologic::CategoryItem.Promotion',
                    'name' => 'Category item list: Add content to main container',
                    'description' => 'Provides content for promotion banners (search and category pages only)',
                    'multiple' => false,
                ],
                [
                    'key' => 'Findologic::CategoryItem.SmartDidYouMean',
                    'name' => 'Category item list: Add alternative searchwords to the search page title',
                    'description' => 'Adds the Smart Did-You-Mean data right beneath the search page title',
                    'multiple' => false,
                ]
            ],
            'dataProviders' => [
                [
                    'key' => 'Findologic\\Containers\\SearchFilterContainer',
                    'name' => 'Filters',
                    'description' => 'Display Findologic filters',
                ],
                [
                    'key' => 'Findologic\\Containers\\SearchBarContainer',
                    'name' => 'Search Bar',
                    'description' => 'Display search bar customized for Findologic',
                ],
                [
                    'key' => 'Findologic\\Containers\\PromotionContainer',
                    'name' => 'Promotion',
                    'description' => 'Display promotion banner',
                ],
                [
                    'key' => 'Findologic\\Containers\\SmartDidYouMeanContainer',
                    'name' => 'Smart Did-You-Mean',
                    'description' => 'Display Smart Did-You-Mean info for the current search',
                ],
            ],
            'pluginSetEntries' => [
                [
                    'id' => 199,
                    'pluginId' => 15,
                    'pluginSetId' => 13,
                    'createdAt' => '2021-01-27T11:19:15+00:00',
                    'updatedAt' => '2021-02-02T14:25:29+00:00',
                    'deleted_at' => null,
                    'branchName' => 'branchname',
                    'position' => '2',
                    'commit' => '0d5efe17a8e51b2478641631d926d1fffc25a63f',
                ],
            ],
        ];
    }
}
