<?php

/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 08.04.15
 * Time: 14:06
 */

class MySQLiDriver
{
   private $queryResult, $query, $mysqli,
      $allowedOperators = array('=', '<=', '>=', '>', '<', '!=', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'LIKE');

   function __construct($host, $username, $passwd, $db)
   {
      $this->mysqli = new mysqli($host, $username, $passwd, $db);

      if(!empty($this->mysqli->connect_errno))
         throw new DatabaseException('Не возможно соединиться с базой данных', self::errno());

      $this->mysqli->set_charset(Settings::$CHARSET);
   }

   function __destruct()
   {
      if(is_object($this->mysqli))
         $this->mysqli->close();
   }

   /**
    * Метод подготавливает запрос для выполнения
    * ВНИМАНИЕ! кавычки в данном методе не экранируются, поэтому должны экранироваться отдельно
    *
    * @param $sql - строка запроса
    *
    * @return $this
    */

   function query($sql)
   {
      $this->query = $sql;

      return $this;
   }

   /**
    * Метод выполняет запрос к базе данных.
    *
    * @return mysqli_result - объект или true в случае успешного запроса
    * @throws DatabaseException - в случае ошибки (false ответа)
    */

   function exec()
   {
      $res = $this->mysqli->query($this->query);
      if($res === false)
         throw new DatabaseException($this->query, self::errno());
      $this->queryResult = $res;
      return $this;
   }

   /**
    * Сразу выполняет переданный запрос
    *
    * @param $sql
    *
    * @return mixed
    */

   function queryExec($sql)
   {
      $this->query($sql)->exec();
      return $this;
   }

   /**
    * Сбрасывает переменные
    */

   function reset()
   {
      $this ->query = '';
   }

   /**
    * Метод возвращает последний подготовленный запрос
    *
    * @return mixed
    */

   function lastQuery()
   {
      return $this->query;
   }

   /**
    * Возвращает результат последнего запроса
    *
    * @return mixed
    */

   function lastResult()
   {
      return $this->queryResult;
   }

   /**
    * Возвращает автоматически генерируемый ID, используя последний запрос
    * В случае, если в одном запросе вставлено больше одной строки, то метод вернет только идентификатор первой строки
    *
    * @return int - 0, если не было затронуто поле auto_increment,
    * string - если значение превышает максимальное значение int
    */

   function lastInsertId()
   {
      return $this->mysqli->insert_id;
   }

   function error()
   {
      return $this->mysqli->error;
   }

   function errno()
   {
      return $this->mysqli->errno;
   }

   /**
    * Возвращаем строку-число записей в таблице, после подсчета с помошью оператора SQL_CALC_FOUND_ROWS
    *
    * @return string
    */

   function foundRows()
   {
      return $this->query('SELECT FOUND_ROWS() as `cnt`')->exec()->getRow('cnt');
   }

   /**
    * Экранирует специальные символы в строке для использования в SQL
    *
    * @param string $str
    *
    * @return string
    */

   public function escape($str)
   {
      return $this->mysqli->real_escape_string($str);
   }

   /**
    * Выполняет INSERT запрос в базу данных
    *
    * @param string $tableName - название таблицы без префикса
    * @param array $params - ассоциативный массив параметров 'columnName' => 'value'
    *
    * @return int - идентификатор внесенной записи
    */

   function insert($tableName, array $params)
   {
      return $this->insertTmpl($tableName, $params, 'INSERT INTO');
   }

   /**
    * Работает анаоргично insert, но заменяет найденые совпадения
    *
    * @param string $tableName
    * @param array $params
    *
    * @return int
    */

   function replace($tableName, array $params)
   {
      return $this->insertTmpl($tableName, $params, 'REPLACE');
   }

   /**
    * Шаблонный метод insert/replace
    * Экранирует как ключи, так и значения всех переданных массивов, но не является панацеей от криворукости.
    *
    * @param string $tableName
    * @param array $params
    * @param string $type
    *
    * @return int
    */

   private function insertTmpl($tableName, array $params, $type)
   {
      $args = '';
      $values = '';

      if(is_array($params) && is_array(reset($params)))
      {
         $allKeys = array();
         foreach($params as $val)
            foreach($val as $key=>$vall)
               $allKeys[$this->escape($key)] = 'NULL';

         $args = '`'.implode('`,`', array_keys($allKeys)).'`';
         $arguments = array();

         foreach($params as $val)
         {
            if(!isset($val))
               continue;

            if(is_array($val))
            {
               foreach($allKeys as &$value)
                  $value = 'NULL';

               foreach($val as $key=>$var)
                  $allKeys[$this->escape($key)] = $this->escape($var);

               $arguments[] = '\''.implode('\',\'', $allKeys).'\'';
            }
         }
         $values = implode('),(', $arguments);
      }
      else
      {
         foreach($params as $key => $val)
         {
            $args .= '`'.$this->escape($key).'`,';
            $values .= '\''.$this->escape($val).'\',';
         }
         $values = substr($values, 0, -1);
         $args = substr($args, 0, -1);
      }

      $this->queryExec($type.' `'.Settings::$DB_PREFIX.'_'.$this->escape($tableName).'`('.$args.')VALUES('.$values.')');

      return $this->lastInsertId();
   }

   /**
    * Подготавливает запрос на обновление базы данных
    *
    * @param string $tableName - название таблицы без префикса
    * @param array $params - ассоциативный массив параметров
    *
    * @return $this
    * @throws mysqli_sql_exception - если массив параметров не ассоциативный
    */

   function update($tableName, array $params)
   {
//      if(!is_assoc($params))
//         throw new mysqli_sql_exception('Не верные данные');

      $this->query = 'UPDATE `'.Settings::$DB_PREFIX.'_'.$this->escape($tableName).'` SET ';

      foreach($params as $key => $val)
         $this->query .= '`'.$this->escape($key).'` = \''.$this->escape($val).'\',';

      $this->query = substr($this->query, 0, -1);

      return $this;
   }

   /**
    * Добавляет к запросу блок WHERE
    *
    * @param string $col - название колонки
    * @param null | string | int | array $val параметр значения указанной колонки
    * @param null | string $op - оператор, один из разрешенных (allowedOperators)
    * @param null $cond - параметр соединения блоко, может быть 'AND', 'OR'
    *
    * Применение:
    *
    * where('lastLogin = createdAt');
    * Даст: ...WHERE lastLogin = createdAt
    *
    * where('id', 1, null, 'AND');
    * where('login', 'admin');
    * Даст: ... WHERE `id`='1' AND `login`='admin'
    *
    * where('id', 50, ">=");
    * where('id', array ('>=' => 50))
    * Даст: ..WHERE `id` >= '50'
    *
    * where('id', array(4, 20), 'BETWEEN')
    * where('id', array('BETWEEN' => array(4, 20)))
    * Даст: .. WHERE `id` BETWEEN '4' AND '20'
    *
    * where('id', array(1, 5, 27, -1, 'd'), 'IN')
    * where('id', array( 'IN' => array(1, 5, 27, -1, 'd')))
    * Даст: ... WHERE `id` IN ('1', '5', '27', '-1', 'd')
    *
    * @return $this
    * @throws mysqli_sql_exception - если первым параметром передана не строка
    */

   function where($col, $val = null, $op = null, $cond = null)
   {
      if(!is_string($col))
         throw new mysqli_sql_exception('неверные параметры');

      if(strpos($this->query, 'WHERE') === false)
         $this->query .= ' WHERE ';

      if(!in_array($op, $this->allowedOperators)) $op = $this->allowedOperators[0];

      if(!isset($val))
         $this->query .= $this->escape($col);
      else
         if(is_string($val) || is_numeric($val))
         {
            if(is_string($val)) $op = $this->allowedOperators[10];
            $this->query .= '`'.$this->escape($col).'` '.$op.' \''.$this->escape($val).'\'';
         }
         else
            if(is_array($val))
            {
               if(($key = key($val)) != "0")
               {
                  if(in_array($key, $this->allowedOperators))
                     $op = $key;
                  $val = $val[$key];
               }
               else
                  $op = $this->allowedOperators[6];

               if(is_array($val) && $op != $this->allowedOperators[8] && $op != $this->allowedOperators[9])
               {
                  $tmp = '(';
                  foreach($val as $v)
                     $tmp .= '\''.$this->escape($v).'\',';
                  $val = $tmp;
                  $val = substr_replace($val, ')', -1);
               }
               else
                  if(is_array($val) && count($val) == 2 && $op == $this->allowedOperators[8] || $op == $this->allowedOperators[9])
                     $val = $this->escape($val[0]).' AND '.$this->escape($val[1]);
                  else
                     $val = '\''.$this->escape($val).'\'';

               $this->query .= '`'.$this->escape($col).'` '.$op.' '.$val;
            }

      if($cond && in_array($cond, array('AND', 'OR')))
         $this->query .= ' '.$cond;
      return $this;
   }

   /**
    * Добавляет в запрос параметр AND
    *
    * @return $this
    */

   function sqlAND()
   {
      if(strpos($this->query, 'WHERE') !== false)
         $this->query .= ' AND ';
      return $this;
   }

   /**
    * Добавляет в запрос параметр OR
    *
    * @return $this
    */

   function sqlOR()
   {
      if(strpos($this->query, 'WHERE') !== false)
         $this->query .= ' OR ';
      return $this;
   }

   /**
    * Метод удаления записей из базы данных
    *
    * @param $tableName - имя таблицы без префикса
    *
    * @return $this
    */

   function delete($tableName)
   {
      $this->query = 'DELETE FROM `'.Settings::$DB_PREFIX.'_'.$this->escape($tableName).'`';
      return $this;
   }

   /**
    * Очищает указанную таблицу
    *
    * @param string $tableName
    *
    * @return $this
    */

   function truncate($tableName)
   {
      $this->query = 'TRUNCATE TABLE `'.Settings::$DB_PREFIX.'_'.$this->escape($tableName).'`';
      return $this;
   }

   /**
    * Подготавливает запрос выборки
    *
    * @param string $tableName - название таблицы без префикса
    * @param string | array $params названия колонок, которые нужно вернуть
    * @param bool   $countTotal - параметр заставляет считать общее число записей в выборке, минуя лимиты(LIMIT)
    *
    * @return $this
    */

   function select($tableName, $params = '*', $countTotal = false)
   {
      if(is_array($params))
      {
         $tmp = '';
         foreach($params as $val)
            $tmp .= '`'.$this->escape($val).'`,';
         $tmp = substr($tmp, 0, -1);
         $params = $tmp;
      }
      else
         $params = $this->escape($params);

      if($countTotal)
         $params = 'SQL_CALC_FOUND_ROWS '.$params;

      $this->query = 'SELECT '.$params.' FROM `'.Settings::$DB_PREFIX.'_'.$this->escape($tableName).'`';

      return $this;
   }

   /**
    * Добавляет к запросу параметры смещения и/или ограничения
    *
    * @param int $lim - ограничение выборки
    * @param int $offset - смещение выборки
    *
    * @return $this
    */

   function limit($lim, $offset = 0)
   {
      $this->query .= ' LIMIT '.($offset?intval($offset).', ':'').intval($lim);
      return $this;
   }

   /**
    * Добавляет к запросу параметр сортировки
    *
    * @param string $column - название колонки сортировки
    * @param bool $down - направление сортировки, по умолчанию сортируется по возрастанию
    *
    * @return $this
    */

   function order($column, $down = false)
   {
      if(is_array($column)) {
         $tmp = "";
         foreach($column as $col) {
            if(!empty($tmp)) $tmp .= ",";
            $tmp .= '`'.$this->escape($col).'`';
         }
         $this->query .= ' ORDER BY '.$tmp.($down?' DESC':'');
      } else if(is_string($column)) {
         $column = ''.$column;
         $this->query .= ' ORDER BY `'.$this->escape($column).'`'.($down?' DESC':'');
      }
      return $this;
   }

   /**
    * Добавляет параметр группировки
    *
    * @param string $column - название колонки группировки
    *
    * @return $this
    */

   function group($column)
   {
      $column = ''.$column;
      $this->query .= 'GROUP BY `'.$this->escape($column).'`';
      return $this;
   }

   /**
    * Начало транзакции
    *
    * @return $this
    */

   function transaction()
   {
      $this->mysqli->autocommit(false);
      $this->mysqli->begin_transaction();
      return $this;
   }

   /**
    * Подтверждаем успешность и завершаем транзакцию
    */

   function commit()
   {
      $this->mysqli->commit();
      $this->mysqli->autocommit(true);
   }

   /**
    * Отменяем и завершаем транзакцию
    */

   function rollback()
   {
      $this->mysqli->rollback();
      $this->mysqli->autocommit(true);
   }

   /**
    * Реализует мета-метод получения результата запроса. Результат запроса очищается после обращения к методам.
    * Реализованные варианты:
    *  - get() - вернет массив всех строк запроса.
    *  - getRow('field') - вернет строку значения поля 'field'
    *  - getRow(array('field1', $field2, ...)) - вернет ассоциативный массив с ключами 'field1', 'field2',...
    *  - getObject('field') - аналогично getRow('field');
    *  - getObject(array('field1','field2', ...)) - вернет объект с полями только из массива
    *  - getField() - вернет информацию об одном столбце результата в виде объекта
    *  - getFields() - вернет массив объектов всех столбцов результата
    *  - getCount() - вернет число строк, вернувшихся с запроса. Не удаляет результат запроса после обращения
    *
    * @param $name название метода
    * @param $args - аргументы (используются только в getRow и getObject)
    *
    * @return array | null | object
    * @throws BadMethodCallException - выбрасывается, если происходит попытка вызвать методы, не описанные в шаблоне
    */

   function __call($name, $args)
   {
      if(strpos($name, 'get') === false)
         throw new BadMethodCallException(get_called_class().' - '.__FUNCTION__);

      $result = null;
      if($this->queryResult->num_rows == 0)
         return $result;

      switch(substr($name, 3))
      {
         case '':
            $result =  $this->queryResult->fetch_all(MYSQL_ASSOC);
            break;
         case 'Row':
            $fields = array_shift($args);
            $result = $this->queryResult->fetch_assoc();  //[$fields] - изменил
            if(is_string($fields) && array_key_exists($fields, $result))
               $result = $result[$fields];
            else
               if(is_array($fields))
                  $result = array_intersect_key($result, array_flip($fields));
            break;
         case 'Object':
            $fields = array_shift($args);
            $result =  $this->queryResult->fetchObject();
            if(is_string($fields) && array_key_exists($fields, $result))
               $result = $result->{$fields};
            else
               if(is_array($fields))
                  foreach($result as $key=>$val)
                     if(!in_array($key, $fields))
                        unset($result->$key);
            break;
         case 'Field':
            $result =  $this->queryResult->fetch_field();
            break;
         case 'Fields':
            $result =  $this->queryResult->fetch_fields();
            break;
         case 'Count':
            return $this->queryResult->num_rows;
         default:
            throw new BadMethodCallException(get_called_class().' - 2 '.__FUNCTION__);
      }
      $this->queryResult->free();
      return $result;
   }
}