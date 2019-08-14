<?php

class Minkasu_Wallet_Model_Observer
{
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

    /**
     * Show a warning to a customer if he goes miss checkout cart page
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function showNotice(Varien_Event_Observer $observer)
    {
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
