<?php

class Core_List_Option implements Core_Interface_ListItem
{
    private $_id;

    private $_value;

    public function __construct($id, $value)
    {
        $this->_id = $id;
        $this->_value = $value;
    }

    public function __get($name)
    {
        if (!preg_match("^_", $name)) {
            $name = "_" . $name;
        }

        if (!isset($this->$name)) {
            throw new Exception("$name doesnt exists in option object");
        }

        return $this->$name;
    }

    public function hasAttributesValues(array $attributesValues)
    {
        $matches = 0;

        try {
            foreach ($attributesValues as $attr=>$value) {
                if ($this->__get($attr) == $value) {
                    $matches ++;
                }
            }
        } catch(Exception $exception) {
            return false;
        }

        return ($matches == count($attributesValues));
    }

}