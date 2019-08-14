<?php

class Minkasu_Wallet_Helper_Api_Merchant extends Mage_Core_Helper_Data
{
    /**
     * @var bool
     */
    protected $_isMerchantActive;

    /**
     * @return bool
     */
    public function isMerchantActive()
    {
        if (null === $this->_isMerchantActive) {
            /** @var $client Minkasu_Wallet_Model_Api_Client */
            $client = Mage::getModel('minkasu_wallet/api_client');
            $result = $client->getType('merchant')->getMerchantStatus();

            if (Minkasu_Wallet_Model_Api_Type_Merchant::STATUS_ACTIVE === $result['status']) {
                $this->_isMerchantActive = true;
            } elseif (Minkasu_Wallet_Model_Api_Type_Merchant::STATUS_INACTIVE === $result['status']) {
                $this->_isMerchantActive = false;
            } else {
                Mage::throwException($this->__('Wrong merchant status passed.'));
            }
        }
        return $this->_isMerchantActive;
    }
}
