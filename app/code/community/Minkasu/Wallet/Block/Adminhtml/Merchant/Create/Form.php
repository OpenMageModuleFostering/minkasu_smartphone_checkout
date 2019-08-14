<?php

class Minkasu_Wallet_Block_Adminhtml_Merchant_Create_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $generalFieldset = $form->addFieldset('general', array('legend' => $this->__('General Information')));
        $generalFieldset->addField('name', 'text', array(
            'label' => $this->__('Store Name'),
            'name'  => 'name',
            'required' => true,
        ));
        $generalFieldset->addField('email', 'text', array(
            'label' => $this->__('Email'),
            'name'  => 'email',
            'class' => 'validate-email',
            'required' => true,
        ));
        $generalFieldset->addField('phone', 'text', array(
            'label' => $this->__('Phone'),
            'name'  => 'phone',
            'required' => false,
        ));
        $generalFieldset->addField('authNet_api_login_id', 'text', array(
            'label' => $this->__('Your Authorize.net API Login ID'),
            'name'  => 'authNet_api_login_id',
            'required' => true,
        ));
        $generalFieldset->addField('authNet_transaction_key', 'text', array(
            'label' => $this->__('Your Authorize.net Transaction Key'),
            'name'  => 'authNet_transaction_key',
            'required' => true,
        ));
        $generalFieldset->addField('authNet_gateway', 'select', array(
            'label' => $this->__('Authorize.net Test Mode'),
            'name' => 'authNet_gateway',
            'options' => array(
                Minkasu_Wallet_Model_Api_Type_Merchant::GATEWAY_AUTHORIZENET_SANDBOX => $this->__('Yes'),
                Minkasu_Wallet_Model_Api_Type_Merchant::GATEWAY_AUTHORIZENET => $this->__('No'),
            ),
            'required' => true,
        ));
        $generalFieldset->addField('terms', 'checkbox', array(
            'label' => $this->__('Terms & Conditions'),
            'note'  => $this->__(
                'I agree to the Minkasu <a href="%s" target="_blank">Terms & Conditions</a>',
                'https://transactions.minkasu.com/terms/merchant.html'
            ),
            'name'     => 'terms',
            'onclick'  => 'this.value = this.checked ? 1 : 0;',
            'required' => true,
        ));

        /** @var $adminhtmlSession Mage_Adminhtml_Model_Session */
        $adminhtmlSession = Mage::getSingleton('adminhtml/session');
        $merchantDetails = $adminhtmlSession->getData('minkasu_merchant_details', true);
        if ($merchantDetails) {
            $form->setValues($merchantDetails);
        } else {
            $store = Mage::app()->getStore();
            /** @var $paygate Mage_Paygate_Model_Authorizenet */
            $paygate = Mage::getModel('paygate/authorizenet');
            /** @var $helper Minkasu_Wallet_Helper_Data */
            $helper = Mage::helper('minkasu_wallet');
            $form->setValues(array(
                'name' => Mage::app()->getStore()->getFrontendName(),
                'phone' => $helper->getStorePhone($store),
                'email' => $helper->getGeneralEmail($store),
                'authNet_api_login_id' => $paygate->getConfigData('login'),
                'authNet_transaction_key' => $paygate->getConfigData('trans_key'),
                'authNet_gateway' => $this->_getAuthorizeNetGateway((bool) $paygate->getConfigData('test')),
            ));
        }
        return parent::_prepareForm();
    }

    /**
     * @param bool $isTestMode
     *
     * @return string
     */
    protected function _getAuthorizeNetGateway($isTestMode)
    {
        if ($isTestMode) {
            return Minkasu_Wallet_Model_Api_Type_Merchant::GATEWAY_AUTHORIZENET_SANDBOX;
        } else {
            return Minkasu_Wallet_Model_Api_Type_Merchant::GATEWAY_AUTHORIZENET;
        }
    }
}
