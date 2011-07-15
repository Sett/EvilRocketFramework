<?php
/**
 * 
 * Json rpc клиент
 * @author nur
 *
 */
class Evil_Json_Rpc
{
    
     public static $rpcUrl = null;
	
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
     * TODO:: надо переписать это
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
        $client = new Evil_Json_jsonRPCClient($url);
        $client->setRPCNotification(false);
        try {
            //	Generate checksum of given data
            $checksum = self::getHash($data, $secretKey);
            /**
             * calling remote procedure store
             * @var unknown_type
             */
          return $client->store($data, $checksum);
            return $client;
        } catch (Exception $e) {
        	return $e;
        	//throw new Evil_Exception($e->getMessage(),$e->getCode());
        }
    }
    
    
  
    
    /**
     * 
     * Меджик метод для вызова удаленных процедур
     * @param string $methodName
     * @param array $params
     * @return array
     * @author NuR
     * @throws Zend_Http_Client_Exception
     * @example
     * 
     *      $data = array(
     *      			  'Service' => 'Citizen',
                          'Method' => 'showOrderList',
                          'userid' => 1
                          );
        
        Evil_Json_Rpc::make($data);
     */
    
    public static function __callStatic ($methodName,$params)
    {
        
        static $requestId = 1; 
        static $client = null;
        if(null == $client)
        {
            /**
             * дефолтный адрес
             */
            if(null == self::$rpcUrl)
            {
                $config = Zend_Registry::get('config');
                
                self::$rpcUrl = isset($config['jsonrpc']['url']) ? $config['jsonrpc']['url'] :null;
            }
            $options = array(
                'maxredirects' => 5,
                'timeout'      => 300,
                'useragent'    => 'Evil_Json_Rpc'
            );
            $client = new Zend_Http_Client(self::$rpcUrl);
            $client->setConfig($options);
        }
       
        $requestParams = array(
    						'method' => $methodName,
    						'params' => $params,
                            'id' => $requestId++
						);
						
		$request = Zend_Json::encode($requestParams);
		$client->setHeaders('Content-type','application/json');
		$client->setRawData($request);
		$response = $client->request('POST');
		try {
		    return Zend_Json::decode($response->getBody());
		} catch (Exception $e)
		{
		    return array('ex' => $e->__toString(),'response' => $response->getBody());
		}
		
    }
}