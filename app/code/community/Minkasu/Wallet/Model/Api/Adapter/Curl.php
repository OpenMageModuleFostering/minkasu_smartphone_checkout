<?php

class Minkasu_Wallet_Model_Api_Adapter_Curl extends Minkasu_Wallet_Model_Api_Adapter_Abstract
{
    /**
     * Send a request to the server, receive a response
     *
     * @param string $url
     * @param array $parameters
     * @param string $httpMethod
     * @return Zend_Http_Response
     */
    protected function _doRequest($url, array $parameters = array(), $httpMethod = Varien_Http_Client::GET)
    {
        $client = new Varien_Http_Client();
        $client->setConfig(array(
            'timeout' => self::CLIENT_TIMEOUT,
        ));
        $client->resetParameters(true);
        $client->setUri($url);
        $client->setMethod($httpMethod);

        if (isset($parameters['headers'])) {
            $client->setConfig(array('strict' => false));
            $client->setHeaders($parameters['headers']);
            unset($parameters['headers']);
        }

        if (Varien_Http_Client::GET == $httpMethod) {
            $client->setParameterGet($parameters);
        } elseif (Varien_Http_Client::POST == $httpMethod) {
            $client->setParameterPost($parameters);
        } else {
            Mage::throwException("Invalid http request method '{$httpMethod}'.");
        }

        return $client->request();
    }
}
