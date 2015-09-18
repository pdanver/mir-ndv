<?php

/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 10.06.15
 * Time: 14:25
 */
class Settings
{
   static $ENGINE;
   /* Database */
   static $DB_DRIVER = 'MySQLiDriver';
   static $DB_host = 'mir-ndv.mysql';
   static $DB_username = 'mir-ndv_mysql';
   static $DB_password = 'vDX_x2Ks';
   static $DB_name = 'mir-ndv_db';
   static $DB_PREFIX = 'engine';
   static $CHARSET = 'utf8';
   /* admin */
   static $ADMIN_REALM = 'env-0714947';
   static $SHOW_ERRORS = true;
   /* Route */
   static $NAME = 'SITE';
   static $controllersDir = 'controllers';
   static $modelsDir = 'models';
   static $viewsDir = 'app/views';
   static $DEFAULT_CONTROLLER = 'main';
   static $log = 2; // 0 - Не пишем логи, 1 - пишем только предупреждения, 2 - пишем всё
   static $logDir = array('..', 'logs');
   static $default_timezone = 'Europe/Moscow';
   static $DEBUG = true;
   /* SessionManager */
   static $sessionDriver = 'DatabaseDriver';
   static $sessionLifetime = 900;
   static $session_hash = 'sha512';
   static $httpOnly = true;
   static $secure = false;
//    How many bits per character of the hash.
//    The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, "-", ",").
   static $hash_bits_per_character = 5;
   
   static $password_level = 2;  //уровень стойкости пароля (от 0 до 4)
   static $CORS = false;  //включен ли Cross-Origin Resource Sharing standard (кроссдоменные запросы)
}