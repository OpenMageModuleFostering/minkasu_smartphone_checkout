<?php

class Minkasu_Wallet_Model_Api_Type_Transaction extends Minkasu_Wallet_Model_Api_Type_Abstract
{
    /**
     * Field names
     */
    const FIELD_NAME_PAYMENT_CODE = 'payment_code';
    const FIELD_NAME_STATUS = 'status';
    const FIELD_NAME_ACTION = 'act';

    /**
     * Operations with a transaction
     */
    const OPERATION_CREATE    = 'new_transaction';
    const OPERATION_UPDATE    = 'update_transaction_details';
    const OPERATION_AUTHORIZE = 'authorize';
    const OPERATION_CAPTURE   = 'capture';
    const OPERATION_CANCEL    = 'cancel';
    const OPERATION_REFUND    = 'refund';
    const OPERATION_VOID      = 'void';

    /**
     * Type API name
     */
    const API_NAME = 'transactions';

    /**
     * Transaction states
     */
    const STATE_CREATED = 0;
    const STATE_AUTHORIZED = 1;
    const STATE_CAPTURED = 2;
    const STATE_CANCELED = 3;
    const STATE_CREDITED = 4;
    const STATE_PREAUTHED = 5;
    const STATE_PREAUTH_VOIDED = 6;

    /**
     * Get payment code of a transaction id
     *
     * @param int $transactionId
     * @return array
     */
    public function getPaymentCode($transactionId)
    {
        $params = array(
            ':id' => $transactionId,
            ':field' => self::FIELD_NAME_PAYMENT_CODE,
        );
        return $this->get($params);
    }

    /**
     * Create a Minkasu transaction
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function createTransaction(Mage_Sales_Model_Quote $quote, array $expectedShippingInfo)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        /** @var $walletHelper Minkasu_Wallet_Helper_Data */
        $walletHelper = Mage::helper('minkasu_wallet');

        $params = array(
            'operation' => self::OPERATION_CREATE,
            'amount' => $walletHelper->convertDollarsToCents($quote->getGrandTotal()),
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'merchant_bill_number' => -1, // Always initialize with invalid number. Will update later with order id when available
            'minkasu_token' => $apiHelper->getApiToken(),
            'beta_code' => 'mkbeta3314'
        );

	if ($expectedShippingInfo) {
	   $params['expected_shipping_info'] = $expectedShippingInfo;
	}

        return $this->post($params);
    }

    /**
     * Update a Minkasu transaction
     *
     * @param $transactionId
     * @param array $transactionData
     * @return array
     */
    public function updateTransaction($transactionId, array $transactionData, array $expectedShippingInfo)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');

        $params = array(
            'operation' => self::OPERATION_UPDATE,
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken(),
            'beta_code' => 'mkbeta3314',
            ':id' => $transactionId,
        );
        $params = array_merge($params, $transactionData);
	if ($expectedShippingInfo != NULL) {
	   $params['expected_shipping_info'] = $expectedShippingInfo;
	}

        return $this->post($params);
    }

    /**
     * Authorize a Minkasu transaction
     *
     * @param string $transactionId
     * @param string $paymentToken
     *
     * @return array
     */
    public function authorizeTransaction($transactionId, $paymentToken, $amount, $orderId)
    {
	/** @var $walletHelper Minkasu_Wallet_Helper_Data */
        $walletHelper = Mage::helper('minkasu_wallet');
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');

        $params = array(
            'operation' => self::OPERATION_AUTHORIZE,
	    'authorize_amount' => $walletHelper->convertDollarsToCents($amount),
	    'merchant_bill_number' => $orderId,  
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken(),
            'payment_token' => $paymentToken,
            ':id' => $transactionId,
            ':action' => self::FIELD_NAME_ACTION,
        );

        return $this->post($params);
    }

    /**
     * Capture a Minkasu transaction
     *
     * @param string $transactionId
     *
     * @return array
     */
    public function captureTransaction($transactionId, $amount)
    {

	/** @var $walletHelper Minkasu_Wallet_Helper_Data */
        $walletHelper = Mage::helper('minkasu_wallet');
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');

        $params = array(
            'operation' => self::OPERATION_CAPTURE,
	    'capture_amount' => $walletHelper->convertDollarsToCents($amount),
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken(),
            ':id' => $transactionId,
            ':action' => self::FIELD_NAME_ACTION,
        );

        return $this->post($params);
    }

    /**
     * Cancel a Minkasu transaction
     *
     * @param string $transactionId
     *
     * @return array
     */
    public function cancelTransaction($transactionId)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');

        $params = array(
            'operation' => self::OPERATION_CANCEL,
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken(),
            ':id' => $transactionId,
            ':action' => self::FIELD_NAME_ACTION,
        );

        return $this->post($params);
    }

    /**
     * Refund a Minkasu transaction
     *
     * @param string $transactionId
     *
     * @return array
     */
    public function refundTransaction($transactionId, $amount)
    {
        /** @var $walletHelper Minkasu_Wallet_Helper_Data */
        $walletHelper = Mage::helper('minkasu_wallet');
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');

        $params = array(
            'operation' => self::OPERATION_REFUND,
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken(),
            'refund_amount' => $walletHelper->convertDollarsToCents($amount),
            ':id' => $transactionId,
            ':action' => self::FIELD_NAME_ACTION,
        );

        return $this->post($params);
    }

    /**
     * Get a Minkasu transaction
     *
     * @param string $transactionId
     *
     * @return array
     */
    public function getTransaction($transactionId)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');

        $url = sprintf('%s/%s/%s', $apiHelper->getApiGatewayUrl(), $this->_getApiName(), $transactionId);
        $data = array(
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken()
        );
        $query = http_build_query($data);
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($query),
                'method'  => Varien_Http_Client::GET,
                'content' => $query,
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result, true);
    }

    /**
     * Void a Minkasu transaction
     *
     * @param string $transactionId
     *
     * @return array
     */
    public function voidTransaction($transactionId)
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');

        $params = array(
            'operation' => self::OPERATION_VOID,
            'merchant_acct_id' => $apiHelper->getApiAccountId(),
            'minkasu_token' => $apiHelper->getApiToken(),
            ':id' => $transactionId,
            ':action' => self::FIELD_NAME_ACTION,
        );

        return $this->post($params);
    }

    /**
     * @param string $transactionId
     *
     * @return array
     */
    public function getTransactionStatus($transactionId)
    {
        $params = array(
            ':id' => $transactionId,
            ':action' => self::FIELD_NAME_STATUS
        );

        return $this->get($params);
    }

    /**
     * @return string
     */
    protected function _getApiName()
    {
        return self::API_NAME;
    }
}
