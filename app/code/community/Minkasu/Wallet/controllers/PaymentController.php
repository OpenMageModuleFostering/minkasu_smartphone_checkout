<?php

class Minkasu_Wallet_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Make a payment
     */
    public function paidAction()
    {
        /** @var $coreResource Mage_Core_Model_Resource */
        $coreResource = Mage::getSingleton('core/resource');
        $writeAdapter = $coreResource->getConnection('core_write');

        // Step 1: Get the current quote object
        /** @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');
        $quote = $checkoutSession->getQuote();

        try {
            $writeAdapter->beginTransaction();

            $minkasuTransactionId = $checkoutSession->getData('minkasu_txn_id');
            $transactionInfoResponse = $this->_getMinkasuTransaction($minkasuTransactionId);
            if (!isset($transactionInfoResponse['txn_state']) || $transactionInfoResponse['txn_state']
                != Minkasu_Wallet_Model_Api_Type_Transaction::STATE_PREAUTHED
            ) {
                Mage::throwException('This transaction has wrong state.');
            }

            $customerDetails = $this->_extractCustomerDetails($transactionInfoResponse);
            if (!($customerDetails['customer_details_valid'])) {
                Mage::throwException('Required details not provided with payment.');
            }

            // Step 2: Set the checkout method as "guest
            $quote->setCheckoutMethod('guest')->save();
            // Step 2.1:Fill customer name (first name ?)
            $quote->setCustomerFirstname($customerDetails['customer_name']);
            // Step 3: Fill the address and save it as billing and shipping address
            $addressInfo = $this->_prepareCustomerAddressInfo($customerDetails);
            $this->_saveBillingAndShippingAddress($quote, $addressInfo);

            // Step 4: Shipping method should already be set by now.
            $checkoutSession->setStepData('shipping_method', 'complete', true);

            // Step 5: Set payment method to minkasu wallet
            $this->_savePayment($quote, array('method' => 'minkasu_wallet'));

            // Calculate shipping rates now
            $quote->getShippingAddress()->collectShippingRates()->save();
            $quote->collectTotals()->save();

            $writeAdapter->commit();

            $order = $this->_saveOrder($quote);

            if ($order->getId()) {
                $this->_updateMinkasuTransactionWithOrderId($minkasuTransactionId, $order->getIncrementId());
            } else {
                Mage::throwException($this->__('Order has not been created.'));
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            $writeAdapter->rollback();

            try {
                /** @var $client Minkasu_Wallet_Model_Api_Client */
                $client = Mage::getModel('minkasu_wallet/api_client');
                $client->getType('transaction')->voidTransaction($minkasuTransactionId);
            } catch (Mage_Core_Exception $internalException) {
                Mage::logException($internalException);
            } catch (Exception $internalException) {
                Mage::logException($internalException);
            }

            $this->_cleanCheckoutSessionParams();
            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody($this->_prepareErrorBody($e->getMessage()));
            $this->getResponse()->sendResponse();
            die;
        } catch (Exception $e) {
            Mage::logException($e);
            $writeAdapter->rollback();

            try {
                /** @var $client Minkasu_Wallet_Model_Api_Client */
                $client = Mage::getModel('minkasu_wallet/api_client');
                $client->getType('transaction')->voidTransaction($minkasuTransactionId);
            } catch (Mage_Core_Exception $internalException) {
                Mage::logException($internalException);
            } catch (Exception $internalException) {
                Mage::logException($internalException);
            }

            $this->_cleanCheckoutSessionParams();
            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody(
                $this->_prepareErrorBody('An error has occurred. Please contact store administrator.')
            );
            $this->getResponse()->sendResponse();
            die;
        }

        $url = Mage::getUrl('checkout/onepage/success');
        $this->getResponse()->setHttpResponseCode(201);
        $this->getResponse()->setBody($this->_prepareBody(array('redirect_url' => $url)));
        $this->getResponse()->sendResponse();
        die;
    }

    /**
     * @param string $minkasuTransactionId
     *
     * @return array
     */
    protected function _getMinkasuTransaction($minkasuTransactionId)
    {
        /** @var $client Minkasu_Wallet_Model_Api_Client */
        $client = Mage::getModel('minkasu_wallet/api_client');
        $result = $client->getType('transaction')->getTransaction($minkasuTransactionId);
        return $result;
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function _extractCustomerDetails(array $response)
    {
        $result = array();

        $result['customer_details_valid'] = false;

        if (!isset($response['customer_address']) || (!isset($response['customer_name']))) {
            return $result;
        }

        $result['customer_name'] = $response['customer_name'];
        $result['street_1'] = $response['customer_address']['address_line_1'];
        $result['street_2'] = $response['customer_address']['address_line_2'];
        $result['city'] = $response['customer_address']['city'];
        $result['state'] = $response['customer_address']['state'];
        $result['zip'] = $response['customer_address']['zip'];
        $result['phone'] = $response['phone'];
        $result['email'] = $response['email'];
        $result['customer_details_valid'] = true;

        return $result;
    }

    /**
     * @param array $customerDetails
     *
     * @return array
     */
    protected function _prepareCustomerAddressInfo(array $customerDetails)
    {
        /** @var $regionModel Mage_Directory_Model_Region */
        $regionModel = Mage::getModel('directory/region');
        $regionModel->loadByCode($customerDetails['state'], 'US');

        $customerNameParts = explode(' ', $customerDetails['customer_name']);

        $addressInfo = array();
        $addressInfo['firstname'] = $customerNameParts[0];
        $addressInfo['lastname'] = end($customerNameParts);
        $addressInfo['email'] =  $customerDetails['email'];
        $addressInfo['street']  = array(0 => $customerDetails['street_1'], 1 => $customerDetails['street_2']);
        $addressInfo['city'] = $customerDetails['city'];
        $addressInfo['region_id'] = $regionModel->getId();
        $addressInfo['postcode'] = $customerDetails['zip'];
        $addressInfo['country_id'] = 'US';
        $addressInfo['telephone'] = $customerDetails['phone'];
        $addressInfo['use_for_shipping'] = 1;

        return $addressInfo;
    }

    /**
     * @param $quote
     * @param array $data
     *
     * @return $this
     */
    protected function _saveBillingAndShippingAddress($quote, $data)
    {
        $address = $quote->getBillingAddress();
        /** @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit');
        $addressForm->setEntityType('customer_address');
        $addressForm->setEntity($address);
        // emulate request object
        $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
        $addressErrors  = $addressForm->validateData($addressData);
        if (true !== $addressErrors) {
            Mage::log("Minkasu - address validation failed");
        }
        $addressForm->compactData($addressData);
        //unset billing address attributes which were not shown in form
        foreach ($addressForm->getAttributes() as $attribute) {
            if (!isset($data[$attribute->getAttributeCode()])) {
                $address->setData($attribute->getAttributeCode(), NULL);
            }
        }
        $address->setCustomerAddressId(null);
        $address->setEmail($data['email']);
        $address->implodeStreetAddress();

        $billing = clone $address;
        $billing->unsAddressId()->unsAddressType();

        $shipping = $quote->getShippingAddress();
        $shippingMethod = $shipping->getShippingMethod();

        // Billing address properties that must be always copied to shipping address
        $requiredBillingAttributes = array('customer_address_id');

        // don't reset original shipping data, if it was not changed by customer
        foreach ($shipping->getData() as $shippingKey => $shippingValue) {
            if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
                && !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
            ) {
                $billing->unsetData($shippingKey);
            }
        }

        $shipping->addData($billing->getData());
        $shipping->setSameAsBilling(1);
        $shipping->setSaveInAddressBook(0);
        $shipping->setShippingMethod($shippingMethod);
        $shipping->setCollectShippingRates(true);

        /** @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');
        $checkoutSession->setStepData('shipping', 'complete', true);

        $quote->collectTotals();
        $quote->save();
        $quote->getShippingAddress()->setCollectShippingRates(true);

        $checkoutSession->setStepData('billing', 'allow', true);
        $checkoutSession->setStepData('billing', 'complete', true);
        $checkoutSession->setStepData('shipping', 'allow', true);

        return $this;
    }

    /**
     * @param $quote
     * @param array $data
     *
     * @return $this
     */
    protected function _savePayment($quote, array $data)
    {
        try {
            if($quote->isVirtual()) {
                $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
            } else {
                $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
            }

            // shipping totals may be affected by payment method
            if (!$quote->isVirtual() && $quote->getShippingAddress()) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
            }

            $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;

            /** @var $checkoutSession Mage_Checkout_Model_Session */
            $checkoutSession = Mage::getSingleton('checkout/session');
            $payment = $quote->getPayment();
            $payment->importData($data);
            $payment->setTransactionId($checkoutSession->getData('minkasu_txn_id'));
            $payment->setTransactionAdditionalInfo('quote-no', $quote->getQuoteId());

            $quote->save();
            $checkoutSession->setStepData('payment', 'complete', true);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * @param $quote
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _saveOrder($quote)
    {
        $quote->setCustomerId(null);
        $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
        $quote->setCustomerIsGuest(true);
        $quote->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

        /** @var $service Mage_Sales_Model_Service_Quote */
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();

        /** @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');
        $checkoutSession->setLastQuoteId($quote->getId());
        $checkoutSession->setLastSuccessQuoteId($quote->getId());
        $checkoutSession->clearHelperData();

        $order = $service->getOrder();

        if ($order->getId()) {
            Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$quote));

            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if ($order->getCanSendNewEmailFlag()) {
                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            // add order information to the session
            $checkoutSession->setLastOrderId($order->getId());
            $checkoutSession->setRedirectUrl(null);
            $checkoutSession->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $checkoutSession->setLastBillingAgreementId($agreement->getId());
            }
        }

        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach ($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $checkoutSession->setLastRecurringProfileIds($ids);
        }

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $quote, 'recurring_profiles' => $profiles)
        );

        $quote->save();
        return $order;
    }

    /**
     * @param string $minkasuTransactionId
     * @param string $orderId
     *
     * @return array
     */
    protected function _updateMinkasuTransactionWithOrderId($minkasuTransactionId, $orderId)
    {
        /** @var $client Minkasu_Wallet_Model_Api_Client */
        $client = Mage::getModel('minkasu_wallet/api_client');
        $result = $client->getType('transaction')->updateTransaction($minkasuTransactionId, array('merchant_bill_number' => $orderId), NULL);

        return $result;
    }

    /**
     * @return $this
     */
    protected function _cleanCheckoutSessionParams()
    {
        /** @var $helper Minkasu_Wallet_Helper_Data */
        $helper = Mage::helper('minkasu_wallet');
        $helper->cleanCheckoutSessionParams();
        return $this;
    }

    /**
     * @param string $error
     *
     * @return string
     */
    protected function _prepareErrorBody($error)
    {
        return $this->_prepareBody(array('error' => $this->__($error)));
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function _prepareBody(array $data)
    {
        /** @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');
        return $helper->jsonEncode($data);
    }
}
?>
