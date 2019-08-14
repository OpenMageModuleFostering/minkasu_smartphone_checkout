<?php

abstract class Minkasu_Wallet_Model_Api_Type_Abstract
{
    /**
     * API type request params
     */
    const PARAM_NAME_ACCOUNT_ID = 'merchant_acct_id';
    const PARAM_NAME_TOKEN      = 'minkasu_token';

    /**
     * The client
     *
     * @var Minkasu_Wallet_Model_Api_Client
     */
    protected $_client;

    /**
     * The client
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->_client = array_shift($params);
    }

    /**
     * Call any type, GET method
     *
     * @param array $parameters
     * @return array
     */
    public function get(array $parameters = array())
    {
        return $this->_client->get($this->_getApiName(), $parameters);
    }

    /**
     * Call any type, POST method
     *
     * @param array $parameters
     * @return array
     */
    public function post(array $parameters = array())
    {
        return $this->_client->post($this->_getApiName(), $parameters);
    }

    /**
     * Get API type name
     *
     * @return string
     */
    abstract protected function _getApiName();
}
