<?php
/**
 * Created by IntelliJ IDEA.
 * User: takeru
 * Date: 24.07.15
 * Time: 3:53
 */

class Session
{
   private static $driver;

   static function start()
   {
      include_once __DIR__.'/sessionDrivers/'.Settings::$sessionDriver.'.php';

      //self::$driver = new Settings::$sessionDriver();
      //session_set_save_handler(array(self::$driver, 'open'),array(self::$driver, 'close'),array(self::$driver, 'read'),
      //            array(self::$driver, 'write'),array(self::$driver, 'destroy'),array(self::$driver, 'gc'));

      register_shutdown_function('session_write_close');

      if(in_array(Settings::$session_hash, hash_algos()))
         ini_set('session.hash_function', Settings::$session_hash);

      ini_set('session.hash_bits_per_character', Settings::$hash_bits_per_character);

      $cookieParams = session_get_cookie_params();

      session_set_cookie_params(Settings::$sessionLifetime, $cookieParams["path"], $cookieParams["domain"], Settings::$secure, Settings::$httpOnly);

      session_name(Settings::$NAME);
      //буферизуем заголовок
	  ob_start();
	  //включаем CORS, если указано в настройках /*
	  if(isset(Settings::$CORS)&&(Settings::$CORS)&&(!empty($_SERVER['HTTP_ORIGIN']))) {
	    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');	
	  } 
	  //включаем сессию
	  session_start();
	  ob_end_flush();  //посылаем заголовок
   }
} 