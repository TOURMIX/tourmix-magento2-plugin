<?php
/**
 * Tourmix Shipping
 *
 * @copyright   Copyright (c) 2024 Tourmix (https://tourmix.delivery/)
 * @author      Vadym Drobko <vdrob17@gmail.com>
 */

declare(strict_types=1);

namespace Tourmix\Shipping\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Tourmix\Shipping\Model\Config as TourmixConfig;

class TourmixApiClient
{
    const API_URL = 'https://tourmix.delivery/api/';

    const TEST_API_URL = 'https://test.tourmix.delivery/api/';
    const TOURMIX_ALLOWED_ZIPS = 'https://tourmix.delivery/api/allowed_zips';

    /**
     * @param Client $client
     * @param TourmixConfig $config
     * @param Json $json
     */
    public function __construct(
        private readonly Client $client,
        private readonly TourmixConfig $config,
        private readonly Json $json,

    )
    {
    }

    /**
     * @param string $urlRequest
     * @param string $methodType
     * @param null $body
     * @return string|null
     * @throws GuzzleException
     */
    private function callApi(string $urlRequest, string $methodType, $body = null): ?string
    {
        $isSandbox = $this->config->isSandboxEnabled();
        $apiToken = $isSandbox ? $this->config->getTestApiToken() : $this->config->getApiToken();
        $apiUrl = $isSandbox ? self::TEST_API_URL : self::API_URL;
        $response = $this->client->request(
            $methodType,
            $apiUrl . $urlRequest . '?api_token=' . $apiToken,
            ['json' => $body]);
        if ($response->getStatusCode() == 200) {
            return $response->getBody()->getContents();
        }
        return null;
    }

    /**
     * @param $shipmentIncrement
     * @param OrderInterface $order
     * @return array
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function parcelCreation($shipmentIncrement, OrderInterface $order): array
    {
        $address = $this->getAddress($order->getShippingAddress()->getStreetLine(1));
        $cod = $this->isOfflinePaymentMethod($order);
        $dataArray = [
            "parcels" => [
                [
                    "recipient" => [
                        "name" => $order->getCustomerName(),
                        "email" => $order->getCustomerEmail(),
                        "phone" => $order->getShippingAddress()->getTelephone(),
                    ],
                    "start_location" => $this->config->getStartLocation(),
                    "end_location" => [
                        "zip" => $order->getShippingAddress()->getPostcode(),
                        "city" => $order->getShippingAddress()->getCity(),
                        "street" => $address['street'],
                        "number" => $address['number']
                            ?: (int)$this->getAddress($order->getShippingAddress()->getStreetLine(2))
                                ?: 0,
                    ],
                    "weight" => (int)$order->getWeight(),
                    "timewindow" => $order->getData('tourmix_timewindow') ?: '0',
                    "size" => "-",
                    "outer_id" => $shipmentIncrement,
                    "outer_id_type" => "MAGENTO",
                    "cod" => $cod,
                    "totalGross" => $cod ? (float)$order->getGrandTotal() : null,
                ]
            ]
        ];
        $response = $this->json->unserialize($this->callApi('post_multiple_parcels', "POST", $dataArray));

        return [
            'access_key' => array_first($response["parcels"])["access_key"],
            'label_url' => $response['label_url'],
        ];
    }

    /**
     * @param $accessKey
     * @return array
     * @throws GuzzleException
     */
    public function getShipmentStatus($accessKey): array
    {
        $response = $this->json->unserialize($this->callApi('parcels/' . $accessKey . '/status', 'GET'));
        return [
            'status' => array_first($response["parcels"])["last_status"]['status'],
            'status_label' => array_first($response["parcels"])["last_status"]['status_label'],
            'status_created' => array_first($response["parcels"])["last_status"]['created_at'],
        ];
    }

    /**
     * @param string $accessKey
     * @return string
     * @throws GuzzleException
     */
    public function cancel(string $accessKey): string
    {
        $response = $this->json->unserialize($this->callApi('parcels/' . $accessKey . '/cancel', 'POST'));
        return
            array_first($response)[$accessKey];
    }

    /**
     * @throws GuzzleException
     */
    public function isPostcodeValid(string $zip = null): bool
    {
        $data = [];
        if (!$zip) {
            return false;
        }
        $response = $this->client->request("GET", self::TOURMIX_ALLOWED_ZIPS);
        if ($response->getStatusCode() == 200) {
            $response = $this->json->unserialize($response->getBody()->getContents());
            foreach ($response as $item) {
                $data[] = $item['zip'];
            }
        }
        return in_array($zip, $data);
    }

    /**
     * @param string $address
     * @return array|string[]
     */
    private function getAddress(string $address): array
    {
        if (preg_match('/\d+/', $address, $matches)) {
            $houseNumber = $matches[0];

            $addressWithoutNumber = preg_replace('/\d+/', '', $address);

            // Trim any extra spaces
            $addressWithoutNumber = trim($addressWithoutNumber);

            return [
                'number' => $houseNumber,
                'street' => $addressWithoutNumber,
            ];
        } else {
            return [
                'number' => false,
                'street' => $address,
            ];
        }
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @throws LocalizedException
     */
    private function isOfflinePaymentMethod(OrderInterface $order): bool
    {
        $payment = $order->getPayment();
        if ($payment) {
            $paymentMethodInstance = $payment->getMethodInstance();
            if ($paymentMethodInstance instanceof MethodInterface) {
                return $paymentMethodInstance->isOffline();
            }
        }
        return false;
    }
}