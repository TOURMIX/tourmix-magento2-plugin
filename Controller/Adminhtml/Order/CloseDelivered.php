<?php

/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Controller\Adminhtml\Order;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Tourmix\Shipping\Service\TourmixApiClient;

class CloseDelivered extends Action
{
    public const TOURMIX_SHIPPING_CODE = 'tourmix_shipping_tourmix_shipping';
    public const DELIVERED_STATUS = "DELIVERED";

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param ResourceConnection $resourceConnection
     * @param OrderCollectionFactory $collectionFactory
     * @param TourmixApiClient $tourmixApiClient
     */
    public function __construct(
        Action\Context $context,
        protected OrderRepositoryInterface $orderRepository,
        protected ResourceConnection $resourceConnection,
        protected OrderCollectionFactory $collectionFactory,
        protected TourmixApiClient $tourmixApiClient,
    )
    {
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $collection = $this->collectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('shipping_method', self::TOURMIX_SHIPPING_CODE)
                ->addFieldToFilter('state', ['neq' => Order::STATE_CLOSED])
                ->addFieldToFilter('state', ['neq' => Order::STATE_CANCELED]);;
            foreach ($collection as $order) {
                if ($this->isStatusDelivered($order)) {
                    $order->setState(Order::STATE_CLOSED)
                        ->setStatus(Order::STATE_CLOSED);
                    $this->orderRepository->save($order);
                }
            }
            $this->messageManager->addSuccess(__('The delivered TOURMIX orders have been closed.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while closing the orders.'));
        } catch (GuzzleException $e) {
            $this->messageManager->addError(__('An error occurred while closing the orders from Tourmix.'));
        }

        return $this->resultRedirectFactory->create()->setPath('sales/order/index');
    }

    /**
     * @throws GuzzleException
     */
    private function isStatusDelivered(OrderInterface $order): bool
    {
        $accessKey = $order->getShipmentsCollection()->getFirstItem()->getData('tourmix_access_key');
        if ($accessKey) {
            return $this->tourmixApiClient->getShipmentStatus($accessKey)['status'] === self::DELIVERED_STATUS;
        }
        return false;
    }
}
