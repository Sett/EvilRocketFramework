<?php
/**
 * @throws Exception
 * @description works with the rest api
 * @author Se#
 * @version 0.0.1
 */
class Evil_Rest_Client implements Evil_Rest_Interface
{
    /**
     * @description items
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    protected $_items    = array();

    /**
     * @description passed arguments
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    protected $_args     = array();

    /**
     * @description current client for working with api
     * @var resource
     * @author Se#
     * @version 0.0.1
     */
    protected $_client   = null;

    /**
     * @description response
     * @var string/array
     * @author Se#
     * @version 0.0.1
     */
    protected $_response = null;

    /**
     * @description set args and a client
     * @throws Exception
     * @param array $args
     * @author Se#
     * @version 0.0.1
     * @return boolean
     */
    public function __construct($args, $request = false)
    {
        if(!is_array($args))
            throw new Exception(' Array is needed, "' . gettype($args) . '" is passed');

        $this->_args = $args;

        $url = isset($this->_args['url']) ? $this->_args['url'] : 'http://' . $_SERVER['SERVER_NAME'] . '/rest/';
        $this->_setClient($url);

        if($request)
            $this->request();
    }

    /**
     * @description set client
     * @param string $url
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    protected function _setClient($url)
    {
        $this->_client = curl_init($url);
        curl_setopt($this->_client, CURLOPT_POST, 1);
    }

    /**
     * @description delete action
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function destroy()
    {
        // todo
    }

    /**
     * @description fetch all items
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function fetchAll()
    {
        // todo
    }

    /**
     * @description find an item, fetch all if id is not passed
     * @param null|int $id
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function find($id = null)
    {
        // todo
    }

    /**
     * @description create a new item
     * @return bool|mixed
     * @author Se#
     * @version 0.0.1
     */
    public function create()
    {
        // todo
    }

    /**
     * @description send a request
     * @return bool|mixed
     * @author Se#
     * @version 0.0.1
     */
    public function request()
    {
        $data = isset($this->_args['params']) ? $this->_args['params'] : array();
        curl_setopt($this->_client, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($this->_client, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->_client, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        $res = curl_exec ($this->_client);

		if (!$res)
        {
			$this->_errno = curl_errno ($this->_client);
			$this->_error = curl_error ($this->_client);
			curl_close ($this->_client);
			return false;
		}

		curl_close ($this->_client);

		return $this->_response = $res;
    }

    /**
     * @description return response
     * @return null|string
     * @author Se#
     * @version 0.0.1
     */
    public function getResponse()
    {
        return $this->_response;
    }
}