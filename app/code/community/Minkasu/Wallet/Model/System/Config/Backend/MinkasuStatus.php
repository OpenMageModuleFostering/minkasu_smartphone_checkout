<?php

class Minkasu_Wallet_Model_System_Config_Backend_MinkasuStatus extends Mage_Core_Model_Config_Data
{
    /**
     * {@inheritdoc}
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        if (false === $this->isValueChanged()) {
            return $this;
        }
        try {
            /** @var $apiHelper Minkasu_Wallet_Helper_Api */
            $apiHelper = Mage::helper('minkasu_wallet/api');
            if (!$apiHelper->getApiAccountId() || !$apiHelper->getApiToken()) {
                Mage::throwException($this->_getHelper()->__('Your Minkasu credentials are empty.'));
            }

            /** @var $client Minkasu_Wallet_Model_Api_Client */
            $client = Mage::getModel('minkasu_wallet/api_client');
            if ('1' === $this->getValue()) {
                $result = $client->getType('merchant')->activateMerchant();
                $message = 'You have succesfully activated your Minkasu Account.';
            } else {
                $result = $client->getType('merchant')->deactivateMerchant();
                $message = 'You have succesfully deactivated your Minkasu Account.'
                    . 'To enable it, open the Minkasu section and set Enabled=Yes.';
            }
            if (null === $result) {
                $this->_getSession()->addSuccess(
                     $this->_getHelper()->__($message)
                );
            } else {
                $this->_getSession()->addError(
                    $this->_getHelper()->__('An error occurred during activating your Minkasu account.')
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
