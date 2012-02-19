<?php
class Core_Db_Observer_Cache implements SplObserver
{

    public function update(SplSubject $row)
    {
        $cache = new Core_Db_Cache($row->getTable());
        $cache->clean();
    }
}