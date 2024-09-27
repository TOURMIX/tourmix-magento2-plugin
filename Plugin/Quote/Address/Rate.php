<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Plugin\Quote\Address;

use Magento\Quote\Model\Quote\Address\RateResult\Method;

class Rate
{
    /**
     * @param $subject
     * @param $result
     * @param $rate
     * @return mixed
     */
    public function afterImportShippingRate($subject, $result, $rate)
    {
        if ($rate instanceof Method) {
            $result->setShowWindowTime(
                $rate->getShowWindowTime()
            );
        }

        return $result;
    }
}