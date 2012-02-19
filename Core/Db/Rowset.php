<?php
/**
 * rowset class
 *
 * @author Jhonatan Teixeira
 */
class Core_Db_Rowset extends Zend_Db_Table_Rowset_Abstract
{
    /**
     * gets the specified value of the current row, usefull for 1x1 relationships
     *
     * @param <type> $name
     * @return <type> 
     */
    public function __get($name)
    {
        return $this->current()->__get($name);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->current(), $name), (array) $arguments);
    }

    public function __set($name, $value)
    {
        $this->current()->__set($name, $value);
    }

    public function Add(Zend_Db_Table_Row_Abstract $row)
    {
        $this->_count ++;

        $this->_rows[] = $row;
    }

    protected function _loadAndReturnRow($position)
    {
        return $this->_rows[$position];
    }

    /**
     * querys this context and the database afterwards, only meant to improve
     * performance on reusable rowsets, may cause concurrency problems, only
     * meant for simple conditions, no suport to like or other things (wich php
     * had something like .net link to query objects), use this with care
     *
     * @param array $cond
     * @return Core_Db_Rowset
     */
    public function where(array $cond)
    {
        $id = $this->getTable()->getPrimary();
        $ids = array();
        $rows = array();
        foreach ($this as $index => $row) {
            if ($row->hasValues($cond)) {
                $ids[] = $row->__get($id);
                $rows[] = $index;
            }
        }

        $result = new Core_Db_Rowset(
            array(
                'table' => $this->_table,
                'rowClass' => $this->_rowClass,
                'stored' => $this->_stored
            )
        );

        foreach ($rows as $row) {
            $result->Add($this[$row]);
        }

        return $result;
    }

    public function getRowByField($field, $value)
    {
        foreach ($this as $row) {
            if ($row->__get($field) == $value) {
                return $row;
            }
        }

        return null;
    }

    /**
     * searches a row contained on this rowset only by its id value
     *
     * @param int $id
     * @return Core_Db_Row null if not found
     */
    public function getRowById($value)
    {
        $id = $this->getTable()->getPrimary();

        return $this->getRowByField($id, $value);
    }

    /**
     * returns a prepared list to be used on a <select> input
     *
     * @param string $innerField
     * @return Core_List 
     */
    public function getListToOptions($innerField)
    {
        $list = new Core_List();
        $id = $this->getTable()->getPrimary();

        foreach($this as $row) {
            $list->appendItem(
                new Core_List_Option(
                    $row->__get($id),
                    $row->__get($innerField)
                )
            );
        }

        return $list;
    }
}