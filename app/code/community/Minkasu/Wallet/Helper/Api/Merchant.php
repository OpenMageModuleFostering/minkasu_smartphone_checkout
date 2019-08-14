<?php

class Minkasu_Wallet_Helper_Api_Merchant extends Mage_Core_Helper_Data
{
    /**
     * @var bool
     */
    protected $_isMerchantActive;

    /**
     * @var array
     */
    protected $_magentoToMinkasuCcMap = array(
        'VI' => 'visa',
        'MC' => 'mastercard',
        'AE' => 'amex',
        'DN' => 'dinners',
        'DI' => 'discover',
        'JCB' => 'jcb',
        'CUP' => 'china_union_pay',
    );

    /**
     * @param string $magentoCode
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getMinkasuCcCode($magentoCode)
    {
        if (!isset($this->_magentoToMinkasuCcMap[$magentoCode])) {
            Mage::throwException($this->__("CC with code %s doesn't exist."));
        }
        return $this->_magentoToMinkasuCcMap[$magentoCode];
    }

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
