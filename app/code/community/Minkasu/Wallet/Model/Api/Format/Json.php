<?php

class Minkasu_Wallet_Model_Api_Format_Json extends Minkasu_Wallet_Model_Api_Format_Abstract
{
    /**
     * Decode a json response to a PHP array
     *
     * @param string $response
     * @return array
     */
    public function decodeResponse($response)
    {
        /** @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');
        return $helper->jsonDecode($response);
    }
}
