<?php
/**
 * 
 * злой профайлер
 * написано на коленке
 * @author nur
 *
 */
class Evil_Profiler extends Zend_Db_Profiler
{
    protected $_message = null;
    protected $_logger = null;
    protected $_stream = '/tmp/evil.profiler';
    public function setEnabled($enable)
    {
        $this->_message = new Zend_Log_Writer_Stream($this->_stream);
        $this->_logger = new Zend_Log($this->_message);
        parent::setEnabled($enable);
    }
    
    
    public function queryEnd($queryId)
    {
        $state = parent::queryEnd($queryId);
        $profile = $this->getQueryProfile($queryId);
        $this->_totalElapsedTime += $profile->getElapsedSecs();
        $this->_logger->info (implode(' ',array((string)round($profile->getElapsedSecs(),5),
                                      $profile->getQuery(),
                                      ($params=$profile->getQueryParams())?implode(' ',$params):null)));
                                      
    }
}