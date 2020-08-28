<?php
namespace App;

use BolCom\RetailerApi\Client;
use BolCom\RetailerApi\Client\ClientConfigInterface;
use BolCom\RetailerApi\Infrastructure\ClientPool;
use BolCom\RetailerApi\Infrastructure\MessageBus;
use BolCom\RetailerApi\Model\ClientPoolInterface;
use BolCom\RetailerApi\Model\MessageBusInterface;
use Psr\Log\LoggerInterface;

class ServiceFactory
{
	public static function createCustomerService(ClientConfigInterface $clientConfig, LoggerInterface $logger): CustomerService
	{
		$messageBus = self::createMessageBus($clientConfig, $logger);
		$service = new CustomerService($messageBus);
		$service->setLogger($logger);
		return $service;
	}

	private static function createMessageBus(ClientConfigInterface $clientConfig, LoggerInterface $logger): MessageBusInterface
	{
		return new MessageBus(
			new ClientPool([
				ClientPoolInterface::DEFAULT_CLIENT_NAME => new Client($clientConfig, $logger)
			])
		);
	}
}