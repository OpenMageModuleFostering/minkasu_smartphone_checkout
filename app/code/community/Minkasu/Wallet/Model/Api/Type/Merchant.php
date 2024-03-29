<?php

class Minkasu_Wallet_Model_Api_Type_Merchant extends Minkasu_Wallet_Model_Api_Type_Abstract
{
    /**
     * Type API name
     */
    const API_NAME = 'merchant';

    /**
     * Supported gateways
     */
    const GATEWAY_AUTHORIZENET = 'AuthorizeNet';
    const GATEWAY_AUTHORIZENET_SANDBOX = 'AuthorizeNetSandBox';

    /**
     * Operations with a merchant
     */
    const OPERATION_CREATE = 'new_merchant_signup';

    /**
     * Merchant channel name
     */
    const CHANNEL_NAME = 'magento';

    /**
     * Merchant statuses
     */
    const STATUS_ACTIVE = 'A';
    const STATUS_INACTIVE = 'I';

    /**
     * @return string
     */
    protected function _getApiName()
    {
        return self::API_NAME;
    }

    /**
     * Create a Minkasu merchant
     *
     * @param array $merchantData
     * @param array $gatewayData
     *
     * @return array
     */
    public function createMerchant(array $merchantData, array $gatewayData )
    {
        $params = array_merge(
            $merchantData,
            array(
                'operation' => self::OPERATION_CREATE,
                'generate_token' => true,
                'channel' => self::CHANNEL_NAME,
                'update_gw_credentials' => true,
                'username' => '',
                'passwd' => '',
            )
        );
        $params['gw_details'] = $gatewayData;

        return $this->post($params);
    }
    /**
     * Update merchant card_acceptance list
     *
     * @param array $cardAcceptanceData
     *
     * @return success/error
     */
    public function updateCardAcceptanctData(array $cardAcceptanceData)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        $params = array(
            'cards_accepted' => $cardAcceptanceData
        );
        return $this->post($params);
    }
    

    /**
     * Create a Minkasu merchant gateway
     *
     * @param array $gatewayData
     *
     * @return array
     */
    public function updateMerchantGateway(array $gatewayData)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        $params = array(
            'login_id' => $gatewayData['login_id'],
            'key' => $gatewayData['key'],
            'test_mode' => $gatewayData['test_mode'],
            ':id' => $apiHelper->getApiAccountId(),
            ':action' => 'gateway',
            'headers' => array(
                'merchant_acct_id' => $apiHelper->getApiAccountId(),
                'minkasu_token' => $apiHelper->getApiToken(),
            ),
        );
        return $this->post($params);
    }
    
    /**
     * @return array
     */
    public function getMerchantStatus()
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        $params = array(
            ':id' => $apiHelper->getApiAccountId(),
            ':action' => 'status',
            'headers' => array(
                'merchant_acct_id' => $apiHelper->getApiAccountId(),
                'minkasu_token' => $apiHelper->getApiToken(),
            ),
        );
        return $this->get($params);
    }

    /**
     * @return array
     */
    public function activateMerchant()
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        $params = array(
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken(),
            'status' => self::STATUS_ACTIVE,
            ':id' => $apiHelper->getApiAccountId(),
            ':action' => 'status',
        );
        return $this->post($params);
    }

    /**
     * @return array
     */
    public function deactivateMerchant()
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        $params = array(
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken(),
            'status' => self::STATUS_INACTIVE,
            ':id' => $apiHelper->getApiAccountId(),
            ':action' => 'status',
        );
        return $this->post($params);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function updatePreference(array $data)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        $params = array(
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken(),
            ':id' => $apiHelper->getApiAccountId(),
            ':action' => 'preference',
        );
        $params = array_merge($params, $data);
        return $this->post($params);
    }

    /**
     * @return array
     */
    public function enableAddressPoBox()
    {
        $params = array(
            'addressPOBoxEnabled' => true
        );
        return $this->updatePreference($params);
    }

    /**
     * @return array
     */
    public function disableAddressPoBox()
    {
        $params = array(
            'addressPOBoxEnabled' => false
        );
        return $this->updatePreference($params);
    }

    /**
     * @param array $cc
     * @return array
     */
    public function updateCc(array $cc)
    {
        $params = array(
            'cards_accepted' => $cc
        );
        return $this->updatePreference($params);
    }
    /**
     * @param string $paymentAction
     * @return array
     */
    public function updatePaymentaction(string $paymentAction)
    {
        //'capture_immediate' => $paymentAction
        $boolean_capture_immediate = ($paymentAction == 'authorize_capture' ? true : false);
        $params = array(
            'capture_immediate' => $boolean_capture_immediate
        );
        return $this->updatePreference($params);
    }

}
