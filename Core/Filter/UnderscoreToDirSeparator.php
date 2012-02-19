<?php
class Core_Filter_UnderscoreToDirSeparator implements Zend_Filter_Interface
{

    public function filter($value)
    {
        return str_replace("_", DIRECTORY_SEPARATOR, $value);
    }
}