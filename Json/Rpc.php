<?php

class Evil_Json_Rpc
{
	
    /**
     * 
     * Generate checksum given data
     * @param array $data
     * @param string $secretKey
     */
    public static function getHash ($data, $secretKey)
    {
        return sha1(implode('', $data) . $secretKey);
    }
    /**
     * 
     * Send data to server
     * @param array $data
     * @param string $url
     * @param string $secretKey
     */
    public static function sendData ($data, $url, $secretKey)
    {
        /**
         * 
         * Create rpc client
         * @var jsonRPCClient
         */
    	include 'jsonRPCClient.php';
        $client = new jsonRPCClient($url);
        try {
            //	Generate checksum of given data
            $checksum = self::getHash($data, $secretKey);
            /**
             * calling remote procedure store
             * Enter description here ...
             * @var unknown_type
             */
            $response = $client->store($data, $checksum);
            return $response;
        } catch (Exception $e) {
        	throw new Evil_Exception($e->getMessage(),$e->getCode());
        }
    }
}