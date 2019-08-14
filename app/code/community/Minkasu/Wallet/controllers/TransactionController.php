<?php

class Minkasu_Wallet_TransactionController extends Mage_Core_Controller_Front_Action
{
    /**
     * Create a Minkasu transaction
     */
    public function createAction()
    {
        /** @var $cart Mage_Checkout_Model_Cart */
        $cart = Mage::getModel('checkout/cart');
        $quote = $cart->getQuote();
        $checkoutSession = $cart->getCheckoutSession();

	$expectedAddressInfo = NULL;
	$shippingAddress = $quote->getShippingAddress();
	if ($shippingAddress != NULL){
	   $expectedAddress = ($shippingAddress->exportCustomerAddress());
	   if ($expectedAddress != NULL) {
	      $data = $expectedAddress->getData();
          $checkoutSession->setData('minkasu_exp_zip', $data["postcode"]);
          $checkoutSession->setData('minkasu_exp_state', $data["region"]);
	      $expectedAddressInfo = array (
	      	'zip' => $data["postcode"],
		'state' => $data["region"]
	   	);
	   }
	}

        try {
            /** @var $session Mage_Core_Model_Session */
            $session = Mage::getSingleton('core/session');
            if ($this->getRequest()->getParam('form_key') !== $session->getFormKey()) {
                Mage::throwException($this->__('Wrong form key.'));
            }

            /** @var $client Minkasu_Wallet_Model_Api_Client */
            $client = Mage::getModel('minkasu_wallet/api_client');
            $transactionInfo = $client->getType('transaction')->createTransaction($quote, $expectedAddressInfo);

            $checkoutSession->setData('minkasu_amount', $quote->getGrandTotal());
            //TODO:AP:quoteId should be used as idempotency id down-the-road
            //$checkoutSession->setData('minkasu_bill_number', $quote->getId());
            $checkoutSession->setData('minkasu_txn_id', $transactionInfo['txn_id']);
            $checkoutSession->setData('minkasu_payment_token',$transactionInfo['payment_token']);
            $result = array(
                'txn_id' => $transactionInfo['txn_id'],
                'payment_code' => $transactionInfo['payment_code'],
                'payment_code_ttl' => $transactionInfo['payment_code_ttl'],
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $result = array('error' => $e->getMessage());
        }


        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Update a Minkasu transaction
     */
    public function updateAction()
    {
        /** @var $cart Mage_Checkout_Model_Cart */
        $cart = Mage::getModel('checkout/cart');
        $quote = $cart->getQuote();
        $checkoutSession = $cart->getCheckoutSession();

        /** @var $walletHelper Minkasu_Wallet_Helper_Data */
        $walletHelper = Mage::helper('minkasu_wallet');
        $minkasuTransactionData = array('amount' => $walletHelper->convertDollarsToCents($quote->getGrandTotal()));
        $minkasuTransactionId = $checkoutSession->getData('minkasu_txn_id');

	$expectedAddressInfo = NULL;
	$shippingAddress = $quote->getShippingAddress();
	if ($shippingAddress != NULL){
	   $expectedAddress = ($shippingAddress->exportCustomerAddress());
	   if ($expectedAddress != NULL) {
	      $data = $expectedAddress->getData();
          $checkoutSession->setData('minkasu_exp_zip', $data["postcode"]);
          $checkoutSession->setData('minkasu_exp_state', $data["region"]);
	      $expectedAddressInfo = array (
	      	'zip' => $data["postcode"],
		'state' => $data["region"]
	   	);
	   }
	}

        try {
            /** @var $session Mage_Core_Model_Session */
            $session = Mage::getSingleton('core/session');
            if ($this->getRequest()->getParam('form_key') !== $session->getFormKey()) {
                Mage::throwException($this->__('Wrong form key.'));
            }

            if (!$minkasuTransactionId) {
                Mage::throwException($this->__("Transaction doesn't exist."));
            }
            /** @var $client Minkasu_Wallet_Model_Api_Client */
            $client = Mage::getModel('minkasu_wallet/api_client');
            $transactionInfo = $client->getType('transaction')
                ->updateTransaction($minkasuTransactionId, $minkasuTransactionData, $expectedAddressInfo);

            $checkoutSession->setData('minkasu_amount', $quote->getGrandTotal());
            //TODO:AP:quoteId should be used as idempotency id down-the-road
            //$checkoutSession->setData('minkasu_bill_number', $quote->getId());
            $checkoutSession->setData('minkasu_txn_id', $transactionInfo['txn_id']);
	    // Payment token will not be returned on an update transaction.
	    // Overwriting existing token with null causes later auth to fail.
            //$checkoutSession->setData('minkasu_payment_token',$transactionInfo['payment_token']);
            $result = array(
                'txn_id' => $transactionInfo['txn_id'],
                'payment_code' => $transactionInfo['payment_code'],
                'payment_code_ttl' => $transactionInfo['payment_code_ttl'],
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $result = array('error' => $e->getMessage());
        }


        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
