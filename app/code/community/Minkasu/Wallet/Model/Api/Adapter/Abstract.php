<?php

abstract class Minkasu_Wallet_Model_Api_Adapter_Abstract
{
    /**
     * API response formats
     */
    const FORMAT_JSON = 'json';

    /**
     * Timeout in sec
     */
    const CLIENT_TIMEOUT = 10;

    /**
     * HTTP Response Codes
     */
    const HTTP_OK             = 200;
    const HTTP_CREATED        = 201;
    const HTTP_INTERNAL_ERROR = 500;

    /**
     * The adapter options
     *
     * @var array
     */
    protected $_options = array(
        'url' => ':url/:type/:id/:action',
        'format' => self::FORMAT_JSON,
    );

    /**
     * Instantiate a new adapter
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->_options = array_merge($this->_options, $options);
    }

    /**
     * Send a request to the server, receive a response
     *
     * @abstract
     * @param string $url
     * @param array $parameters
     * @param string $httpMethod
     * @return string HTTP response
     */
    abstract protected function _doRequest($url, array $parameters = array(), $httpMethod = Varien_Http_Client::GET);

    /**
     * Send a GET request
     *
     * @param string $type
     * @param array $parameters
     * @return array Data
     */
    public function get($type, array $parameters = array())
    {
        $response = $this->_request($type, $parameters, Varien_Http_Client::GET);
        try {
            $decodedResponse = $this->decodeResponse($response->getBody());
        } catch (Exception $e) {
            Mage::logException($e);
            $decodedResponse = $response->getBody();
        }

        if (self::HTTP_OK !== $response->getStatus()) {
            /** @var $apiHelper Minkasu_Wallet_Helper_Api */
            $apiHelper = Mage::helper('minkasu_wallet/api');
            /** @var $helper Minkasu_Wallet_Helper_Data */
            $helper = Mage::helper('minkasu_wallet');

            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $error = $decodedResponse['error'];
            } else {
                $error = $decodedResponse;
            }

            Mage::log(
                $helper->__(
                    'API type: %s. Params: %s. HTTP code: %s. Response: %s.',
                    $type, json_encode($parameters), $response->getStatus(), $response->getBody()
                ),
                null,
                $apiHelper->getApiLogFilename()
            );
            Mage::throwException($helper->__('Minkasu API Error: %s.', $error));
        }
        return $decodedResponse;
    }

    /**
     * Send a POST request
     *
     * @param string $type
     * @param array $parameters
     * @return array Data
     */
    public function post($type, array $parameters = array())
    {
        $response = $this->_request($type, $parameters, Varien_Http_Client::POST);
        try {
            $decodedResponse = $this->decodeResponse($response->getBody());
        } catch (Exception $e) {
            Mage::logException($e);
            $decodedResponse = $response->getBody();
        }

        if (self::HTTP_OK !== $response->getStatus()) {
            /** @var $apiHelper Minkasu_Wallet_Helper_Api */
            $apiHelper = Mage::helper('minkasu_wallet/api');
            /** @var $helper Minkasu_Wallet_Helper_Data */
            $helper = Mage::helper('minkasu_wallet');

            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                $error = $decodedResponse['error'];
            } else {
                $error = $decodedResponse;
            }

            Mage::log(
                $helper->__(
                    'API type: %s. Params: %s. HTTP code: %s. Response: %s.',
                    $type, json_encode($parameters), $response->getStatus(), $response->getBody()
                ),
                null,
                $apiHelper->getApiLogFilename()
            );
            Mage::throwException($helper->__('Minkasu API Error: %s.', $error));
        }
        return $decodedResponse;
    }

    /**
     * Send a request to the server, receive a response,
     * decode the response and returns a SimpleXMLElement object or an associative array
     *
     * @param string $type
     * @param array $parameters
     * @param string $httpMethod
     * @return Zend_Http_Response
     */
    protected function _request($type, array $parameters = array(), $httpMethod = Varien_Http_Client::GET)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');

        $url = strtr($this->_options['url'], array(
            ':url'  => $apiHelper->getApiGatewayUrl(),
            ':type' => $type,
        ));
        if (isset($parameters[':id'])) {
            $url = str_replace(':id', $parameters[':id'], $url);
            unset($parameters[':id']);
        } else {
            $url = str_replace(':id', '', $url);
        }
        if (isset($parameters[':action'])) {
            $url = str_replace(':action', $parameters[':action'], $url);
            unset($parameters[':action']);
        } else {
            $url = str_replace(':action', '', $url);
        }
        $url = rtrim($url, '/');

        return $this->_doRequest($url, $parameters, $httpMethod);
    }

    /**
     * Get encoded response and transform it to a PHP array or a SimpleXMLElement object
     *
     * @param string $response
     * @return array|SimpleXMLElement
     */
    public function decodeResponse($response)
    {
        /** @var $format Minkasu_Wallet_Model_Api_Format_Abstract */
        $format = Mage::getSingleton("minkasu_wallet/api_format_{$this->_options['format']}");

        if (false === $format || !$format instanceof Minkasu_Wallet_Model_Api_Format_Abstract) {
            Mage::throwException("Format '{$this->_options['format']}' not found.");
        }

        return $format->decodeResponse($response);
    }

    /**
     * Change an option value.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return Minkasu_Wallet_Model_Api_Adapter_Abstract
     */
    public function setOption($name, $value)
    {
        $this->_options[$name] = $value;

        return $this;
    }
}
