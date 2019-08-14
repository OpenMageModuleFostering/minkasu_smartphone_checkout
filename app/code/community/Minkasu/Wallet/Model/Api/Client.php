<?php

class Minkasu_Wallet_Model_Api_Client
{
    /**
     * @var Minkasu_Wallet_Model_Api_Adapter_Abstract
     */
    protected $_adapter;

    /**
     * The list of loaded API type instances
     *
     * @var array
     */
    protected $_types = array();

    /**
     * Instantiate a new adapter
     *
     * @param Minkasu_Wallet_Model_Api_Adapter_Abstract $adapter Custom adapter
     */
    public function __construct($adapter = null)
    {
        if (empty($adapter)) {
            $this->setAdapter(Mage::getSingleton('minkasu_wallet/api_adapter_curl'));
        } elseif ($adapter instanceof Minkasu_Wallet_Model_Api_Adapter_Abstract) {
            $this->setAdapter($adapter);
        } else {
            Mage::throwException('Invalid client');
        }
    }

    /**
     * Call any type, GET method
     *
     * @param string $type
     * @param array $parameters
     * @return array
     */
    public function get($type, array $parameters = array())
    {
        return $this->getAdapter()->get($type, $parameters);
    }

    /**
     * Call any type, POST method
     *
     * @param string $type
     * @param array $parameters
     * @return array
     */
    public function post($type, array $parameters = array())
    {
        return $this->getAdapter()->post($type, $parameters);
    }

    /**
     * Get the adapter
     *
     * @return Minkasu_Wallet_Model_Api_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Inject another adapter
     *
     * @param Minkasu_Wallet_Model_Api_Adapter_Abstract $adapter
     * @return Minkasu_Wallet_Model_Api_Client
     */
    public function setAdapter(Minkasu_Wallet_Model_Api_Adapter_Abstract $adapter)
    {
        $this->_adapter = $adapter;

        return $this;
    }

    /**
     * Inject an API type instance
     *
     * @param string $name
     * @param Minkasu_Wallet_Model_Api_Type_Abstract $instance
     *
     * @return Minkasu_Wallet_Model_Api_Client
     */
    public function setType($name, Minkasu_Wallet_Model_Api_Type_Abstract $instance)
    {
        $this->_types[$name] = $instance;

        return $this;
    }

    /**
     * Get any API type instance
     *
     * @param string $name
     * @return Minkasu_Wallet_Model_Api_Type_Abstract
     */
    public function getType($name)
    {
        if (!isset($this->_types[$name])) {
            /** @var $type Minkasu_Wallet_Model_Api_Type_Abstract */
            $type = Mage::getSingleton("minkasu_wallet/api_type_{$name}", array($this));

            if (false === $type || !$type instanceof Minkasu_Wallet_Model_Api_Type_Abstract) {
                Mage::throwException("Type '{$name}' not found.");
            }
            $this->setType($name, $type);
        }

        return $this->_types[$name];
    }
}
