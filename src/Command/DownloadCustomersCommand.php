<?php
namespace App\Command;

use App\Mailer;
use App\ServiceFactory;
use BolCom\RetailerApi\Client\ClientConfigInterface;
use Monolog\Handler\PsrHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCustomersCommand extends Command
{
	/** @var string */
	protected static $defaultName = 'download-customers';

	protected function configure()
	{
		$this
			->addOption('send-email', 'e', InputOption::VALUE_NONE, 'Send email ')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$sendEmail = $input->getOption('send-email');
		$log = __DIR__.'/../../var/download-customers.log';

		$logger = new Logger('DownloadCustomer');
		$logger->pushHandler(new PsrHandler(new ConsoleLogger($output)));
		$logger->pushHandler(new StreamHandler($log, Logger::INFO));

		global $API_CONFIG;
		foreach ($API_CONFIG as $configData) {
			$clientName = $configData['clientName'];
			$customersFile = __DIR__.'/../../var/'.$clientName.'_customers.csv';
			$tokenPath = '/tmp/bol_customer_downloader_'.$clientName.'.json';
			$config = new \BolCom\RetailerApi\Client\ClientConfig(
				$configData['clientId'],
				$configData['key'],
				false,
				$tokenPath
			);

			try {
				try {
					$this->downloadForClient($customersFile, $config, $logger);
				} catch (\Exception $e) {
					$logger->critical('Exception: '.$e->getMessage(), ['e' => $e]);
					Mailer::sendError($e->getMessage());
				}

				if ($sendEmail) {
					Mailer::sendCustomers($customersFile);
					$logger->info('Customers have sent to email');
				}
			} catch (\Exception $e) {
				$logger->critical('Exception: '.$e->getMessage(), ['e' => $e]);
			}
		}
		return 0;
	}

	private function downloadForClient(string $customersFile, ClientConfigInterface $config, LoggerInterface $logger)
	{
		$customerService = ServiceFactory::createCustomerService($config, $logger);
		$customers = $customerService->loadCustomersFromCsvFile($customersFile);
		$h = fopen($customersFile, 'ab');
		if (!$h) {
			throw new \RuntimeException('Failed open csv file');
		}
		$callback = $this->createCallbackOnNewCustomerSaveToCsv($h, $logger);
		try {
			$customerService->updateCustomers($customers, $callback);
			$logger->info('Done');
		} catch (\Exception $e) {
			$logger->critical('Exception: '.$e->getMessage(), ['e' => $e]);
		} finally {
			fclose($h);
		}

		return 0;
	}

	private function createCallbackOnNewCustomerSaveToCsv($handler, LoggerInterface $logger): callable
	{
		return function (array $customerData) use ($handler, $logger) {
			fputcsv($handler, $customerData);
			$logger->debug(implode(', ', $customerData));
		};
	}
}