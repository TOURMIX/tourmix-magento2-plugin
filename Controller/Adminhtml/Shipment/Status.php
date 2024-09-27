<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Controller\Adminhtml\Shipment;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Tourmix\Shipping\Service\TourmixApiClient;
use Psr\Log\LoggerInterface;

class Status extends Action
{
    /**
     * @param Context $context
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param JsonFactory $resultJsonFactory
     * @param TourmixApiClient $tourmixApiClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        protected readonly ShipmentRepositoryInterface $shipmentRepository,
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly TourmixApiClient $tourmixApiClient,
        protected readonly LoggerInterface $logger
    )
    {
        parent::__construct($context);
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $resultJson = $this->resultJsonFactory->create();

        try {
            // Load the shipment by ID
            $shipment = $this->shipmentRepository->get($shipmentId);
            if (!$shipment->getData('tourmix_access_key')) {
                $shipment->addComment('Shipment method is not Tourmix or Access key was not provided to Magento system');
            } else {
                // Call the external API service to get the shipment status
                $status = $this->tourmixApiClient->getShipmentStatus($shipment->getData('tourmix_access_key'));
                $shipment->addComment(
                    "Tourmix Shipping Status: " . $status['status_label'] . ' (' . $status['status'] . ') ' . ' created : ' . $status['status_created']
                );
            }
            // Add the status as a comment to the shipment
            $this->shipmentRepository->save($shipment);

            $this->_redirect(
                'adminhtml/order_shipment/view',
                ['shipment_id' => $this->getRequest()->getParam('shipment_id')]
            );
        } catch (\Exception $e) {
            $this->logger->error('Error fetching shipment status: ' . $e->getMessage());
            $this->_redirect(
                'adminhtml/order_shipment/view',
                ['shipment_id' => $this->getRequest()->getParam('shipment_id')]
            );
        }
    }
}