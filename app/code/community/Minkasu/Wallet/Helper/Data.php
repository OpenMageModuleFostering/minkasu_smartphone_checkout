<?php

class Minkasu_Wallet_Helper_Data extends Mage_Payment_Helper_Data
{
    /**
     * @param mixed $amount
     *
     * @return int
     */
    public function convertDollarsToCents($amount)
    {
        return $amount * 100;
    }

    /**
     * @param int $amount
     *
     * @return mixed
     */
    public function convertCentsToDollars($amount)
    {
        return $amount / 100;
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getStorePhone($store = null)
    {
        return Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_PHONE, $store);
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getGeneralEmail($store = null)
    {
        return Mage::getStoreConfig('trans_email/ident_general/email', $store);
    }

    /**
     * @return $this
     */
    public function cleanCheckoutSessionParams()
    {
        /** @var $session Mage_Checkout_Model_Session */
        $session = Mage::getSingleton('checkout/session');
        $session->unsetData('minkasu_amount');
        $session->unsetData('minkasu_bill_number');
        $session->unsetData('minkasu_txn_id');
        $session->unsetData('minkasu_payment_token');
        $session->unsetData('minkasu_est_zip');
        $session->unsetData('minkasu_est_state');
        return $this;
    }
}
