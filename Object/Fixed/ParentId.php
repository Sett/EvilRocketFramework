<?php
/**
 * @throws Exception
 * @author Se#
 * @version 0.0.2
 * @description Realize storing trees using parentId
 */
class Evil_Object_Fixed_ParentId extends Evil_Object_Fixed_Required
{
    /**
     * @description required fields
     * @var array
     * @author Se#
     * @version 0.0.2
     */
    protected $_required = array('id'          => 'int',
                                 'parentId'    => 'int');
    
    /**
     * @description create a row in a DB
     * @param  $data
     * @return int
     * @author Se#
     * @version 0.0.1
     */
    public function create ($data)
    {// insert only existed fields
        foreach ($data as $key => $value)
        {
            if(!isset($this->_info['metadata'][$key]))
                return null;
        }

        $this->_fixed->insert($data);
        $this->_id = Zend_Registry::get('db')->lastInsertId();
        return Zend_Registry::get('db')->lastInsertId();
    }

    /**
     * @description load node/branch
     * @param int $id
     * @return bool|Evil_Object_Fixed_ParentId
     * @author Se#
     * @version 0.0.2
     */
    public function load($id)
    {
        $self     = $this->_fixed->fetchRow($this->_fixed->select()->where('id=?', $id));
        if(!$self)
            return false;

        $children = $this->_getChildren($id);

        $this->_data = array('self' => $self->toArray(), 'children' => $children);

        return $this;
    }

    /**
     * @description get node's children
     * @param int $id
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _getChildren($id)
    {
        $result = array();
        $nodes = $this->_fixed->fetchAll($this->_fixed->select()->where('parentId=?', $id));
        foreach($nodes as $node)
        {
            if(isset($node['id']))
            {
                $result[$node['id']] = array('self' => $node, 'children' => array());
                $result[$node['id']]['children'] = $this->_getChildren($node['id']);
            }
        }

        return $result;
    }

    /**
     * @description get node's children ids
     * @param int $id
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _getChildrenIds($id)
    {
        $result = array();
        $nodes = $this->_fixed->fetchAll($this->_fixed->select()->where('parentId=?', $id));
        foreach($nodes as $node)
        {
            if(isset($node['id']))
            {
                $result[] = $node['id'];
                $result = array_merge($result, $this->_getChildrenIds($node['id']));
            }
        }

        return $result;
    }

    /**
     * @description delete node/brunch
     * @return Evil_Object_Fixed_ParentId
     * @author Se#
     * @version 0.0.2
     */
    public function erase()
    {
        parent::erase();
        // delete node itself
        $this->_fixed->delete($this->_fixed->getAdapter()->quoteInto(array('id = ?'), array($this->_id)));
        // get children ids
        $childrenIds = $this->_getChildrenIds($this->_id);
        // delete children
        $this->_fixed->delete('id IN ("' . implode('","', $childrenIds) . '")');

        return  $this;
    }
}