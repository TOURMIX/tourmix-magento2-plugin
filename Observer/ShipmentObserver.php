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
use Magento\Sales\Api\Data\ShipmentInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Tourmix\Shipping\Service\TourmixApiClient;

class ShipmentObserver implements ObserverInterface
{
    public const SHIPPING_METHOD_CODE = 'tourmix_shipping_tourmix_shipping';

    /**
     * @param LoggerInterface $logger
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param TourmixApiClient $tourmixApiClient
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ShipmentRepositoryInterface $shipmentRepository,
        private readonly TourmixApiClient $tourmixApiClient,
    )
    {
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /**
         * @var ShipmentInterface $shipment
         */
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $shippingMethod = $order->getShippingMethod();
        if ($shipment->getData('tourmix_access_key') && $shippingMethod != self::SHIPPING_METHOD_CODE) {
            return;
        }
        try {
            // Send a POST request to the external API
            $apiResponse = $this->tourmixApiClient->parcelCreation($order);
            $shipment->setData('tourmix_access_key', $apiResponse['access_key']);
            $shipment->setData('tourmix_label_url', $apiResponse['label_url']);
            $this->shipmentRepository->save($shipment);
        } catch (\Exception $e) {
            $this->logger->error('Error while generating shipping label: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            $this->logger->error('Error while generating shipping label: ' . $e->getMessage());
        }
    }
}
