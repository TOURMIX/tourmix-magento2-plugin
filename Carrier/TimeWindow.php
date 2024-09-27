<?php

namespace Tourmix\Shipping\Plugin\Carrier;

use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;


class TimeWindow
{
    /**
     * @var ShippingMethodInterfaceFactory
     */
    protected $extensionFactory;

    /**
     * Description constructor.
     * @param ShippingMethodInterfaceFactory $extensionFactory
     */
    public function __construct(
        ShippingMethodInterfaceFactory $extensionFactory
    )
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param $subject
     * @param $result
     * @param $rateModel
     * @return mixed
     */
    public function afterModelToDataObject($subject, $result, $rateModel)
    {
        $extensionAttribute = $result->getExtensionAttributes() ?
            $result->getExtensionAttributes()
            :
            $this->extensionFactory->create()
        ;
        $extensionAttribute->setShowWindowTime($rateModel->getShowWindowTime());
        $result->setExtensionAttributes($extensionAttribute);
        return $result;
    }
}