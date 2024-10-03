<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;
use Tourmix\Shipping\Service\TourmixApiClient;

class MassPrintShippingLabel extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';


    /**
     * @param Context $context
     * @param Filter $filter
     * @param FileFactory $fileFactory
     * @param LabelGenerator $labelGenerator
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        protected readonly FileFactory $fileFactory,
        protected readonly LabelGenerator $labelGenerator,
        protected readonly TourmixApiClient $client,
        CollectionFactory $collectionFactory,
        Context $context,
        Filter $filter,
    )
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * Batch print shipping labels for whole shipments.
     * Push pdf document with shipping labels to user browser
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     * @throws \Zend_Pdf_Exception
     */
    protected function massAction(AbstractCollection $collection)
    {
        $tourmixAccessKeys = [];

        if ($collection->getSize()) {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            foreach ($collection as $shipment) {
                $tourmixAccessKey = $shipment->getData('tourmix_access_key');
                if ($tourmixAccessKey) {
                    $tourmixAccessKeys[] = $tourmixAccessKey;
                }
            }
        }
        $href = null;
        if (!empty($tourmixAccessKeys)) {
            $href = $this->client->generateLabel(implode(',', $tourmixAccessKeys));
        }
        if ($href) {
            $this->getResponse()->setRedirect($href);
            return $this->getResponse();
        }
        $this->messageManager->addError(__('There are no shipping labels related to selected shipments.'));
        return $this->resultRedirectFactory->create()->setPath('sales/shipment/');
    }
}
