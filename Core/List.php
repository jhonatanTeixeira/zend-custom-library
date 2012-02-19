<?php

class Core_List implements IteratorAggregate, Core_Interfaces_Queryable, Countable
{
    private $_items = array();

    public function __construct(array $items = null)
    {
        if (!is_null($items)) {
            foreach ($items as $item) {
                $this->appendItem($item);
            }
        }
    }

    /**
     *
     * @param string $field
     * @param mixed $value
     * @return Core_Interface_ListItem
     */
    public function getItemBy($field, $value)
    {
        $list = $this->where(array($field => $value));

        return $list->getIterator()->current();
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_items);
    }

    /**
     *
     * @param array $conditions
     * @return Core_List
     */
    public function where(array $conditions)
    {
        $list = new self();
        foreach ($this as $item) {
            if ($item->hasAttributesValues($conditions)) {
                $list->appendItem($item);
            }
        }

        return $list;
    }

    public function appendItem(Core_Interface_ListItem $item)
    {
        $this->_items[] = $item;
    }

    public function count()
    {
        return count($this->_items);
    }

}