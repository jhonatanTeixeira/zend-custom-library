<?php
abstract class Core_Db_Row extends Zend_Db_Table_Row_Abstract implements SplSubject
{
    protected $_loaded = array();

    /**
     *
     * @var SplObserver
     */
    protected $_observers = array();

    /**
     * sets the respective table class
     *
     * @param array $config 
     */
    public function __construct(array $config = array())
    {
        $this->_tableClass = str_replace(
            "Model_",
            "Model_DbTable_",
            get_class($this)
        );

        $this->_table = $this->_getTableFromString($this->_tableClass);

        if (isset($config['data']) and is_array($config['data'])) {
            $this->_prepareData($config['data']);
        }

        $this->_attachObservers();
        
        parent::__construct($config);
    }

    protected function _attachObservers()
    {
        $path = realpath(APPLICATION_PATH . '/../library');

        $files = glob("$path/Core/Db/Observer/*.php");

        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $className = "Core_Db_Observer_$filename";
            $observer = new $className();
            $this->attach($observer);
        }

        $files = glob("$path/App/Db/Observer/*.php");
    }

    /**
     * checks if its a column of the table if not try to load the related model
     *
     * @param string $columnName
     * @return string|Core_Db_Row
     */
    public function __get($columnName)
    {
        if (!$this->tableHasField($columnName)) {
            $this->loadModel($columnName);
            return $this->getLoadedModel($columnName);
        }

        return parent::__get($columnName);
    }

    public function __call($method, array $args)
    {
        if (preg_match('/^load(\w+?)(?:By(\w+))?$/', $method, $matches)) {
            $model  = $matches[1];
            $rule   = isset($matches[2]) ? $matches[2] : null;
            $this->loadModel($model, $rule);

            return $this;
        }
        
        return parent::__call($method, $args);
    }

    protected function _prepareData(array &$data)
    {
        $fields = $this->_getTable()->info('cols');
        $data = array_intersect_key($data, array_flip($fields));
    }

    /**
     * checks if the table has the field specified
     *
     * @param string $fieldName
     * @return bool
     */
    public function tableHasField($fieldName)
    {
        return $this->_getTable()->hasField($fieldName);
    }

    /**
     *
     * @param string $modelName
     * @param mixed $columns
     * @param mixed $refColumns
     * @return Core_Db_Row;
     */
    public function loadModel($modelName, Zend_Db_Table_Select $select = null,
        $rule = null)
    {
        $table = $this->_getTable();
        $modelName = ucfirst($modelName);

        $modelClass = 'Model_DbTable_' . $modelName;

        if ($this->hasLoadedModel($modelName)) {
            return $this;
        }

        try {
            $this->_loaded[$modelName] = $this->findDependentRowset(
                $modelClass,
                $rule,
                $select
            );
        } catch (Exception $exception) {
            $dbTable = new $modelClass();

            if (is_null($select)) {
                $select = $dbTable->select();
            } else {
                $select->reset('from')
                    ->from($dbTable->info(Zend_Db_Table_Abstract::NAME));
            }

            $id = $this->getIdFieldName();

            if (!$dbTable->hasField($id)) {
                $id = $dbTable->getPrimary();
            }

            $select->where("$id = ?", $this->__get($id));

            $this->_loaded[$modelName] = $dbTable->fetchAll($select);
        }

        if (is_null($this->_loaded[$modelName])) {
            $this->_loaded[$modelName] = array();
        }

        return $this;
    }

    /**
     * appends specified data to this row, and atach the related fields.
     * PHP should seriously have methods overload ...
     *
     * @param mixed $data can be either rowset or a row object
     */
    public function append($data)
    {
        if (!$data instanceof Zend_Db_Table_Rowset_Abstract
            and !$data instanceof Zend_Db_Table_Row_Abstract) {
            throw new Core_Db_Row_Exception(
                'zend row or zend rowset instanceof expected'
            );
        }

        $modelName = str_replace('Model_DbTable_', '', $data->getTableClass());
        if (!isset($this->_loaded[$modelName])) {
            $this->_loaded[$modelName] = new Core_Db_Rowset(
                array(
                    'table'     => $data->getTable(),
                    'rowClass'  => $data->getTable()->getRowClass(),
                    'stored'    => true
                )
            );
        }

        if ($data instanceof Zend_Db_Table_Rowset_Abstract) {
            foreach ($data as $row) {
                $row->__set(
                    $this->getIdFieldName(), 
                    $this->__get($this->getIdFieldName())
                );
                $this->_loaded[$modelName]->Add($row);
            }
        } else {
            $data->__set(
                $this->getIdFieldName(),
                $this->__get($this->getIdFieldName())
            );
            $this->_loaded[$modelName]->Add($data);
        }

        return $this;
    }

    /**
     * gets a loaded model inside this row
     *
     * @param string $modelName
     * @return Core_Db_Row
     */
    public function getLoadedModel($modelName)
    {
        $modelName = ucfirst($modelName);

        if ($this->hasLoadedModel($modelName)) {
            return $this->_loaded[$modelName];
        }

        throw new Core_Db_Row_Exception("model $modelName não carregado");
    }

    /**
     * checks if theres a model loaded by the specified name
     *
     * @param string $modelName
     * @return bool
     */
    public function hasLoadedModel($modelName)
    {
        $modelName = ucfirst($modelName);

        return (isset($this->_loaded[$modelName]));
    }

    /**
     * loads the data from database on this row, based by the id value specified
     *
     * @param int $id
     * @return Core_Db_Row
     */
    public function loadById($id)
    {
        $key = $this->getIdFieldName();

        $this->__set($key, (int) $id);

        $this->refresh();

        return $this;
    }

    /**
     * magic setter, check if fiels exists in table instead of checking if only
     * present on the result fields
     *
     * @param string $columnName
     * @param mixed $value
     */
    public function __set($columnName, $value)
    {
        if (!$this->tableHasField($columnName)) {
            throw new Core_Db_Row_Exception(
                "field $columnName does not exists in table"
            );
        }
        
        $columnName = $this->_transformColumn($columnName);
        $this->_data[$columnName] = $value;
        $this->_modifiedFields[$columnName] = true;
    }

    /**
     * sets the value from an determined array
     *
     * @param array $data
     * @return Core_Db_Row 
     */
    public function setFromArray(array $data)
    {
        $this->_prepareData($data);
        
        foreach ($data as $columnName => $value) {
            $this->__set($columnName, $value);
        }

        return $this;
    }

    /**
     *
     * @param bool $useDirty
     * @return array
     */
    protected function _getPrimaryKey($useDirty = true)
    {
        if (!is_array($this->_primary)) {
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception("The primary key must be set as an array");
        }

        $primary = array_flip($this->_primary);
        if ($useDirty) {
            $array = array_intersect_key($this->_data, $primary);
        } else {
            $array = array_intersect_key($this->_cleanData, $primary);
        }

        return $array;
    }

    /**
     * gets the id field name
     *
     * @return string
     */
    public function getIdFieldName()
    {
        $primary = $this->_primary;

        if (is_array($primary)) {
            $key = array_shift($primary);
        } else {
            $key = $primary;
        }

        return $key;
    }

    /**
     * gets the model fields
     *
     * @return array
     */
    public function getFields()
    {
        return array_keys($this->_data);
    }

    /**
     * saves the data from this row and the data from all loaded models
     *
     * @return mixed
     */
    public function save()
    {
        $result = parent::save();

        foreach ($this->_loaded as $rowset) {
            foreach ($rowset as $row) {
                $row->save();
            }
        }

        $this->notify();

        return $result;
    }

    public function refresh($recursive = false)
    {
        parent::refresh();

        if ($recursive) {
            foreach ($this->_loaded as $row) {
                $row->refresh();
            }
        }
    }

    public function hasValues(array $values)
    {
        $intersect = array_intersect_assoc($values, $this->_data);

        return (bool) count($intersect);
    }

    public function isForeignKey($fieldName)
    {
        return $this->_getTable()->isForeignKey($fieldName);
    }

    public function getForeignKeyRefClassName($fieldName)
    {
        return $this->_getTable()->getForeignKeyRefClassName($fieldName);
    }

    public function getMainField()
    {
        return $this->_getTable()->getMainField();
    }

    public function getMainFieldValue()
    {
        return $this->__get($this->getMainField());
    }

    public function attach(SplObserver $observer)
    {
        $this->_observers[get_class($observer)] = $observer;
    }

    public function detach(SplObserver $observer)
    {
        unset($this->_observers[get_class($observer)]);
    }

    public function notify()
    {
        foreach ($this->_observers as $observer) {
            $observer->update($this);
        }
    }
}