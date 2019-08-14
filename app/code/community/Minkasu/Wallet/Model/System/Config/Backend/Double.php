<?php

class Minkasu_Wallet_Model_System_Config_Backend_Double extends Mage_Adminhtml_Model_System_Config_Backend_Encrypted
{
    /**
     * {@inheritdoc}
     */
    protected function _beforeSave()
    {
        $currentMode = Mage::registry('current_minkasu_mode');

        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');
        $oldValue = $this->getOldValue();
        $oldValue = $coreHelper->decrypt($oldValue);
        if ($oldValue) {
            $value = json_decode($oldValue);
            $value[$currentMode] = (string) $this->getValue();
        } else {
            $value = array($currentMode => (string) $this->getValue());
        }
        $this->setValue(json_encode($value));
        parent::_beforeSave();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getValue()) {
            /** @var $apiHelper Minkasu_Wallet_Helper_Api */
            $apiHelper = Mage::helper('minkasu_wallet/api');
            $value = json_decode($this->getValue(), true);
            $mode = $apiHelper->getApiMode();
            if (isset($value[$mode])) {
                $this->setValue($value[$mode]);
            }
        }
    }
}
