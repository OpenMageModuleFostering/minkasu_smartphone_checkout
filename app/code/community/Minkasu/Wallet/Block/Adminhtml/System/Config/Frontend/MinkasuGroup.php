<?php

class Minkasu_Wallet_Block_Adminhtml_System_Config_Frontend_MinkasuGroup
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * {@inheritdoc}
     */
    protected function _getHeaderCommentHtml($element)
    {
        /** @var $helper Minkasu_Wallet_Helper_Data */
        $helper = Mage::helper('minkasu_wallet');
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');

        $doesMerchantExist = $apiHelper->getApiAccountId() && $apiHelper->getApiToken();
        $element->comment = sprintf(
            $element->comment,
            $doesMerchantExist ? 'openEditMerchantPopup()' : 'openCreateMerchantPopup()',
            $apiHelper->__($doesMerchantExist ? 'Edit your Minkasu account' : 'Create a Minkasu account'),
            $helper->getHelpUrl(),
            $apiHelper->__('Help')
        );
        return parent::_getHeaderCommentHtml($element);
    }
}
