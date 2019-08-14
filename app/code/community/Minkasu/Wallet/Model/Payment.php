<?php

class Minkasu_Wallet_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'minkasu_wallet';

    protected $_canUseCheckout          = false;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;

    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canRefund               = true;
    protected $_canVoid                 = true;

    public function isAvailable($quote = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableToQuote($quote, $checks)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        /** @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');

        /** @var $client Minkasu_Wallet_Model_Api_Client */
        $client = Mage::getModel('minkasu_wallet/api_client');
        $client->getType('transaction')->authorizeTransaction(
            $checkoutSession->getData('minkasu_txn_id'),
            $checkoutSession->getData('minkasu_payment_token'),
	    $amount
        );

        $payment->setTransactionId($checkoutSession->getData('minkasu_txn_id'));
        $payment->setIsTransactionClosed(0);
        $payment->setTransactionAdditionalInfo('quote number', $checkoutSession->getData('minkasu_bill_number'));

        /** @var $helper Minkasu_Wallet_Helper_Data */
        $helper = Mage::helper('minkasu_wallet');
        $helper->cleanCheckoutSessionParams();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function capture(Varien_Object $payment, $amount)
    {
        /** @var $client Minkasu_Wallet_Model_Api_Client */
        $client = Mage::getModel('minkasu_wallet/api_client');
        $client->getType('transaction')->captureTransaction($payment->getData('parent_transaction_id'), $amount);
        $payment->setIsTransactionClosed(0);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function refund(Varien_Object $payment, $amount)
    {
        /** @var $client Minkasu_Wallet_Model_Api_Client */
        $client = Mage::getModel('minkasu_wallet/api_client');
        $transactionId = str_replace('-capture', '', $payment->getData('parent_transaction_id'));
        $client->getType('transaction')->refundTransaction($transactionId, $amount);
        $payment->setIsTransactionClosed(0);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cancel(Varien_Object $payment)
    {
        /** @var $client Minkasu_Wallet_Model_Api_Client */
        $client = Mage::getModel('minkasu_wallet/api_client');
        $client->getType('transaction')->cancelTransaction($payment->getData('parent_transaction_id'));

        return $this;
    }
}
