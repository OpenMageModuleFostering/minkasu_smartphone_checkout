<?php

class Minkasu_Wallet_Block_Adminhtml_Merchant_Create_Success extends Mage_Adminhtml_Block_Template
{
    /**
     * @var Minkasu_Wallet_Helper_Api
     */
    protected $_apiHelper;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_apiHelper = Mage::helper('minkasu_wallet/api');
    }

    /**
     * @return int
     */
    public function getApiAccountId()
    {
        return $this->_apiHelper->getApiAccountId();
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->_apiHelper->getApiToken();
    }
}
