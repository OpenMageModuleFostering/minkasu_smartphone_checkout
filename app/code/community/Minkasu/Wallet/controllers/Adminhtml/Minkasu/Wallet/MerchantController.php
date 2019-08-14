<?php

class Minkasu_Wallet_Adminhtml_Minkasu_Wallet_MerchantController extends Mage_Adminhtml_Controller_Action
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUsedModuleName('Minkasu_Wallet');
    }

    /**
     * Create a Minkasu merchant
     */
    public function createAction()
    {
        /** @var $apiHelper Minkasu_Wallet_Helper_Api */
        $apiHelper = Mage::helper('minkasu_wallet/api');
        $hasMinkasuMerchant = $apiHelper->getApiAccountId() && $apiHelper->getApiToken();
        if ($hasMinkasuMerchant) {
            $this->_getSession()->addNotice(
                 $this->__('You already have Minkasu merchant account. Please delete the credentials and try again.')
            );
        }

        $this->loadLayout();
        if ($hasMinkasuMerchant) {
            $this->getLayout()->getBlock('content')->unsetChild('minkasu_wallet.merchant.create');
        }
        $this->renderLayout();
    }

    /**
     * Create a Minkasu merchant
     */
    public function editAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save a Minkasu merchant
     */
    public function saveAction()
    {
        try {
            if (false === $this->getRequest()->isPost()) {
                Mage::throwException($this->__('Wrong request type.'));
            }
            $postData = $this->getRequest()->getPost();
            $userData = array(
                'business_name' => $postData['name'],
                'email' => $postData['email'],
                'phone' => $postData['phone'],
            );
            $gatewayData = array(
                'login_id' => $postData['authNet_api_login_id'],
                'key' => $postData['authNet_transaction_key'],
                'gateway' => $postData['authNet_gateway'],
            );
            /** @var $client Minkasu_Wallet_Model_Api_Client */
            $client = Mage::getModel('minkasu_wallet/api_client');
            $result = $client->getType('merchant')->createMerchant($userData, $gatewayData);
            if (isset($result['merchant_id']) && isset($result['minkasu_token'])) {
                $apiHelper = Mage::helper('minkasu_wallet/api');
                $apiHelper->saveApiAccountId($result['merchant_id']);
                $apiHelper->saveApiToken($result['minkasu_token']);

                $this->_getSession()->addSuccess(
                     $this->__('You have successfully created your Minkasu account and added it your Magento configuration.')
                );
                $this->_redirect('*/*/successCreate');
            } else {
                $this->_getSession()->addError($this->__('An error occurred during creating a Minkasu merchant.'));
                $this->_getSession()->setData('minkasu_merchant_details', $postData);
                $this->_redirect('*/*/create');
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
            $this->_getSession()->setData('minkasu_merchant_details', $postData);
            $this->_redirect('*/*/create');
        }
    }

    /**
     * Update a Minkasu merchant
     */
    public function updateAction()
    {
        try {
            if (false === $this->getRequest()->isPost()) {
                Mage::throwException($this->__('Wrong request type.'));
            }
            $postData = $this->getRequest()->getPost();
            $gatewayData = array(
                'login_id' => $postData['authNet_api_login_id'],
                'key' => $postData['authNet_transaction_key'],
                'test_mode' => $postData['authNet_gateway'],
            );
            /** @var $client Minkasu_Wallet_Model_Api_Client */
            $client = Mage::getModel('minkasu_wallet/api_client');
            $result = $client->getType('merchant')->updateMerchantGateway($gatewayData);
            if (isset($result['status']) &&  'success' == $result['status']) {
                $this->_getSession()->addSuccess(
                     $this->__('Merchant has been updated successfully.')
                );
                $this->_redirect('*/*/successUpdate');
            } else {
                $this->_getSession()->addError($this->__('An error occurred during updating a Minkasu merchant.'));
                $this->_getSession()->setData('minkasu_merchant_details', $postData);
                $this->_redirect('*/*/edit');
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
            $this->_getSession()->setData('minkasu_merchant_details', $postData);
            $this->_redirect('*/*/edit');
        }
    }

    /**
     * Show success page after Minkasu merchant creation
     */
    public function successCreateAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Show success page after Minkasu merchant updating
     */
    public function successUpdateAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
