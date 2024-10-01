<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Tourmix\Shipping\Service\TourmixApiClient;

class TourmixShipping extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'tourmix_shipping';
    public const ALLOWED_WEIGHT = 6;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param TourmixApiClient $apiClient
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        protected readonly ResultFactory $rateResultFactory,
        protected readonly MethodFactory $rateMethodFactory,
        protected readonly TourmixApiClient $apiClient,
        array $data = []
    )
    {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return Result|false
     */
    public function collectRates(RateRequest $request): Result|false
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->rateResultFactory->create();
        $method = $this->rateMethodFactory->create();

        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setCarrier($this->_code);
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('title'));

        $shippingPrice = $this->getConfigData('shipping_price');
        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        if (
            $this->validateWeight((float)$request->getPackageWeight())
            && $this->apiClient->isPostcodeValid($request->getDestPostcode())) {
            $method->setShowWindowTime(true);
        } else {
            $method->setShowWindowTime(false);
        }

        $result->append($method);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('title')];
    }

    /**
     * @param float $weight
     * @return bool
     */
    public function validateWeight(float $weight): bool
    {
        if ($weight <= self::ALLOWED_WEIGHT) {
            return true;
        } else {
            return false;
        }
    }
}
