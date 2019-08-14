<?php

class Minkasu_Wallet_Block_Adminhtml_System_Config_Frontend_MinkasuGroup
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * {@inheritdoc}
     */
    protected function _getHeaderCommentHtml($element)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        if ($apiHelper->getApiAccountId() && $apiHelper->getApiToken()) {
            $linkName = 'Edit your Minkasu account';
        } else {
            $linkName = 'Create a Minkasu account';
        }
        $element->comment = sprintf($element->comment, $apiHelper->__($linkName));
        return parent::_getHeaderCommentHtml($element);
    }
}
