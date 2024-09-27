<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const XML_PATH_ORIGIN_CITY = 'shipping/origin/city';

    const XML_PATH_ORIGIN_POSTCODE = 'shipping/origin/postcode';

    const XML_PATH_ORIGIN_STREET_LINE_1 = 'shipping/origin/street_line1';

    const XML_PATH_ORIGIN_STREET_LINE_2 = 'shipping/origin/street_line2';

    const XML_PATH_TOURMIX_SHIPPING_ACTIVE = 'carriers/tourmix_shipping/active';
    const XML_PATH_TOURMIX_SHIPPING_TITLE = 'carriers/tourmix_shipping/title';
    const XML_PATH_TOURMIX_SHIPPING_API_URL = 'carriers/tourmix_shipping/api_url';
    const XML_PATH_TOURMIX_SHIPPING_API_TOKEN = 'carriers/tourmix_shipping/api_token';
    const XML_PATH_TOURMIX_SHIPPING_PRICE = 'carriers/tourmix_shipping/shipping_price';
    const XML_PATH_TOURMIX_SHIPPING_ALLOWED_WEIGHT = 'carriers/tourmix_shipping/allowed_weight';

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    )
    {
    }

    /**
     * Check if the shipping method is active
     *
     * @param string|null $store
     * @return bool
     */
    public function isActive(string $store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_TOURMIX_SHIPPING_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the shipping method title
     *
     * @param string|null $store
     * @return string
     */
    public function getTitle(string $store = null): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TOURMIX_SHIPPING_TITLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the API URL
     *
     * @param string|null $store
     * @return string
     */
    public function getApiUrl(string $store = null): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TOURMIX_SHIPPING_API_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the encrypted API token
     *
     * @param string|null $store
     * @return string
     */
    public function getApiToken(string $store = null): string
    {
        $apiToken = $this->scopeConfig->getValue(
            self::XML_PATH_TOURMIX_SHIPPING_API_TOKEN,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return (!empty($apiToken)) ? $this->encryptor->decrypt($apiToken) : '';

    }

    /**
     * Get the shipping price
     *
     * @param string|null $store
     * @return float
     */
    public function getShippingPrice(string $store = null): float
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_TOURMIX_SHIPPING_PRICE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the allowed weight for the time window iframe
     *
     * @param string|null $store
     * @return float
     */
    public function getAllowedWeight(string $store = null): float
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_TOURMIX_SHIPPING_ALLOWED_WEIGHT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $store
     * @return array
     */
    public function getStartLocation($store = null): array
    {
        return [
            "zip" => (string)$this->scopeConfig->getValue(self::XML_PATH_ORIGIN_POSTCODE, ScopeInterface::SCOPE_STORE, $store),
            "city" => (string)$this->scopeConfig->getValue(self::XML_PATH_ORIGIN_CITY, ScopeInterface::SCOPE_STORE, $store),
            "street" => (string)$this->scopeConfig->getValue(self::XML_PATH_ORIGIN_STREET_LINE_1, ScopeInterface::SCOPE_STORE, $store),
            "number" => (int)$this->scopeConfig->getValue(self::XML_PATH_ORIGIN_STREET_LINE_2, ScopeInterface::SCOPE_STORE, $store),
        ];
    }
}
