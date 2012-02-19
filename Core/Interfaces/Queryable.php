<?php
interface Core_Interfaces_Queryable
{
    public function where(array $conditions);
    public function getItemBy($field, $value);
}