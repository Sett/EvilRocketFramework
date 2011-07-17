<?php

class Evil_Composite_Fixed extends Evil_Composite_Base implements Evil_Composite_Interface {
	private $_fixed;
	protected $_lastQuery = null;
	protected $_loadedData = null;
	
	public function __construct($type, $id = null, $data = null) {
		$this->_type = $type;
		$this->_fixed = new Zend_Db_Table ( Evil_DB::scope2table ( $type ) );
		$info = $this->_fixed->info ();
		$this->_fixedschema = $info ['cols'];
		$this->_lastQuery = $this->_fixed->select ()->from ( $this->_fixed );
	
	}
	public function erase() {
		foreach ( $this->_items as $item ) {
			$item->erase ();
		}
		return $this;
	}
	
	public function truncate() {
		$this->_fixed->delete ();
		return $this;
	}
	
	public function update($data) {
		foreach ( $this->_items as $item ) {
			$item->update ( $data );
		}
		return $this;
	}
	
	public function where($key, $selector, $value = null, $offset = 0, $count = 500, $orderBy = 'id DESC') {
		switch ($selector) {
			case '=' :
			case '<' :
			case '>' :
			case '<=' :
			case '>=' :
			case '!=' :
				if (in_array ( $key, $this->_fixedschema )) {
					$this->_lastQuery = $this->_fixed->select ()->from ( $this->_fixed )

					->where ( $key . ' ' . $selector . ' ?', $value );
					$rows = $this->_fixed->fetchAll ( $this->_lastQuery );
					
					$ids = $rows->toArray ();
				}
				break;
			
			case ':' :
				foreach ( $value as &$cvalue )
					$cvalue = '"' . $cvalue . '"';
				
				if (in_array ( $key, $this->_fixedschema )) {
					$this->_lastQuery = $this->_fixed->select ()->from ( $this->_fixed )

					->where ( $key . ' IN (' . implode ( ',', $value ) . ')' );
					$rows = $this->_fixed->fetchAll ( $this->_lastQuery );
				}
				break;
			
			case '*' :
			case '@' :
				switch ($key) {
					case 'all' :
						$this->_lastQuery = $this->_fixed->select ()->limitPage ( $offset, $count )->order ( $orderBy );
						$rows = $this->_fixed->fetchAll ( $this->_lastQuery );
						break;
				}
				break;
			
			case 'multi' :
				$this->_lastQuery = $this->_fixed->select ()->from ( $this->_fixed );
				foreach ( $value as $fieldName => $fieldParams ) {
					foreach ( $fieldParams as $selector => $val ) {
						$this->_lastQuery->where ( $fieldName . ' ' . $selector . ' ?', $val );
					}
				
				}
				$rows = $this->_fixed->fetchAll ( $this->_lastQuery );
				
				break;
			
			default :
				throw new Evil_Exception ( 'Unknown selector ' . $selector );
				break;
		}
		
		$ids = $rows->toArray ();
		foreach ( $ids as $data ) {
			$id = $data ['id'];
			
			$this->_items [$id] = Evil_Structure::getObject ( $this->_type, $id, $data );
		}
		
		return $this;
	}
	
	/**
	 * 
	 * return items count from set
	 * @author NuR
	 * @return int
	 */
	public function count() {
		/**
		 * 
		 * нутро подсказывает что как то проще должно быть...
		 * @var unknown_type
		 */
		
		$query = $this->_lastQuery->reset ( Zend_Db_Select::LIMIT_COUNT )->reset ( Zend_Db_Select::LIMIT_OFFSET )->reset ( Zend_Db_Select::COLUMNS )->columns ( new Zend_Db_Expr ( 'count(*) as c' ) );
		$rowData = $this->_lastQuery->getTable ()->fetchRow ( $query );
		$count = $rowData->toArray ();
		return $count ['c'];
	
	}
	
	public function load($ids) {
		foreach ( $ids as $id ) {
			$this->_items [$id] = new Evil_Object_Fixed ( $this->_type, $id );
		}
	}
}