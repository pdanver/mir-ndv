<?php

/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 15.04.15
 * Time: 9:33
 */

class DatabaseDriver implements SessionHandlerInterface
{
   function open($save_path, $sessionName)
   {
      if(session_id())
         Database::DB()->replace('sessions', array('ssid' => session_id()));
      return true;
   }

   function read($session_id)
   {
      Database::DB()->select('sessions', 'data')->where('ssid', $session_id)->limit(1)->exec();
      return Database::get('data');
      return true;
   }

   function write($session_id, $session_data)
   {
      Database::DB()->replace('sessions', array('ssid' => $session_id, 'data' => $session_data));
      return true;
   }

   function destroy($session_id)
   {
      Database::DB()->delete('sessions')->where('ssid', $session_id)->exec();
      return true;
   }

   function gc($maxLifetime)
   {
      Database::DB()->delete('sessions')->where('`date` < DATE_SUB(NOW(),INTERVAL '.$maxLifetime.' SECOND)')->exec();
      return true;
   }

   function close()
   {
      Database::close();
      return true;
   }

//   function setAuthorization($uid)
//   {
////      MySQLi::query('UPDATE `engine_sessions` SET `uid` = \''.md5($_SERVER['HTTP_USER_AGENT'].$uid.$_SERVER['REMOTE_ADDR']).'\'
////                        WHERE `ssid` = \''.session_id().'\'');
//   }
//
//   function isAuthorized()
//   {
////      $result = MySQLi::query('SELECT * FROM `engine_sessions` WHERE `ssid` = \''.session_id().'\'');
////
////      if($result ->num_rows > 0)
////         return $result ->fetch_object()->uid;
//
//      return false;
//   }
//
//   function getConnectedCount()
//   {
////      $result = MySQLi::query('SELECT count(*) FROM `engine_sessions`
////                                   WHERE NOW() < `date` + INTERVAL '.Settings::$sessionLifetime.' SECOND');
////
////      if($result ->num_rows > 0)
////         return $result ->fetch_assoc()['count(*)'];
//
//      return 0;
//   }
}