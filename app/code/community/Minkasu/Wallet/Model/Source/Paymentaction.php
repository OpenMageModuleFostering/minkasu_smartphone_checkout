<?php

class Minkasu_Wallet_Model_Source_Paymentaction
{

     public function toOptionArray()
     {
         return array(
             array(
                 'value' => 'authorize',
                 'label' => 'Authorize Only'
             ),
             array(
                 'value' => 'authorize_capture',
                 'label' => 'Authorize and Capture'
             ),
         );
     }
}
