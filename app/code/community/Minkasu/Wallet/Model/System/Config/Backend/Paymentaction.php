<?php

class Minkasu_Wallet_Model_System_Config_Backend_Paymentaction extends Mage_Core_Model_Config_Data
{
    /**
     * {@inheritdoc}
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

	if (false === $this->isValueChanged()) {
            return $this;
        }
        try {
            /** @var $apiHelper Minkasu_Wallet_Helper_Api */
            $apiHelper = Mage::helper('minkasu_wallet/api');
            if (!$apiHelper->getApiAccountId() || !$apiHelper->getApiToken()) {
                Mage::throwException($this->_getHelper()->__('Your Minkasu credentials are empty.'));
            }

            /** @var $merchantHelper Minkasu_Wallet_Helper_Api_Merchant */
            $merchantHelper = Mage::helper('minkasu_wallet/api_merchant');
            /** @var $client Minkasu_Wallet_Model_Api_Client */
            $client = Mage::getModel('minkasu_wallet/api_client');
            $result = $client->getType('merchant')->updatePaymentaction($this->getValue());            
            if (isset($result['status']) && 'success' === $result['status']) {
                $this->_getSession()->addSuccess(
                    $this->_getHelper()->__('You have succesfully updated Payment Action.')
                );
            } else {
                $this->_getSession()->addError(
                    $this->_getHelper()->__('An error occurred during saving Payment Action.')
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
        }
        return $this;
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @return Minkasu_Wallet_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('minkasu_wallet');
    }
}
