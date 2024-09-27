<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Plugin\Carrier;

use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;


class TimeWindow
{
    /**
     * Description constructor.
     * @param ShippingMethodInterfaceFactory $extensionFactory
     */
    public function __construct(
        protected ShippingMethodInterfaceFactory $extensionFactory
    )
    {
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
            $this->extensionFactory->create();
        $extensionAttribute->setShowWindowTime($rateModel->getShowWindowTime());
        $result->setExtensionAttributes($extensionAttribute);
        return $result;
    }
}