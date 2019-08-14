<?php

class Minkasu_Wallet_Block_Adminhtml_System_Config_Frontend_MinkasuMode
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Override method to output our custom HTML with JavaScript
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        // Get the default HTML for this option
        $html = parent::_getElementHtml($element);
        // Set up additional JavaScript for our toggle action. Note we are using the two helper methods above
        // to get the correct field ID's. They are hard-coded and depend on your option names in system.xml
        $javaScript = "
            <script type=\"text/javascript\">
                var accountApiIds = {$this->_getApiAccountIds()},
                    accountTokens = {$this->_getApiTokens()};

                Event.observe(window, 'load', function() {});
                Event.observe('{$element->getHtmlId()}', 'change', function() {
                    var enabled = $('{$element->getHtmlId()}').value,
                        gatewayUrl = $('payment_minkasu_wallet_gateway_url');

                    if(enabled == 0) {
                        gatewayUrl.value = 'https://transactions.minkasu.com';
                    } else {
                        gatewayUrl.value = 'https://sb.minkasu.com';
                    }
                    $('payment_minkasu_wallet_account_id').value = (accountApiIds[enabled] == undefined ? '' : accountApiIds[enabled]);
                    $('payment_minkasu_wallet_token').value = (accountTokens[enabled] == undefined ? '' : accountTokens[enabled]);
                    gatewayUrl.focus();
                 });
            </script>";
 
        $html .= $javaScript;
        return $html;
    }

    /**
     * @return string
     */
    protected function _getApiAccountIds()
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        $apiAccountIds = $apiHelper->getApiAccountIds();
        return $apiAccountIds ? $apiAccountIds : '{}';
    }

    /**
     * @return string
     */
    protected function _getApiTokens()
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        $apiTokens = $apiHelper->getApiTokens();
        return $apiTokens ? $apiTokens : '{}';
    }
}
