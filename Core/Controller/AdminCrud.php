<?php
abstract class Core_Controller_AdminCrud extends Core_Controller_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        $this->view->crud = $this->_crudObject;
    }

    public function formAction()
    {
        $this->view->form = $this->_crudObject->getFormClass('default');
        $id = $this->_request->getParam('id');
        $model = $this->_crudObject->getModelClass();

        if ($this->isPost()) {
            if ($this->view->form->isValid($this->getPost())) {
                $model->setFromArray($this->view->form->getValues());
                $model->save();

                $this->_redirect(
                    $this->_request->getModuleName() . '/' . $this->_crudObject->getControllerName() . '/list'
                );
            }
        }

        if ($id) {
            $model->loadById($id);
            $this->view->form->populate($model->toArray());
        }

        $this->render('default/form', null, true);
    }

    public function listAction()
    {
        $paginator = $this->_crudObject->getDbTableClass()
            ->fetchAllPaginated(
                $this->_getParam('page', 1),
                $this->_getParam('max', 20)
            );
        
        $this->view->paginator = $paginator;

        $this->render('default/list', null, true);
    }

}