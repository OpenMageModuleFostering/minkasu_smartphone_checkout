<?php
class Minkasu_Wallet_Helper_Api extends Mage_Core_Helper_Data
{
    /**
     * Xml paths
     */
    const XML_PATH_API_GATEWAY_URL         = 'payment/minkasu_wallet/gateway_url';
    const XML_PATH_API_ACCOUNT_ID          = 'payment/minkasu_wallet/account_id';
    const XML_PATH_API_TOKEN               = 'payment/minkasu_wallet/token';
    const XML_PATH_API_ACTIVE              = 'payment/minkasu_wallet/active';
    const XML_PATH_API_MODE                = 'payment/minkasu_wallet/mode';
    const XML_PATH_API_ESTIMATE_ENABLED    = 'payment/minkasu_wallet/estimate_enabled';
    const XML_PATH_API_CC_TYPES            = 'payment/minkasu_wallet/cctypes';
    const XML_PATH_API_PAYMENTACTION       = 'payment/minkasu_wallet/payment_action';
    const XML_PATH_API_PROMO_CODES	   = 'payment/minkasu_wallet/promo_codes';

    /**
     * Minkasu API log filename
     */
    const LOG_FILENAME = 'minkasu_api.log';

    /**
     * @return string
     */
    public function getApiGatewayUrl()
    {
        return rtrim(Mage::getStoreConfig(self::XML_PATH_API_GATEWAY_URL), '/');
    }

    /**
     * @return int
     */
    public function getApiAccountId()
    {
        $configValue = Mage::getStoreConfig(self::XML_PATH_API_ACCOUNT_ID);
        $configValue = json_decode($configValue, true);

        return isset($configValue[$this->getApiMode()]) ? (int) $configValue[$this->getApiMode()] : '';
    }

    /**
     * @return int
     */
    public function getApiMode()
    {
        return (int) Mage::getStoreConfigFlag(self::XML_PATH_API_MODE);
    }

    /**
     * @return string
     */
    public function getApiAccountIds()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_ACCOUNT_ID);
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        $configValue = Mage::getStoreConfig(self::XML_PATH_API_TOKEN);
        $configValue = json_decode($configValue, true);

        return isset($configValue[$this->getApiMode()]) ? $configValue[$this->getApiMode()] : '';
    }

    /**
     * @return string
     */
    public function getApiTokens()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_TOKEN);
    }

    /**
     * @return string
     */
    public function getPaymentaction()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_PAYMENTACTION);
    }

    /**
     * @param string $accountId
     *
     * @return $this
     */
    public function saveApiAccountId($accountId)
    {
        $this->_saveConfig(self::XML_PATH_API_ACCOUNT_ID, json_encode(array($this->getApiMode() => $accountId)));
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    protected function _saveConfig($key, $value)
    {
        /** @var $config Mage_Core_Model_Config */
        $config = Mage::getModel('core/config');
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');
        $config->saveConfig($key, $coreHelper->encrypt($value));
        return $this;
    }
    /**
     * @param string $paymentAction
     *
     * @return $this
     */
    public function savePaymentaction($paymentAction)
    {
        /** @var $config Mage_Core_Model_Config */
        $config = Mage::getModel('core/config');
        $config->saveConfig(self::XML_PATH_API_PAYMENTACTION, $paymentAction);
        return $this;
    }

    /**
     * @param string $ccTypes
     *
     * @return $this
     */
    public function saveCcTypes($ccTypes)
    {
        /** @var $config Mage_Core_Model_Config */
        $config = Mage::getModel('core/config');
        $config->saveConfig(self::XML_PATH_API_CC_TYPES, $ccTypes);
        return $this;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function saveApiToken($token)
    {
        $this->_saveConfig(self::XML_PATH_API_TOKEN, json_encode(array($this->getApiMode() => $token)));
        return $this;
    }

    /**
     * @return string
     */
    public function getApiLogFilename()
    {
        return self::LOG_FILENAME;
    }

    /**
     * @return bool
     */
    public function isApiActive()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_API_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isApiEstimateEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_API_ESTIMATE_ENABLED);
    }
}
