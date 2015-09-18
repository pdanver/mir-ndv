<?php
/**
 * Created by IntelliJ IDEA.
 * User: takeru
 * Date: 16.07.15
 * Time: 2:07
 */

class Log
{
   static function printArray(array $arr)
   {
      echo '<pre>';
      print_r($arr);
      echo '</pre>';
   }

   static function writeLog($msg)
   {
      if(!Settings::$log)
         return false;

      $fileName = Route::getDir(Settings::$logDir).'/log_'.date('d-m-Y');

      file_put_contents($fileName, date('H:i:s').'->'.$msg."\r\n", FILE_APPEND);
      return true;
   }
} 