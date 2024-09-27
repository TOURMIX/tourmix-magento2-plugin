<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Plugin;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class ShippingInformationManagementPlugin
{

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        private readonly CartRepositoryInterface $quoteRepository
    )
    {
    }

    /**
     * Before plugin for saveAddressInformation
     *
     * @param ShippingInformationManagement $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        int $cartId,
        ShippingInformationInterface $addressInformation
    ): void
    {
        // Get the shipping address
        $shippingAddress = $addressInformation->getShippingAddress();

        // Get extension attributes from the shipping address
        $extensionAttributes = $shippingAddress->getExtensionAttributes();
        // Check if timewindow is available in the extension attributes
        if ($extensionAttributes && $extensionAttributes->getTimewindow()) {
            $timewindow = $extensionAttributes->getTimewindow();

            // Get the active quote
            $quote = $this->quoteRepository->getActive($cartId);

            // Set the timewindow in the quote
            $quote->setData('tourmix_timewindow', $timewindow);

            // Save the quote
            $this->quoteRepository->save($quote);
        }
    }
}
