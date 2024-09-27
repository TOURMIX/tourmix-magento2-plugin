<?php

/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class QuoteToOrderObserver implements ObserverInterface
{
    public const SHIPPING_METHOD_CODE = 'tourmix_shipping_tourmix_shipping';

    /**
     * Transfer the timewindow from quote to order
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        $shippingMethod = $order->getShippingMethod();
        // Get timewindow from the quote and set it to the order
        if ($quote->getData('tourmix_timewindow') && $shippingMethod == self::SHIPPING_METHOD_CODE) {
            $order->setData('tourmix_timewindow', $quote->getData('tourmix_timewindow'));
        }
    }
}
