<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Chazki\ChazkiArg\Model\Config\Source\ServerEndpoint;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Connect
{
    /**
     * API Response Constants
     */
    const RESPONSE_STATUS_OK = 'Success';
    const RESPONSE_STATUS_ERROR = 'Error';

    /**
     * @var mixed|string
     */
    public $bearerToken = '';

    /**
     * @var mixed
     */
    protected $baseUrl;

    /**
     * @var mixed
     */
    protected $username;

    /**
     * @var mixed
     */
    protected $password;

    /**
     * @var mixed
     */
    protected $APIKey;

    /**
     * @var mixed
     */
    protected $urlServerEndpoint;

    /**
     * @var ScopeInterface
     */
    protected $scopeConfig;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Connect constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param HelperData $helperData
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HelperData $helperData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param bool $body
     * @return mixed
     */
    protected function chazkiRequest($endpoint, $method = 'GET', $body = false)
    {
        $apiKey = $this->getAPIKey();

        if (!empty($apiKey)) {
            if (strpos($endpoint, '?') === false) {
                $apiKey = '?key=' . $apiKey;
            } else {
                $apiKey = '&key=' . $apiKey;
            }
        }

        $ch = curl_init($this->getUrlServerEndpoint() . $endpoint . $apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $headers = [];
        if ($body) {
            $headers[] = "Content-Type: application/json";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $this->helperData->log('Error:' . curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }

    /**
     * @return bool|mixed|string
     */
    public function getAPIKey()
    {
        if (is_null($this->APIKey)) {
            if ($this->getServerEndpoint() === ServerEndpoint::LIVE_SERVER_ENDPOINT) {
                $this->APIKey = $this->getLiveAPIKey();
            } elseif ($this->getServerEndpoint() === ServerEndpoint::TESTING_SERVER_ENDPOINT) {
                $this->APIKey = $this->getTestingAPIKey();
            }
        }

        return $this->APIKey;
    }

    /**
     * @return bool|mixed|string
     */
    protected function getUrlServerEndpoint()
    {
        if (is_null($this->urlServerEndpoint)) {
            if ($this->getServerEndpoint() === ServerEndpoint::LIVE_SERVER_ENDPOINT) {
                $this->urlServerEndpoint = $this->getLiveServerEndpoint();
            } elseif ($this->getServerEndpoint() === ServerEndpoint::TESTING_SERVER_ENDPOINT) {
                $this->urlServerEndpoint = $this->getTestingServerEndpoint();
            }
        }

        return $this->urlServerEndpoint;
    }

    /**
     * Send shipping info to Chazki
     *
     * @param $shipmentInfo
     *
     * @return mixed
     */
    public function createShipment($shipmentInfo)
    {
        return $this->chazkiRequest(
            '/shipment/create',
            'POST',
            json_encode($shipmentInfo)
        );
    }

    /**
     * Get shipping info from Chazki
     *
     * @param $trackingId
     *
     * @return mixed
     */
    public function getShipment($trackingId)
    {
        return $this->chazkiRequest(
            '/shipment/tracker/' . $trackingId,
            'GET'
        );
    }

    /**
     * @return mixed
     */
    protected function getServerEndpoint()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/server_endpoint', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getTestingServerEndpoint()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/url_testing', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getLiveServerEndpoint()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/url_live', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getTestingAPIKey()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/api_key_testing', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getLiveAPIKey()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/api_key_live', ScopeInterface::SCOPE_STORE);
    }
}
