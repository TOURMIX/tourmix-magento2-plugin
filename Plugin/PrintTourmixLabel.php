<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Plugin;

use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Shipment\PrintAction;

class PrintTourmixLabel
{
    /**
     * @param ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(private readonly ShipmentRepositoryInterface $shipmentRepository)
    {
    }

    /**
     * @param PrintAction $subject
     * @param callable $proceed
     * @return ResponseInterface
     * @throws \Exception
     */
    public function aroundExecute(PrintAction $subject, callable $proceed): ResponseInterface
    {
        /**
         * @var ShipmentInterface $shipment
         */
        $shipmentId = $subject->getRequest()->getParam('shipment_id');
        $shipment = $this->shipmentRepository->get($shipmentId);
        if (!$shipment->getData('tourmix_label_url')) {
            return $proceed();
        }
        $subject->getResponse()->setRedirect($shipment->getData('tourmix_label_url'));
        return $subject->getResponse();
    }


}