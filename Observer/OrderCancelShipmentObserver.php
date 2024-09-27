<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Observer;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Psr\Log\LoggerInterface;
use Tourmix\Shipping\Service\TourmixApiClient;

class OrderCancelShipmentObserver implements ObserverInterface
{
    /**
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param TourmixApiClient $client
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ShipmentRepositoryInterface $shipmentRepository,
        private readonly TourmixApiClient $client,
        private readonly LoggerInterface $logger
    )
    {
    }

    /**
     * Execute observer when order is canceled
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /**
         * @var OrderInterface $order
         */
        $order = $observer->getEvent()->getOrder();

        // Check if the order is canceled
        if ($order->isCanceled()) {
            try {
                // Fetch all shipments related to the order
                $shipments = $order->getShipmentsCollection();

                // Loop through each shipment
                foreach ($shipments as $shipment) {
                    if ($shipment->getData('tourmix_access_key')) {
                        $status = $this->client->cancel($shipment->getData('tourmix_access_key'));
                        $shipment->addComment(
                            "Tourmix Shipping Status: " . $status
                        );
                        $this->shipmentRepository->save($shipment);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('Error canceling shipment: ' . $e->getMessage());
            } catch (GuzzleException $e) {
                $this->logger->error('Error canceling shipment: ' . $e->getMessage());
            }
        }
    }
}
