<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Command;

use Exception;
use FINDOLOGIC\PlentyMarketsRestExporter\Client;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\AuthorizationException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\CustomerException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\PermissionException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\Retry\EmptyResponseException;
use FINDOLOGIC\PlentyMarketsRestExporter\Exception\ThrottlingException;
use FINDOLOGIC\PlentyMarketsRestExporter\Request\WebStoreRequest;
use FINDOLOGIC\PlentyMarketsRestExporter\Utils;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateTokenCommand extends Command
{
    protected static $defaultName = 'generate:token';

    private ?GuzzleClient $client;

    public function __construct(GuzzleClient $client = null)
    {
        parent::__construct();

        $this->client = $client;
    }

    protected function configure()
    {
        $this->setDescription('Generates a bearer token that can be used for manually sending requests.')
            ->setHelp(
                'Logs into the REST API and returns the bearer token. It can be used to manually send' .
                ' requests to the API.'
            );

        $this->addArgument(
            'shopkey',
            InputArgument::OPTIONAL,
            'Optionally add the shopkey of a specific service. Note that this requires' .
            ' the env variable "IMPORT_DATA_URL" to be set in .env.local.',
        );
    }

    /**
     * @throws PermissionException
     * @throws EmptyResponseException
     * @throws CustomerException
     * @throws AuthorizationException
     * @throws ThrottlingException
     * @throws Exception
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $shopkey = Utils::validateAndGetShopkey($input->getArgument('shopkey'));
        $config = Utils::getExportConfiguration($shopkey, $this->client);

        $io->writeln(sprintf('Generating token for service %s...', $config->getDomain()));

        $client = new Client($this->client ?? new GuzzleClient(), $config);

        // Send some request, so we are automatically logged in.
        $request = new WebStoreRequest();
        $client->send($request);

        $io->title('Bearer Token:');
        $io->writeln($client->getAccessToken());
        $io->success('The token was successfully generated.');

        return Command::SUCCESS;
    }
}
