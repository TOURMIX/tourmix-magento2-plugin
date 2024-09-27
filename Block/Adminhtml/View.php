<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Block\Adminhtml;

class View extends \Magento\Shipping\Block\Adminhtml\View
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();
        if ($this->getShipment()->getId()) {
            $this->buttonList->add(
                'tourmix_status',
                [
                    'label' => __('Get Tourmix Status'),
                    'class' => 'save',
                    'onclick' => 'setLocation(\'' . $this->getTourmixStatusUrl() . '\')'
                ]
            );
        }
    }

    /**
     * @return string
     */
    public function getTourmixStatusUrl(): string
    {
        return $this->getUrl('tourmix/shipment/status', ['shipment_id' => $this->getShipment()->getId()]);
    }

}
