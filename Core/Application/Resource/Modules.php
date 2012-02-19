<?php
class Core_Application_Resource_Modules extends Zend_Application_Resource_Modules
{
    /**
     * since it doesn't makes sense at all to run all modules bootstraps despite
     * of what module are being acessed, this method is used to make sure that
     * only the requested module bootstrap will run, its maybe an ugly 
     * workaraound because it forces the creation of a request object and routing
     * it using the rewrite route, if the application is set to use any other 
     * routers, this may bootstrap the wrong module, use it with care.
     *
     * @return array list os bootstraps, now itll only be one, but had to keep
     *         compatibility
     * 
     * @author Jhonatan Teixeira
     */
    public function init()
    {
        $front = Zend_Controller_Front::getInstance();
        $request = new Zend_Controller_Request_Http();
        $router = $front->getRouter();
        $router->route($request);

        $bootstrapClass = $this->_formatModuleName($request->getModuleName()) . '_Bootstrap';

        require_once $front->getModuleDirectory($request->getModuleName()) . '/Bootstrap.php';

        $moduleBootstrap = new $bootstrapClass($this->getBootstrap());
        $moduleBootstrap->bootstrap();
        $this->_bootstraps[$request->getModuleName()] = $moduleBootstrap;

        return $this->_bootstraps;
    }
}