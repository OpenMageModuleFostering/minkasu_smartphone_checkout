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
        return (int) Mage::getStoreConfig(self::XML_PATH_API_ACCOUNT_ID);
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return Mage::getStoreConfig(self::XML_PATH_API_TOKEN);
    }

    /**
     * @param string $accountId
     *
     * @return $this
     */
    public function saveApiAccountId($accountId)
    {
        $this->_saveConfig(self::XML_PATH_API_ACCOUNT_ID, $accountId);
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
     * @param string $token
     *
     * @return $this
     */
    public function saveApiToken($token)
    {
        $this->_saveConfig(self::XML_PATH_API_TOKEN, $token);
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
     * @return int
     */
    public function getApiMode()
    {
        return (int) Mage::getStoreConfigFlag(self::XML_PATH_API_MODE);
    }

    /**
     * @return bool
     */
    public function isApiEstimateEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_API_ESTIMATE_ENABLED);
    }
}
