<?php

abstract class Minkasu_Wallet_Model_Api_Format_Abstract
{
    /**
     * Decode an http response to a PHP array
     *
     * @abstract
     * @param string $response
     * @return array
     */
    abstract public function decodeResponse($response);
}
