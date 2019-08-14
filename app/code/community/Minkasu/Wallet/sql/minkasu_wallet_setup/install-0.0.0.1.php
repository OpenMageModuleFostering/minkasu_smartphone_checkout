<?php

/** @var $helper Minkasu_Wallet_Helper_Data */
$helper = Mage::helper('minkasu_wallet');
/** @var $notice Mage_AdminNotification_Model_Inbox */
$notice = Mage::getModel('adminnotification/inbox');
$message = $helper->__(
    'You have installed Minkasu_Wallet module. Please open "System" > "Configuration" > "Sales" > "Payment Methods"'
        . ' for creating a merchant account</a>.'
);
$notice->addNotice($message, $message);
