<?php
class Core_Db_Hydrator
{
    protected $_data;

    /**
     *
     * @var Core_Db_Select
     */
    protected $_select;


    public function __construct(array $data, Core_Db_Select $select)
    {
        $this->_data = $data;
        $this->_select = $select;
    }

    /**
     * creates an row instance
     *
     * @param array $data
     * @param string $modelClass
     * @return Core_Db_Row
     */
    protected function _createRow(array $data, $modelClass)
    {
        $row = new $modelClass(
            array(
                'data' => $data,
                'stored' => true
            )
        );

        return $row;
    }

    /**
     * this function is made to separate the data into a orm form, in case joins
     * being used in the query, some systems grow so complex and performance 
     * critical, that using common orm techniques can become heavy, so using
     * joins in query becomes less expensive, data hydration is to prevent data
     * redundancy on such resultSets
     *
     * @todo method is too big and still not complete, maybe its best to split
     * it around
     * @todo this method doesnt resolve many-to-many cases, doesnt append a row
     * to a model other than the main context, stuill need to do that
     *
     * @return Core_Db_Rowset
     */
    public function hydrate()
    {
        if (count($this->_data) == 0) {
            return null;
        }

        //get the tables involved in the query
        $models = $this->_select->getPart("from");

        //will create a list of all the models with theyr needed information
        $modelList = array();
        foreach ($models as $model => $attr) {
            $info = new Core_Db_DatabaseInfo_Table($model);
            $identity = (string) $info->getIdentityField();
            $modelClass = $info->getModelName();
            $modelList[$model] = (object) array(
                'identity'  => $identity,
                'class'     => $modelClass,
                'currentId' => 0,
                'isContext' => false
            );

            //the table wich doesnt have a join condition is the main table in 
            //the query, therefore the current context
            if (is_null($attr['joinCondition'])) {
                $data = reset($this->_data);
                $context = $this->_createRow(
                    $data,
                    $modelClass
                );
                $modelList[$model]->currentId = $data[$identity];
                $modelList[$model]->isContext = true;
            }
        }

        //creates the main rowset that will hold the context rows
        $rowset = new Core_Db_Rowset(
            array(
                'table'     => $context->getTable(),
                'rowClass'  => get_class($context)
            )
        );
        $rowset->Add($context);

        //iterates the data and puts each row into its place
        foreach ($this->_data as $index => $row) {
            //for each row we iterate the models list and identify it
            foreach ($modelList as $model) {
                $identity = $model->identity;

                //only creates a new row when the identity has changed its value
                if ($row[$identity] != $model->currentId) {
                    //if its a context row we add it to the main rowset class
                    //append to the context otherwise
                    if ($model->isContext) {
                        $context = $this->_createRow($row, $model->class);
                        $rowset->Add($context);
                    } else {
                        $context->append($this->_createRow($row, $model->class));
                    }
                }

                //change the current model current id
                $model->currentId = $row[$identity];
            }
        }

        return $rowset;
    }
}