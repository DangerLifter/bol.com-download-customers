<?php
namespace App;

use BolCom\RetailerApi\Model\Customer\CustomerDetails;
use BolCom\RetailerApi\Model\MessageBusInterface;
use BolCom\RetailerApi\Model\Offer\FulfilmentMethod;
use BolCom\RetailerApi\Model\Order\OrderId;
use BolCom\RetailerApi\Model\Shipment\Query\GetShipment;
use BolCom\RetailerApi\Model\Shipment\Query\GetShipmentList;
use BolCom\RetailerApi\Model\Shipment\Shipment;
use BolCom\RetailerApi\Model\Shipment\ShipmentId;
use BolCom\RetailerApi\Model\Shipment\ShipmentList;
use BolCom\RetailerApi\Model\Shipment\ShipmentListItem;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CustomerService implements LoggerAwareInterface
{
	use LoggerAwareTrait;

//	private MessageBusInterface $_messageBus; For php 7.2
	/** @var MessageBusInterface */
	private $_messageBus;

	public function __construct(MessageBusInterface $_messageBus)
	{
		$this->_messageBus = $_messageBus;
	}

	public function loadCustomersFromCsvFile(string $file): array
	{
		if (!file_exists($file)) {
			return [];
		}
		$result = [];
		$h = fopen($file, 'rb');
		try {
			while($data = fgetcsv($h)) {
				$result[] = $data;
			}
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
		} finally {
			fclose($h);
		}
		return $result;
	}

	public function updateCustomers(array $customerList = [], callable $onNewCustomer = null): self
	{
		$page = 1;
		while ($this->updateCustomersFromPage($page, FulfilmentMethod::FBR(), $customerList, $onNewCustomer)) {
			$page++;
		}
		$page = 1;
		while ($this->updateCustomersFromPage($page, FulfilmentMethod::FBB(), $customerList, $onNewCustomer)) {
			$page++;
		}
		return $this;
	}

	private function updateCustomersFromPage(int $page, FulfilmentMethod $method, array &$customerList, callable $onNewCustomer): bool
	{
		$hasNewCustomers = false;
		$this->logger->debug('Proceed method: '.$method->toString().'; page: '.$page);
		$message = GetShipmentList::with($page,$method);
		/** @var ShipmentList $shipmentList */
		$shipmentList = $this->_messageBus->dispatch($message);
		if (!$shipmentList || !$shipmentList->shipments()) {
			return $hasNewCustomers;
		}
		/** @var Shipment $fullShipment */
		foreach ($shipmentList->shipments() as $shipmentListItem) {
			$orderId = $this->getSingleShipmentOrderId($shipmentListItem);
			if (!$orderId || $this->hasShipmentInList($customerList, $orderId->value())) {
				$this->logger->debug('Order: '.$orderId->value().' already in list');
				continue;
			}

			$hasNewCustomers = true;
			$fullShipment = $this->_messageBus->dispatch(GetShipment::with($shipmentListItem->shipmentId()));
			$customerAsArray = $this->customerToArray($fullShipment->customerDetails(), $orderId);
			if ($onNewCustomer) $onNewCustomer($customerAsArray);
			$customerList[] = $customerAsArray;
		}
		return $hasNewCustomers;
	}

	private function hasShipmentInList(array $customerList, int $orderId): bool
	{
		foreach ($customerList as $customerData) {
			if ($customerData[0] == $orderId) return true;
		}
		return false;
	}

	private function getSingleShipmentOrderId(ShipmentListItem $shipmentListItem): ?OrderId
	{
		if (!$shipmentListItem->shipmentItems()) return null;
		return $shipmentListItem->shipmentItems()[0]->orderId();
	}

	private function customerToArray(CustomerDetails $customerDetails, OrderId $orderId): array
	{
		return [
			$orderId->value(),
			$customerDetails->firstName(),
			$customerDetails->surname(),
			$customerDetails->deliveryPhoneNumber(),
			$customerDetails->zipCode(),
			$customerDetails->countryCode(),
			$customerDetails->city(),
			$customerDetails->streetName().' '.$customerDetails->houseNumberExtended().' '.$customerDetails->houseNumber(),
			$customerDetails->extraAddressInformation(),
			$customerDetails->vatNumber()
		];
	}
}