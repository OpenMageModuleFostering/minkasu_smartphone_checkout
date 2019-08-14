<?php
class Minkasu_Wallet_Block_Adminhtml_System_Config_Frontend_MinkasuMode extends Mage_Adminhtml_Block_System_Config_Form_Field
{
 
    /**
     * Get element ID of the dependent field to toggle
     *
     * @param object $element
     * @return String
     */
    protected function _getToggleElementId($element)
    {
	return "payment_minkasu_wallet_gateway_url";
        //return substr($element->getId(), 0, strrpos($element->getId(), 'gateway_url')) . 'active';
    }
    /**
     * Get element ID of the dependent field's parent row
     *
     * @param object $element
     * @return String
     */
    protected function _getToggleRowElementId($element)
    {
        return 'row_'.$this->_getToggleElementId($element);
    }
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
        //alert('{$element->getHtmlId()}'+ '='+ enabled);
        $javaScript = "
            <script type=\"text/javascript\">
                Event.observe(window, 'load', function() {});
                Event.observe('{$element->getHtmlId()}', 'change', function(){
                enabled=$('{$element->getHtmlId()}').value;
                if(enabled == 0) {
                    $('{$this->_getToggleElementId($element)}').value = 'https://transactions.minkasu.com';
                }else {
                    $('{$this->_getToggleElementId($element)}').value = 'http://sb.minkasu.com';
                }
                $('{$this->_getToggleElementId($element)}').focus();
            });
            </script>";
 
        $html .= $javaScript;
        return $html;
    }
}
