<?php

/**
 * Created by IntelliJ IDEA.
 * User: takeru
 * Date: 02.07.15
 * Time: 21:57
 *
 * Класс работы с уведомлениями.
 */
class Notice
{
   /**
    * Метод добавляет новое уведомление в базу данных
    *
    * @param string      $msg     само сообщение(не больше 256 символов)
    * @param bool        $confirm переменная указывает нужно ли подтверждение прочтения от пользователя
    * @param null|string $start   дата начала показа уведомления, может быть не указан, в этом случае показывается с
    *                             момента создания
    * @param null|string $end     дата окончаний действия уведомления, может быть не указан, в этом случае показывается
    *                             до момента удаления из базы
    *
    * @return bool
    */

   static function addNotice($msg, $confirm, $start = null, $end = null)
   {
      if($start)
      {
         $startArg = '`startDate`';
         $start = MySQLi::checkString($start);
      }
      if($end)
      {
         $endArg = '`stopDate`';
         $end = MySQLi::checkString($end);
      }

      return MySQLi::query('INSERT INTO `'.Settings::$DB_PREFIX.'_notice` (`msg`, `confirm`'.(empty($startArg) ? '' : ','.$startArg).(empty($endArg) ? '' : ','.$endArg).')
      VALUES (\''.MySQLi::checkString($msg).'\', \''.intval($confirm).'\''.($start ? ',\''.$start.'\'' : '').($end ? ',\''.$end.'\'' : '').')');
   }

   /**
    * Метод редактирования уже существующего уведомления. Метод содержит все переменные, что и для создания уведомления,
    * но здесь некоторые параметры могут не указываться. Если ни один параметр не будет указан, то изменений в базу
    * внесено не будет
    *
    * @param int|array(int) $id идентификатор уведомления, может быть как числом, так и массивом чисел, в этом случае
    *                           изменятся все указанные уведомления
    * @param null|sting     $msg
    * @param null|bool      $confirm
    * @param null|string    $start
    * @param null|string    $end
    *
    * @return bool
    */

   static function redactNotice($id, $msg = null, $confirm = null, $start = null, $end = null)
   {
      if(is_array($id))
      {
         $id = array_map(function ($var) { return intval($var); }, $id);
         $id = implode(',', $id);
      }
      else
         $id = intval($id);

      $args = array();

      if($msg)
         $args[] = '`msg` = \''.MySQLi::checkString($msg).'\'';
      if($start)
         $args[] = '`startDate` = \''.MySQLi::checkString($start).'\'';
      if($end)
         $args[] = '`stopDate` = \''.MySQLi::checkString($end).'\'';
      if($confirm)
         $args[] = '`confirm` = \''.intval($confirm).'\'';

      if($args)
         return MySQLi::query('UPDATE `'.Settings::$DB_PREFIX.'_notice` SET '.(implode(',', $args)).' WHERE `id` IN ('.$id.')');
   }

   /**
    * Метод удаления уведомления или группы уведомлений из базы, а так же удаляются записи о подтверждении от
    * пользователей
    *
    * @param null|int|array(int) $id идентификатор уведомления. Если не указан, то удаляются все записи из таблицы,
    *                                если указано число или массив чисел, то удаляются только указанные записи
    *
    * @return bool
    */

   static function deleteNotice($id = null)
   {
      if(is_array($id))
      {
         $id = array_map(function ($var) { return intval($var); }, $id);

         $id = implode(',', $id);
      }
      else
         $id = intval($id);

      return MySQLi::query('DELETE FROM `'.Settings::$DB_PREFIX.'_notice` WHERE `id` IN ('.$id.')');
   }

   /**
    * Метод подтверждает прочтение указанного уведомления определенным пользователем
    *
    * @param int $noticeId идентификатор уведомления
    * @param int $uid      идентификатор пользователя
    *
    * @return bool
    */

   static function signNotice($noticeId, $uid)
   {
      return MySQLi::query('REPLACE INTO `'.Settings::$DB_PREFIX.'_notice_users` (`noticeId`, `uid`)
      VALUES (\''.intval($noticeId).'\', \''.intval($uid).'\')');
   }

   /**
    * Метод возвращает все активные уведомления для конкретного пользователя, кроме тех, которым нужно подтверждение,
    * и которые пользователь уже подтвердил
    *
    * @param int $uid идентификатор пользователя
    *
    * @return array ассоциативный массив всех параметров всех уведомлений
    * @throws Exception в случае отсутствия уведомлений в базе, выбрасывает исключение
    */

   static function getNotice($uid)
   {
      $result = MySQLi::query('SELECT `noticeId` FROM `'.Settings::$DB_PREFIX.'_notice_users` WHERE `uid` = '.intval($uid));

      if($result->num_rows)
         while($row = $result->fetch_assoc())
            $confirmed[] = $row['noticeId'];

      $result = MySQLi::query('SELECT * FROM `'.Settings::$DB_PREFIX.'_notice`
      WHERE (`startDate` <= NOW() AND `stopDate` >= NOW() OR `startDate` IS NULL OR `stopDate` IS NULL)'.(!empty($confirmed) ? ' AND `id` NOT IN('.implode(',', $confirmed).')' : null).'');

      if(!$result->num_rows)
         throw new Exception('Нет уведомлений');

      return $result->fetch_all(MYSQL_ASSOC);
   }
}