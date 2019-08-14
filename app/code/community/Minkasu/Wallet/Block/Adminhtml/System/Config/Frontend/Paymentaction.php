<?php

class Minkasu_Wallet_Block_Adminhtml_System_Config_Frontend_Paymentaction
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
       /** @var $apiHelper Minkasu_Wallet_Helper_Api */
       $apiHelper = Mage::helper('minkasu_wallet/api');
       if (!$apiHelper->getApiAccountId() || !$apiHelper->getApiToken()) {
           return '';
       }
       return parent::render($element);
   }
}
