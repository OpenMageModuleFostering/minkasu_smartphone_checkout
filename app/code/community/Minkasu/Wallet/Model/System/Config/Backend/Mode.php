<?php

class Minkasu_Wallet_Model_System_Config_Backend_Mode extends Mage_Core_Model_Config_Data
{
    /**
     * {@inheritdoc}
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        Mage::register('current_minkasu_mode', $this->getValue() ? $this->getValue() : $this->getOldValue());
    }
}
