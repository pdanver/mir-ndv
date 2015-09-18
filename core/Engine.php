<?php

/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 27.05.15
 * Time: 9:52
 */

class Engine
{
   static function loadSettings($var = null)
   {
      Database::DB()->select('settings');
      if($var)
         Database::DB()->where('var', $var, 'IN');
      Database::DB()->exec();

      if(Database::result()->num_rows == 0)
         throw new Exception('Ошибка при загрузке настроек');

      $arg = array();
      while($row = Database::result()->fetch_assoc())
         $arg[$row['var']] = $row['val'];

      if(is_string($var))
         return $arg[$var];

      return $arg;
   }

   static function loadIPList()
   {
      $result = MySQLi::query('SELECT * FROM `engine_ip_list`');

      if($result ->num_rows > 0)
         return $result ->fetch_all(MYSQLI_ASSOC);

      return null;
   }

   static function loadForbidden()
   {
      $result = MySQLi::query('SELECT * FROM `engine_forbidden_stuff`');

      if($result ->num_rows > 0)
         return $result ->fetch_all(MYSQLI_ASSOC);

      return null;
   }

   static function checkIp($ip)
   {
      $result = MySQLi::query('SELECT * FROM `engine_ip_list` WHERE `ip` = \''.MySQLi::checkString($ip).'\'');

      if($result ->num_rows > 0)
         return $result ->fetch_assoc();

      return false;
   }

   static function checkForbiddenStuff($stuff)
   {
      $result = MySQLi::query('SELECT * FROM `engine_forbidden_stuff`
            WHERE `name` IN(\''.$stuff['os'].'\', \''.$stuff['browser'].'\') OR `version` = \''.$stuff['version'].'\'');

      if($result ->num_rows > 0)
         return $result ->fetch_assoc()['msg'];

      return false;
   }

   static function checkController($controller)
   {
      $result = MySQLi::query('SELECT * FROM `engine_controllers` WHERE `name` = \''.MySQLi::checkString($controller).'\'');

      if($result ->num_rows == 0)
         throw new Exception('Неизвестный контроллер');

      return $result ->fetch_assoc()['status'];
   }

   static function loadControllers()
   {
      $result = MySQLi::query('SELECT A.`id`, A.`name`, A.`img`, A.`date`, A.`status`, B.`'.Controller::$lang.'` AS `alias`
                                   FROM `engine_controllers` A, `engine_lang` B
                                   WHERE A.`alias` = B.`id` ORDER BY A.`id`');

      if($result ->num_rows == 0)
         throw new Exception('Ошибка доступа к контрорллерам');

      return $result ->fetch_all(MYSQLI_ASSOC);
   }

   static function loadURIMasks($uri = null)
   {
      $result = MySQLi::query('SELECT A.`uri`, B.`'.Controller::$lang.'` AS `alias`
                                   FROM `engine_uri_mask` A, `engine_lang` B
                                   WHERE A.`alias` = B.`id`'.(empty($uri) ? '' : ' `uri` = \''.MySQLi::checkString($uri).'\''));

      if($result ->num_rows == 0)
         throw new Exception('Нет масок');

      if(empty($uri))
         return $result ->fetch_all();
      else
         return $result ->fetch_assoc()[Controller::$lang];
   }

   static function parseURI($word)
   {
      $result = MySQLi::query('SELECT A.`uri`, B.`'.Controller::$lang.'` AS `alias`
                                   FROM `engine_uri_mask` A, `engine_lang` B
                                   WHERE A.`alias` = B.`id` AND B.`'.Controller::$lang.'` LIKE \''.MySQLi::checkString($word).'\'');

      if($result ->num_rows == 0)
         throw new Exception('Ошибка парсинга');

      return $result->fetch_assoc()['uri'];
   }

   static function getIpCount()
   {
      return self::get('SELECT COUNT(*) FROM `engine_ip_list`');
   }

   static function get($query)
   {
      $result = MySQLi::query($query);

      if($result ->num_rows > 0)
         return $result ->fetch_assoc()['COUNT(*)'];

      return 0;
   }

   static function getStuffCount()
   {
      return self::get('SELECT COUNT(*) FROM `engine_forbidden_stuff`');
   }

   static function getControllersCount()
   {
      return self::get('SELECT COUNT(*) FROM `engine_controllers`');
   }

   static function getActivedControllersCount()
   {
      return self::get('SELECT COUNT(*) FROM `engine_controllers` WHERE `status` = 2');
   }

   static function getLanguageList()
   {
      $result = MySQLi::query('SELECT * FROM `engine_lang` LIMIT 1');

      $arr = array();
      $t = $result ->fetch_fields();
      for($i = 2; $i < count($t); $i++)
         $arr[] = $t[$i]->name;

      return $arr;
   }

   static function JSON($array)
   {
      if(is_array($array))
      {
         $array = array('st'=>'good', 'content'=>$array);
      }

      return json_encode($array);
   }
}