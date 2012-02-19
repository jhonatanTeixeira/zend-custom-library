<?php
/**
 * customized table class, implements orm style, model and dbtable pattern, and
 * data hydration for join statements
 *
 * @author Jhonatan Teixeira
 */
abstract class Core_Db_Table extends Zend_Db_Table
{
    public function __construct()
    {
        parent::__construct();
        $this->_rowClass = str_replace(
            'Model_DbTable_',
            'Model_',
            get_class($this)
        );

        $this->_rowsetClass = 'Core_Db_Rowset';
    }

    /**
     * parse the post conditions
     *
     * @param array $post
     * @return <type> 
     */
    protected function _parseConditions(array $post = null)
    {
        if (!$post) {
            $post = Zend_Controller_Front::getInstance()
                ->getRequest()
                ->getPost();
        }

        $conditions = array();
        foreach ($post as $key => $value) {
            $conditions["$key = ?"] = $value;
        }

        return $conditions;
    }

    /**
     * setup the table name
     */
    protected function _setupTableName()
    {
        $this->_name = str_replace('model_dbtable_', '', strtolower(get_class($this)));
    }

    /**
     * returns the primary key name
     *
     * @return string
     */
    public function getPrimary()
    {
        return (is_array($this->_primary))
            ? reset($this->_primary)
            : $this->_primary;
    }

    public function getMainField()
    {
        foreach ($this->_metadata as $field => $data) {
            if (preg_match("/(^name)|(^description)/", $field)) {
                return $field;
            }
        }

        return null;
    }

    /**
     * returns a db select with the from case set with the dbtable table name
     *
     * @return Core_Db_Select 
     */
    public function select()
    {
        require_once 'Zend/Db/Table/Select.php';
        $select = new Core_Db_Select($this);

        $select->from(
            $this->info(self::NAME),
            Zend_Db_Table_Select::SQL_WILDCARD,
            $this->info(self::SCHEMA)
        )->setIntegrityCheck(false);
        
        return $select;
    }

    /**
     * returns a select with the search params
     *
     * @param array $post
     * @return <type> 
     */
    public function getSearchSelect(array $post = null)
    {
        $select = $this->select();
        foreach ($this->_parseConditions($post) as $condition => $value) {
            $select->where($condition, $value);
        }

        return $select;
    }

    /**
     * prepares the data for saving
     *
     * @param array $data 
     */
    protected function _prepareData(array &$data)
    {
        $fields = $this->info('cols');
        $data = array_intersect_key($data, array_flip($fields));
    }

    /**
     * creates and execute a insert sql statement
     *
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $this->_prepareData($data);
        return parent::insert($data);
    }

    /**
     * creates and executes a update statement
     *
     * @param array $data
     * @param mixed $where
     * @return mixed
     */
    public function update(array $data, $where)
    {
        $this->_prepareData($data);
        return parent::update($data, $where);
    }

    /**
     * checks if the table has the specified reference
     *
     * @param string $tableClassname
     * @return bool
     */
    public function hasReference($tableClassname)
    {
        $refMap = $this->_getReferenceMapNormalized();

        foreach ($refMap as $reference) {
            if ($reference[self::REF_TABLE_CLASS] == $tableClassname) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @see Zend_Db_Table_Rowset::fetchAll
     * @param Zend_Db_Table_Select $where
     * @param string $order
     * @param int $count
     * @param int $offset
     * @return Core_Db_Rowset
     */
    public function fetchAll($where = null, $order = null, $count = null,
        $offset = null)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            if ($count !== null || $offset !== null) {
                $select->limit($count, $offset);
            }

        } else {
            $select = $where;
        }

        $rows = $this->_fetch($select);

        return $rows;
    }

    /**
     *
     * @param mixed $where
     * @param int $order
     * @return Core_Db_Row
     */
    public function fetchRow($where = null, $order = null)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            $select->limit(1);

        } else {
            $select = $where->limit(1);
        }

        $rows = $this->_fetch($select);

        if (is_null($rows)) {
            return null;
        }

        return $rows->current();
    }

    /**
     * fetches a result from the database and does the hydration
     *
     * @param Zend_Db_Table_Select $select
     * @return Core_Db_Rowset
     */
    protected function _fetch(Zend_Db_Table_Select $select)
    {
        $cache = new Core_Db_Cache($this);

        $resultSet = $cache->load($select);

        if ($resultSet === false) {
            $resultSet = parent::_fetch($select);
            $cache->save($resultSet, $select);
        }

        $hydrator = new Core_Db_Hydrator($resultSet, $select);
        return $hydrator->hydrate();
    }

    public static function loadTable($table, $where = null, $order = null, $count = null,
        $offset = null)
    {
        $inflector = new Core_Filter_TableNameToDbTable();
        $tableName = $inflector->filter($table);
        $instance = new $tableName();
        return $instance->fetchAll($where, $order, $count, $offset);
    }

    public function hasField($fieldName)
    {
        $cols = $this->info('metadata');

        return array_key_exists($fieldName, $cols);
    }

    public function isForeignKey($fieldName)
    {
        foreach ($this->_referenceMap as $map) {
            if ($map['refColumns'] == $fieldName) {
                return true;
            }
        }

        return false;
    }

    public function getForeignKeyRefClassName($fieldName)
    {
        foreach ($this->_referenceMap as $map) {
            if ($map['refColumns'] == $fieldName) {
                return array_pop(explode("_", $map['refTableClass']));
            }
        }
    }

    /**
     *
     * @param int $page
     * @param int $max
     * @param Zend_Db_Table_Select $where
     * @return Zend_Paginator 
     */
    public function fetchAllPaginated($page, $max,
        Zend_Db_Table_Select $where = null)
    {
        if (is_null($where)) {
            $where = $this->select();
        }

        $adapter = new Zend_Paginator_Adapter_DbTableSelect(
            $where
        );

        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage($max);

        return $paginator;
    }

    public function fetchAllForOptions(Zend_Db_Table_Select $select = null)
    {
        $select = $select ? $select : $this->select();
        $select->reset('columns');

        $select->columns(
            array(
                'key' => $this->getPrimary(),
                'value' => $this->getMainField()
            )
        );

        return $this->getAdapter()->fetchAll($select);
    }
}