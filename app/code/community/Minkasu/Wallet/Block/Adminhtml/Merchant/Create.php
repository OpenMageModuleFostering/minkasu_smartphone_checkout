<?php

class Minkasu_Wallet_Block_Adminhtml_Merchant_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'object_id';
        $this->_blockGroup = 'minkasu_wallet';
        $this->_controller = 'adminhtml_merchant';
        $this->_mode = 'create';
        $this->_headerText = Mage::helper('minkasu_wallet')->__('Minkasu Merchant Create Form');

        $this->_removeButton('reset');
        $this->_removeButton('back');
        $this->_updateButton('save', 'label', Mage::helper('minkasu_wallet')->__('Create'));
    }
}
