<?php
/**
 * Created by IntelliJ IDEA.
 * User: takeru
 * Date: 31.07.15
 * Time: 3:49
 */

class PageManager
{
   static function findPage($name)
   {
      Database::DB()->select('pages')->where('name', $name)->exec();
      return Database::getRow();
   }
}