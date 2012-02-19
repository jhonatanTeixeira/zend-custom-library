<?php
class Core_Db_Cache
{
    /**
     *
     * @var Zend_Cache_Manager
     */
    protected $_manager;

    /**
     *
     * @var array
     */
    protected $_options = array();

    /**
     *
     * @var Core_Db_Table
     */
    protected $_table;

    /**
     *
     * @var string
     */
    protected $_tableName;

    /**
     *
     * @var bool
     */
    protected $_hasResource = false;

    /**
     *
     * @var bool
     */
    protected $_hasTemplate = false;

    /**
     *
     * @var Zend_Cache_Core
     */
    protected $_cache;
    
    public function __construct(Core_Db_Table $table)
    {
        $this->_table = $table;
        $this->_tableName = $table->info(Zend_Db_Table_Abstract::NAME);

        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $resource = $bootstrap->getPluginResource('cachemanager');

        if (!is_null($resource)) {
            $this->_hasResource = true;

            $this->_manager = $resource->getCacheManager();

            if ($this->_manager->hasCacheTemplate('database')) {
                $this->_hasTemplate = true;

                $options = $this->_manager->getCacheTemplate('database');

                $this->_options['frontend'] = $options['frontend'];
                $this->_options['backend']  = $options['backend'];

                $this->_cache = $this->_manager->getCache('database');
            }
        }
    }

    protected function _getIdentifier(Zend_Db_Table_Select $select)
    {
        return $this->_tableName . md5((string)$select);
    }

    /**
     * gets the backend options
     *
     * @return stdClass
     * @return null
     */
    public function getBackendOptions()
    {
        if (isset($this->_options['backend']['options'])) {
            return (object) $this->_options['backend']['options'];
        }

        return null;
    }

    /**
     * return the cache manager template options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * returns the table name used in this context
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_tableName;
    }

    /**
     * returns the cache manager got from the resource if any
     *
     * @return Zend_Cache_Manager
     */
    public function getManager()
    {
        return $this->_manager;
    }

    /**
     * save a cache data based on the select stm
     *
     * @param array $data
     * @param Zend_Db_Table_Select $select
     * @return bool
     */
    public function save(array $data, Zend_Db_Table_Select $select)
    {
        if (!$this->_hasTemplate) {
            return false;
        }

        $this->_cache->save($data, $this->_getIdentifier($select));
    }

    /**
     * load the cache based on the select
     *
     * @param Zend_Db_Table_Select $select
     * @return bool|mixed
     */
    public function load(Zend_Db_Table_Select $select)
    {
        if (!$this->_hasTemplate) {
            return false;
        }

        return $this->_cache->load($this->_getIdentifier($select));
    }

    /**
     * cleans the table cache
     */
    public function clean()
    {
        if (!$this->_hasTemplate) {
            return false;
        }

        $cacheDir = $this->getBackendOptions()->cache_dir;

        $files = glob("$cacheDir/*$this->_tableName*");

        foreach ($files as $file) {
            unlink($file);
        }
    }
}