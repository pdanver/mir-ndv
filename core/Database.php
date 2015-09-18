<?php
/**
 * Created by IntelliJ IDEA.
 * User: takeru
 * Date: 24.07.15
 * Time: 4:04
 */

class Database
{
   private static $db;

   static function DB()
   {
      if(empty(self::$db))
      {
         include_once __DIR__.'/databaseDrivers/'.Settings::$DB_DRIVER.'.php';

         self::$db = new Settings::$DB_DRIVER(Settings::$DB_host, Settings::$DB_username, Settings::$DB_password, Settings::$DB_name);
      }

      return self::$db;
   }

   static function result()
   {
      return self::$db->lastResult();
   }

   static function get($var = null)
   {
      /*if($var)
      {
         if(self::result() && self::result()->num_rows > 0)
            return self::result()->fetch_assoc()[$var];
         return null;
      }
      else
      {
         if(self::result() && self::result()->num_rows > 0)
            return self::result()->fetch_all(MYSQL_ASSOC);
         return null;
      }*/
      return self::$db->get();
   }

   static function getRow()
   {
      /*if(self::result() && self::result()->num_rows > 0)
         return self::result()->fetch_assoc();
      return null;*/
      return self::$db->getRow();
   }

   static function close()
   {

   }
} 