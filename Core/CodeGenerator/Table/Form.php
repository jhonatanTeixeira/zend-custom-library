<?php

class Core_CodeGenerator_Table_Form extends Core_CodeGenerator_Table_Abstract
{
    protected function _setupClassPrefix()
    {
        $this->_classPreffix = "Model_Form_";
    }

    protected function _setupExtendFrom()
    {
        $this->_extendFrom = 'Core_Form';
    }

    /**
     * create elements creation methods to be put on the form classes, it uses
     * detections from the database and try to guess the form classes needed
     *
     * @todo split method into smaller methods
     *
     * @return array Zend_CodeGenerator_Php_Method 
     */
    protected function _createMethods()
    {
        $methods = array();
        foreach ($this->_table as $field) {
            $method = new Zend_CodeGenerator_Php_Method();

            $code = "";

            $related = $this->_getForeignKeyTable($field);
            if ($field->isIdentity()) {
                $code .= "\$element = new Zend_Form_Element_Hidden('$field');\n";
            } elseif (false !== $related) {
                $code .= "\$table = new " . $related->getDbTableName() . "();\n";
                $code .= "\$element = new Zend_Dojo_Form_Element_FilteringSelect('$field');\n";
                $code .= "\$element->setMultiOptions(\$table->fetchAllForOptions());\n";
            } else {
                switch ($field->getDataType()) {
                    case 'timestamp':
                        $code .= "\$element = new Zend_Dojo_Form_Element_DateTextBox('$field');\n";
                        break;
                    case 'int':
                    case 'varchar':
                    default:
                        if (preg_match("/^pass/", (string) $field)) {
                            $code .= "\$element = new Zend_Dojo_Form_Element_PasswordTextBox('$field');\n";
                        } else {
                            $code .= "\$element = new Zend_Dojo_Form_Element_TextBox('$field');\n";
                        }
                        break;
                    case 'text':
                        $code .= "\$element = new Zend_Dojo_Form_Element_Textarea('$field');\n";
                        break;
                    case 'tinyint':
                        $code .= "\$element = new Zend_Dojo_Form_Element_CheckBox('$field');\n";
                        break;
                }

                switch ($field->getDataType()) {
                    case 'int':
                    case 'tinyint':
                        $code .= "\$element->addValidator(new Zend_Validate_Int());\n";
                        break;
                    case 'varchar':
                    case 'text':
                        if (preg_match("/^email/", (string) $field)) {
                            $code .= "\$element->addValidator(new Zend_Validate_EmailAddress(true));\n";
                        } else {
                            $code .= "\$element->addValidator(new Zend_Validate_Alnum(true));\n";
                        }
                        break;
                    case 'timestamp':
                        $code .= "\$element->addValidator(new Zend_Validate_Date());\n";
                        break;
                    case 'float':
                        $code .= "\$element->addValidator(new Zend_Validate_Float());\n";
                        break;
                }
            }

            if ($field->isNullable()) {
                $code .= "\$element->setAllowEmpty(true);\n";
            } elseif(!$field->isIdentity()) {
                $code .= "\$element->setRequired();\n";
            }

            if (!$field->isIdentity()){
                $tableName = $this->_table->getName();
                $label = str_replace(ucfirst($tableName), "", (string) $field);
                $code .= "\$element->setLabel('$label');\n";
            }

            $code .= "return \$element;\n";
            //str_replace(";", ";\n", $code);
            $method->setName("_create". ucfirst($field) . "Element");
            $method->setVisibility('protected');
            $method->setBody($code);
            $method->setSourceDirty(true);
            $method->setDocblock("generated $field field form element creator");
            $methods[] = $method;
        }

        return $methods;
    }

    protected function _getForeignKeyTable(Core_Db_DatabaseInfo_Field $field)
    {
        //Zend_Debug::dump($this->_relationShips);
        foreach ($this->_relationShips as $relationShip) {
            if ($relationShip['parentTable']->hasField($field->__toString())) {
                return $relationShip['parentTable'];
            }
        }

        return false;
    }
}