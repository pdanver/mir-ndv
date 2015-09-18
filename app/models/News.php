<?php

/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 02.07.15
 * Time: 10:32
 *
 * Базовый класс для работы с новостями. Для работы требуется только доступ к базе данных и наличие нужных таблиц ы ней.
 */
class News
{
   /**
    * Метод часовой выборки новостей. По умолчанию берется текущие дата и время
    *
    * @param string $date  строка даты в формате пендосов
    * @param        $theme см. getNewsByDate()
    *
    * @return array см. getNews()
    */

   static function getHourlyNews($date = '', $theme = null)
   {
      if(empty($date))
         $date = date('Y-m-d H:00:00');

      return self::getNewsByDate($date, date('Y-m-d H:i:s', strtotime($date.' + 59 MINUTES +59 SECONDS')), $theme);
   }

   /**
    * Метод поиска по теме и/или промежутку времени(даты). Даты записываются в пендосовском формате 'Y-m-d H:i:s'
    *
    * @param string                     $from  Строка даты от которой начинается отсчет поиска
    * @param string                     $to    Строка даты окончания поиска
    * @param null|string|int|array(int) $theme может быть как числом, строкой, так и массивом чисел для поиска
    *
    * @return array см. getNews()
    * @throws Exception см. getNews()
    */

   static function getNewsByDate($from, $to, $theme = null)
   {
      if($theme)
      {
         $arg = '`themeId` ';
         if(is_array($theme) && is_numeric($theme[0]))
         {
            $theme = array_map(function ($var) { return intval($var); }, $theme);
            $theme = 'IN ('.implode(',', $theme).')';
         }
         else if(is_string($theme))
            $theme = '= (SELECT `id` FROM `'.Settings::$DB_PREFIX.'_news_themes` WHERE `name` = \''.MySQLi::checkString($theme).'\')';
         else
            $theme = '= '.intval($theme);

         $theme = $arg.$theme.' AND ';
      }

      return self::getNews(null, $theme.'`date` BETWEEN \''.MySQLi::checkString($from).'\' AND \''.MySQLi::checkString($to).'\'');
   }

   /**
    * Метод возвращает все или несколько определенных записей. Поиск ведется только по тем записям, которые помечены
    * одобренными (approved).
    *
    * @param null|string|int|array(string) $id    Идентификатор поиска, представляющий собой поле `title`
    * @param string                        $where Второй параметр поиска, используется, если первый установлен в null
    *
    * @return array Возвращает ассоциативный массив записей из базы данный, удовлетворяющим условиям поиска
    * @throws Exception если поиск вернул пустой массив, то будет выбрашено исключение
    */

   static function getNews($id = null, $where = null)
   {
      if($id)
      {
         $where = ' AND ';
         if(is_numeric($id))
            $where .= '`id` = '.$id;
         else if(is_array($id) && is_string($id[0]))
         {
            $id = array_map(function ($var) { return MySQLi::checkString($var); }, $id);
            $where .= '`title` IN (\''.implode('\',\'', $id).'\')';
         }
         else
            $where .= '`title` LIKE \'%'.MySQLi::checkString($id).'%\'';
      }

      if($where)
         $where = ' AND '.$where;

      $result = MySQLi::query('SELECT * FROM `'.Settings::$DB_PREFIX.'_news` WHERE `approved` = 1'.$where);

      if(!$result ->num_rows)
         throw new Exception('Нет новостей вовсе');

      return $result->fetch_all(MYSQLI_ASSOC);
   }

   /**
    * Метод дневной выборки новостей. По умолчанию берется текущая дата
    *
    * @param string $date  см. getNewsByDate()
    * @param null   $theme см. getNewsByDate()
    *
    * @return array см. getNews()
    */

   static function getDailyNews($date = '', $theme = null)
   {
      if(empty($date))
         $date = date('Y-m-d 00:00:00');

      return self::getNewsByDate($date, Date('Y-m-d 23:59:59', strtotime($date)), $theme);
   }

   /**
    * Метод недельной выборки новостей. Работает аналогично другим выборкам
    *
    * @param string $date
    * @param null   $theme
    *
    * @return array
    */

   static function getWeeklyNews($date = '', $theme = null)
   {
      if(empty($date))
         $date = date('Y-m-d 00:00:00', strtotime("last Monday"));

      return self::getNewsByDate($date, Date('Y-m-d 23:59:59', strtotime($date.' + 6 DAYS')), $theme);
   }

   /**
    * Метод месячной выборки новостей. Работает аналогично остальным
    *
    * @param string $date
    * @param null   $theme
    *
    * @return array
    */

   static function getMonthlyNews($date = '', $theme = null)
   {
      if(empty($date))
         $date = date('Y-m-01 00:00:00');

      return self::getNewsByDate($date, Date('Y-m-t 23:59:59', strtotime($date)), $theme);
   }

   /**
    * Метод годовой выборки. Рвботает аналогично остальным выборкам.
    *
    * @param string $date
    * @param null   $theme
    *
    * @return array
    */

   static function getYearlyNews($date = '', $theme = null)
   {
      if(empty($date))
         $date = date('Y-01-01 00:00:00');

      return self::getNewsByDate($date, Date('Y-12-31 23:59:59', strtotime($date)), $theme);
   }

   /**
    * Метод поиска новостей по теме
    *
    * @param string|int|array(int) $id
    *
    * @return array
    * @throws Exception
    */

   static function getNewsByTheme($id)
   {
      if(is_string($id))
         $id = '= (SELECT `id` FROM `'.Settings::$DB_PREFIX.'_news_themes` WHERE `name` LIKE \''.MySQLi::checkString($id).'\')';
      else if(is_array($id))
      {
         $id = array_map(function ($var) { return intval($var); }, $id);
         $id = 'IN ('.implode(',', $id).')';
      }
      else
         $id = '= '.intval($id);

      return self::getNews(null, '`themeId` '.$id);
   }

   /**
    * Метод возвращает неподтвержденные новости из базы для их проверки и последующей публикации.
    * Преимущественно для админки
    *
    * @return array ассоциативный массив
    * @throws Exception если нет неопубликованных новостей
    */

   static function getNotApprovedNews()
   {
      $result = MySQLi::query('SELECT * FROM `'.Settings::$DB_PREFIX.'_news` WHERE `approved` = 0');

      if(!$result ->num_rows)
         throw new Exception('Нет неодобренных новостей');

      return $result->fetch_all(MYSQLI_ASSOC);
   }

   /**
    * Метод получения списка всех или нескольких тем из существующих в базе данных
    *
    * @param null|string|array(string) $id
    *
    * @return array ассоциативный массив (id, name)
    * @throws Exception
    */

   static function getNewsThemes($id = null)
   {
      if($id)
      {
         $arg = ' WHERE `name` IN (';
         if(is_array($id))
            $id = '\''.implode('\',\'', array_map(function ($var) { return MySQLi::checkString($var); }, $id)).'\'';
         else
            $id = '\''.MySQLi::checkString($id).'\'';

         $id = $arg.$id.')';
      }
      $result = MySQLi::query('SELECT * FROM `'.Settings::$DB_PREFIX.'_news_themes`'.$id);

      if(!$result ->num_rows)
         throw new Exception('Нет доступных тем');

      return $result->fetch_all(MYSQL_ASSOC);
   }

   /**
    * Метод добавления новости в базу данных. По умолчанию добавленные записи не видны в поиске(approved = 0),
    * пока их не обобрят (showHideNews()).
    *
    * @param int|string  $theme число или строка темы новости
    * @param string      $title Заголовок новости (не более 256 символов)
    * @param string      $msg   текст новости(может содержать разметку и прочие элементы)
    * @param null|string $date  дата добавления. По умолчанию добавляет текущую дату
    */

   static function addNews($theme, $title, $msg, $date = null)
   {
      if(is_string($theme))
         $theme = '(SELECT `id` FROM `'.Settings::$DB_PREFIX.'_news_themes` WHERE `name` = \''.MySQLi::checkString($theme).'\')';
      else
         $theme = intval($theme);

      $title = MySQLi::checkString($title);
      $msg = MySQLi::checkString($msg);

      if($date)
      {
         $arg = ', `date`';
         $date = ', '.MySQLi::checkString($date);
      }


      MySQLi::query('INSERT INTO `'.Settings::$DB_PREFIX.'_news` (`themeId`, `title`, `msg`'.(isset($arg) ? $arg : null).')
                         VALUES ('.$theme.', \''.$title.'\', \''.$msg.'\''.$date.')');
   }

   /**
    * Метод редактирования существующей записи.
    * Если параметры не указаны, то обновление не будет выполнено.
    *
    * @param int             $id    идентификатор записи в базе
    * @param null|string|int $theme номер или название темы
    * @param null|string     $title заголовок
    * @param null|string     $msg   тело
    * @param null|string     $date  дата
    */

   static function redactNews($id, $theme = null, $title = null, $msg = null, $date = null)
   {
      $arg = array();
      if($title)
         $arg[] = '`title` = \''.MySQLi::checkString($title).'\'';
      if($msg)
         $arg[] = '`msg` = \''.MySQLi::checkString($msg).'\'';
      if($date)
         $arg[] = '`date` = \''.MySQLi::checkString($date).'\'';
      if($theme)
         $arg[] = '`themeId` = '.(is_numeric($theme) ? intval($theme) : '(SELECT `id` FROM `news_themes` WHERE `name` = \''.MySQLi::checkString($theme).'\')');

      if($arg)
         MySQLi::query('UPDATE `'.Settings::$DB_PREFIX.'_news` SET '.implode(',', $arg).' WHERE `id` = '.intval($id));
   }

   /**
    * Метод разрешения/запрещения новости
    *
    * @param int|array(int) $news идентификаторы новостей.
    * @param bool           $show разрешить или запретить
    */

   static function showHideNews($news, $show = true)
   {
      if(is_array($news))
      {
         $news = array_map(function ($var) { return intval($var); }, $news);
         $news = implode(',', $news);
      }
      else
         $news = intval($news);

      MySQLi::query('UPDATE `'.Settings::$DB_PREFIX.'_news` SET `approved` = '.intval($show).' WHERE `id` IN ('.$news.')');
   }
}