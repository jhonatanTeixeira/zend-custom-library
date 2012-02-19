<?php
class Core_FB
{
    /**
     *
     * @var Zend_Log
     */
    protected static $_instance;

    public static function log($message, $type = Zend_Log::INFO)
    {
        if (!self::$_instance instanceof  Zend_Log) {
            self::$_instance = new Zend_Log(new Zend_Log_Writer_Firebug());
        }

        self::$_instance->log($message, $type);
    }

    private function  __construct()
    {
        
    }
}