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
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Tourmix\Shipping\Model\Config as TourmixConfig;

class TourmixApiClient
{
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
        $apiToken = $this->config->getApiToken();
        $apiUrl = $this->config->getApiUrl();
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
     * @param OrderInterface $order
     * @return array
     * @throws GuzzleException
     */
    public function parcelCreation(OrderInterface $order): array
    {
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
                        "street" => $order->getShippingAddress()->getStreetLine(1),
                        "number" => $order->getShippingAddress()->getStreetLine(2),
                    ],
                    "weight" => (int)$order->getWeight(),
                    "timewindow" => $order->getData('tourmix_timewindow') ?: '0',
                    "size" => "-",
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
}