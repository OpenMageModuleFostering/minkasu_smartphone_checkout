<?php

class Minkasu_Wallet_Model_Source_Cctype extends Mage_Payment_Model_Source_Cctype
{
    /**
     * @var array
     */
    protected $_ccTypes = array('VI', 'MC', 'AE', 'DN', 'DI', 'JCB', 'CUP');

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        $authNetCcTypes = $this->_getAuthNetCcTypes();
        $ccTypes = array();

        foreach ($this->_ccTypes as $ccType) {
            if (in_array($ccType, $authNetCcTypes)) {
                $ccTypes[] = $ccType;
            }
        }

        return $ccTypes;
    }

    /**
     * @return array
     */
    protected function _getAuthNetCcTypes()
    {
        /** @var $paygate Mage_Paygate_Model_Authorizenet */
        $paygate = Mage::getModel('paygate/authorizenet');
        $authNetCcTypes = $paygate->getConfigData('cctypes');
        $authNetCcTypes = explode(',', $authNetCcTypes);
        return $authNetCcTypes;
    }
}
