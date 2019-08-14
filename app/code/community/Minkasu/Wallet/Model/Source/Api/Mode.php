<?php

class Minkasu_Wallet_Model_Source_Api_Mode
{
    /**
     * Available modes
     */
    const MODE_SANDBOX = 1;
    const MODE_LIVE    = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::MODE_SANDBOX => Mage::helper('minkasu_wallet')->__('Sandbox'),
            self::MODE_LIVE    => Mage::helper('minkasu_wallet')->__('Live'),
        );
    }
}
