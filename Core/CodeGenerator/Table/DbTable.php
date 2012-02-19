<?php
class Core_CodeGenerator_Table_DbTable extends Core_CodeGenerator_Table_Abstract
{
    protected function _setupClassPrefix()
    {
        $this->_classPreffix = "Model_DbTable_";
    }

    protected function _setupExtendFrom()
    {
        $this->_extendFrom = "Core_Db_Table";
    }

    protected function _createAttributes()
    {
        if (!isset($this->_relationShips)) {
            return array();
        }

        $map = array();
        foreach ($this->_relationShips as $relationShip) {
            $relationShipName = ucfirst($relationShip['parentTable']->getName());
            $field = $relationShip['parentTable']->getIdentityField()->__toString();
            $map[$relationShipName] = array(
                'columns' => $field,
                'refTableClass' => "Model_DbTable_$relationShipName",
                'refColumns' => $field
            );
        }

        $attributes = array(
            array(
                'name' => '_referenceMap',
                'visibility' => 'protected',
                'defaultValue' => $map
            )
        );

        return $attributes;
    }

}