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
    const XML_PATH_TOURMIX_SHIPPING_SANDBOX_MODE = 'carriers/tourmix_shipping/sandbox_mode';

    const XML_PATH_TOURMIX_SHIPPING_TITLE = 'carriers/tourmix_shipping/title';
    const XML_PATH_TOURMIX_SHIPPING_API_TOKEN = 'carriers/tourmix_shipping/api_token';
    const XML_PATH_TOURMIX_SHIPPING_TEST_API_TOKEN = 'carriers/tourmix_shipping/test_api_token';
    const XML_PATH_TOURMIX_SHIPPING_PRICE = 'carriers/tourmix_shipping/shipping_price';

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
     * Check if the shipping method is active
     *
     * @param string|null $store
     * @return bool
     */
    public function isSandboxEnabled(string $store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_TOURMIX_SHIPPING_SANDBOX_MODE,
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
     * Get the encrypted TEST API token
     *
     * @param string|null $store
     * @return string
     */
    public function getTestApiToken(string $store = null): string
    {
        $apiToken = $this->scopeConfig->getValue(
            self::XML_PATH_TOURMIX_SHIPPING_TEST_API_TOKEN,
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
