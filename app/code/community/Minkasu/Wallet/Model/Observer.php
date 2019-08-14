<?php

class Minkasu_Wallet_Model_Observer
{

   const XML_PATH_API_PROMO_CODES         = 'payment/minkasu_wallet/promo_codes';

   protected function _getMinkasuPromoCodes()
   {
	$promo_codes_str = trim(Mage::getStoreConfig(self::XML_PATH_API_PROMO_CODES));
	$promo_code_arr = explode(',', $promo_codes_str);
	for ($i = 0; $i < count($promo_code_arr); $i++) {
		$promo_code_arr[$i] = trim($promo_code_arr[$i]);
	}
	//Mage::log("Mk promo code str - " . $promo_codes_str . "; Promo codes - " . print_r($promo_code_arr, true));
	return $promo_code_arr;
   }

   protected function _hasMinkasuPromoCodes()
   {
	$promo_codes_str = trim(Mage::getStoreConfig(self::XML_PATH_API_PROMO_CODES));
	if ($promo_codes_str == '') {
		return FALSE;
	}
	return TRUE;
   }

   protected function _getQuotePaymentMethod($quote)
   {
	$method_name = '';
	try {
		$method_name = $quote->getPayment()->getMethodInstance()->getCode();
	}
	catch (Exception $e) {
		// Mage::log("Exception caught while reading payment method name.");
	}
	return $method_name;

   }


   protected function _resetQuotePaymentMethod()
   {

        $checkoutSession = Mage::getSingleton('checkout/session');
	$quote = $checkoutSession->getQuote();

	$initial_payment_method = $this->_getQuotePaymentMethod($quote);
	//Mage::log("Restting payment method - current: " . $initial_payment_method);

        /*
        if($quote->isVirtual()) {
		if ($quote->getBillingAddress()) {
		   $quote->getBillingAddress()->setPaymentMethod('');
		}
        } else {
	       if ($quote->getShippingAddress()) {
                  $quote->getShippingAddress()->setPaymentMethod('');
	       }
        }

        // shipping totals may be affected by payment method
	if (!$quote->isVirtual() && $quote->getShippingAddress()) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
	}
	*/

        $payment = $quote->getPayment();
	if ($payment) {
		$quote->removePayment();
	}
	$final_payment_method = $this->_getQuotePaymentMethod($quote);
	//Mage::log("Restting payment method - new: " . $final_payment_method);

	$quote->setTotalsCollectedFlag(false)->collectTotals();
        $quote->save();
	Mage::log("Payment method reset"); // " . $original_payment_method . " to " . $final_payment_method);
	return;

   }


    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveOrderQuoteToSession(Varien_Event_Observer $observer)
    {
        /** @var Mage_Checkout_Model_Cart $cart */
        $cart = $observer->getEvent()->getCart();
        $checkoutSession = $cart->getCheckoutSession();
        $shippingMethod = $cart->getQuote()->getShippingAddress()->getShippingMethod();
        $checkoutSession->setData('minkasu_shipping_method', $shippingMethod);
        $checkoutSession->setData('minkasu_shipping_estimated', $shippingMethod ? true : false);

        return $this;
    }


    public function beforeSaveOrderQuoteToSession(Varien_Event_Observer $observer)
    {
        /** @var Mage_Checkout_Model_Cart $cart */
        $cart = $observer->getEvent()->getCart();

        $quote = $cart->getQuote();
        //Mage::log('BeforeSaveOrderQuoteToSession called with coupon code ' . $quote->getCouponCode());

        return $this;
    }

/*
    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
	$method = $observer->getEvent()->getMethodInstance();

        //Mage::log('PaymentMethodIsActive called with coupon code ' . $quote->getCouponCode() . ' and method ' . $method->getCode());

        return $this;
    }
*/


    public function salesRuleValidatorProcess(Varien_Event_Observer $observer)
    {

        $quote = $observer->getQuote();
	$current_payment_method = $this->_getQuotePaymentMethod($quote);

        Mage::log('SalesRuleValidatorProcess called with coupon code ' . $quote->getCouponCode() . " and payment " . $current_payment_method);

	$mk_promo_codes = $this->_getMinkasuPromoCodes();
	$current_coupon_code = $quote->getCouponCode();

	if (($current_coupon_code != '') && (in_array($current_coupon_code, $mk_promo_codes))) {
		if (($current_payment_method != '') && ($current_payment_method != 'minkasu_wallet')) {
		   $quote->setCouponCode('');
		   //$quote->save();
		   $result = $observer['result'];
        	   $result->setDiscountAmount(0);
		   Mage::log('Coupon code not allowed for this payment method. Removing coupon.');		   
		}
	}

        return $this;
    }


    public function predispatchCheckoutCartIndex(Varien_Event_Observer $observer)
    {

	$quote = Mage::getSingleton('checkout/session')->getQuote();
        //Mage::log('predispatchCheckoutCartIndex called with coupon code ' . $quote->getCouponCode());
	if ($this->_hasMinkasuPromoCodes()) { 	
		$this->_resetQuotePaymentMethod();
	}
        return $this;
    }


    /**
     * Show a warning to a customer if he goes miss checkout cart page
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function showNotice(Varien_Event_Observer $observer)
    {

	$quote = Mage::getSingleton('checkout/session')->getQuote();

        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        if (false === $apiHelper->isApiActive()) {
            return $this;
        }

        /** @var Mage_Checkout_OnepageController $controller */
        $controller = $observer->getData('controller_action');
        /** @var $coreSession Mage_Core_Model_Session */
        $coreSession = Mage::getSingleton('core/session');
        $coreSession->addWarning(
            $controller->__(
                'You can <a href="%s">checkout quickly</a> using Minkasu App.',
                Mage::getUrl('checkout/cart')
            )
        );
        return $this;
    }
}
