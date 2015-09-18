<?php
/**
 * Created by IntelliJ IDEA.
 * User: takeru
 * Date: 18.08.15
 * Time: 15:17
 *
 * ВАЖНО: в наследуемых классах должно быть реализовано 2 статические переменные $tableFields и $tableName
 *
 *  - $tableName - название таблицы в базе данных
 *  - $tableFields - массив всех полей из таблицы
 */

abstract class Model
{
   /**
    * Шаблонный метод для добавления данных модели в базу данных.
    *
    * @param $data см. check($data)
    *
    * @return int mixed возвращает auto_inkrement идентификатор вставленной строки или строк.
    * Если вставлено много строк, то возвращает только первый идентификатор
    */

   static function insert($data)
   {
      self::check($data);

      if(is_object($data))
         $data = $data->getModifiedFields();
      else
         if(is_array($data))
            if(is_object(reset($data)))
            {
               foreach($data as &$val)
                  if(is_object($val))
                     $val = $val->getModifiedFields();
               unset($val);
            }

      $func = function($var) { return ($var?$var:null); };

      if(is_array(reset($data)))
      {
         foreach($data as &$arr)
            $arr = array_filter($arr, $func);
         unset($arr);
      }
      else
         $data = array_filter($data, $func);

      $class = get_called_class();
      return Database::DB()->insert($class::$tableName, $data);
   }

   /**
    * Обновляет запись в базе данных.
    *
    * Использование:
    *    Model::update($obj); обновит запись данного объекта по его id
    *    Model::update($obj, 'field'); обновит запись по полю 'field'(может затронуть несколько записей)
    *    Model::update($obj, 'field', array('value1', 'value2')); обновит запись по полю 'field' со значениями
    * 'value1', 'value2' (Затрагивает несколько записей)
    *    Model::update(array('field1'=>'val1'), 'id', array(1)); обновит запись с id = 1
    *    Model::update(array($obj, $obj2, $obj3)); обновит записи всех 3 объектов по их идентификаторам,
    * поля для обновления будут взяты только у первого
    *
    * TODO: Со временем возможно появление универсального запроса на обновление полей
    *
    * @param object | array | array(object) $data аргументы обновления
    * @param string $whereField параметр поиска в базе данных, по умолчанию 'id'
    * @param array  $whereValue аргументы поиска в базе данных
    */

   static function update($data, $whereField = 'id', array $whereValue = array())
   {
      self::check($data);

      if(is_object($data))
      {
         $whereValue[] = $data->id;
         $data = $data->getModifiedFields();
      }
      else
         if(is_array($data) && is_object(reset($data)))
         {
            foreach($data as $val)
               $whereValue[] = $val->id;
            $data = array_shift($data)->getModifiedFields();
         }

      $class = get_called_class();
      Database::DB()->update($class::$tableName, $data)->where($whereField, $whereValue, 'IN')->exec();
   }

   /**
    * Удаляет запись из базы данных по идентификатору 'id'
    *
    * @param array | object $data объект записи или массив идентификаторов
    */

   static function delete($data)
   {
      self::check($data);

      $class = get_called_class();
      if(is_object($data))
         Database::DB()->delete($class::$tableName)->where('id', $data->id)->exec();
      else
         if(is_array($data))
            Database::DB()->delete($class::$tableName)->where('id', $data, 'IN')->exec();
   }

   /**
    * Очищает таблицу базы данных и обнуляет счетчики auto_increment
    */

   final static function truncate()
   {
      $class = get_called_class();
      Database::DB()->truncate($class::$tableName)->exec();
   }

   /**
    * Проверяет правильность полученых аргументов. Принимаются массивы, массивы массивов, объекты и массивы объектов
    *
    * @param array | object | array(array) | array(object) $data при получении объекта или массива объектов приводит
    *                                                       все объекты к виду массивов для работы методов базы данных
    *
    * @throws InvalidArgumentException если аргументы не удовлетворяют условию
    */

   private static function check(&$data)
   {
      if(!is_array($data) && !is_object($data))
         throw new InvalidArgumentException(get_called_class().' - '.__FUNCTION__);
   }

   /**
    * Маска для метода findByField.
    *
    * @param $name
    * @param $args
    *
    * @return mixed
    * @throws BadMethodCallException
    */

   final static function __callStatic($name, $args)
   {
      $class = get_called_class();
      if(substr($name, 0, 6) == 'findBy')
      {
         array_unshift($args, substr($name, 6));
         return call_user_func_array(array($class, 'findByField'), $args);
      }
      else
         throw new BadMethodCallException($class.' - '.__FUNCTION__);
   }

   /**
    * Возвращает результат запроса в виде объекта или массива объектов.
    *
    * @param string $field строка - название колонки, по которой происходит поиск
    * @param array  $args - ассоциативный массив аргументов:
    *                     'fields' - строка или массив строк - поля, которые вернутся из поиска
    *                     'where' - строка или массив - параметры поиска
    *                     'group' - строка - название колонки - параметр группировки
    *                     'order' - строка - параметр сортировки результирующей выборки
    *                     'orderDown' - тип данных не имеет значения - если указан, то упорядочевание будет по убыванию
    *                     'limit' - число - параметр ограничения выборки
    *                     'offset' - число - параметр смещения выборки
    *
    * @return null | object | array
    * @throws InvalidArgumentException
    */

   static function findByField($field, array $args = null)
   {
      $class = get_called_class();
      if(!is_string($field) || !in_array(strtolower($field), $class::$tableFields))
         throw new InvalidArgumentException($class.' - '.__FUNCTION__);

      Database::DB()->select($class::$tableName, empty($args['fields'])?'*':$args['fields']);

      if(!empty($args['where']))
         Database::DB()->where($field, $args['where']);
      if(!empty($args['group']) && is_string($args['group']))
         Database::DB()->group($args['group']);
      if(!empty($args['order']) && is_string($args['order']))
         Database::DB()->order($args['order'], empty($args['orderDown'])?false:true);
      if(!empty($args['limit']))
         Database::DB()->limit($args['limit'], empty($args['offset'])?$args['offset']:0);

      Database::DB()->exec();

      if(Database::DB()->getCount() == 1)
         return new $class(Database::DB()->getRow());
      else
         if(Database::DB()->getCount() > 1)
         {
            $objects = Database::DB()->get();
            foreach($objects as &$val)
               $val = new $class($val);

            unset($val);
            return $objects;
         }
      return null;
   }

// -= Dynamic =-

   public $id;
   protected $fields;
   private $modifiedFields, $isNew = false;

   /**
    * Инициализирует объект
    */

   abstract function init();

   /**
    * Фильтр ввода переменных. Может быть использован для контроля типа полей класса или их допустимых значений
    *
    * @param string $name
    * @param mixed  $value
    *
    * @return mixed
    */

   abstract function inFilter($name, $value);

   /**
    * Фильтр форматирования данных для запросов в базу данных
    *
    * @param string $name
    * @param mixed  $value
    *
    * @return mixed
    */

   abstract function outFilter($name, $value);

   final function __construct(array $args = null)
   {
      $this->fields = array();
      if($args)
         foreach($args as $key => $value)
            $this->{$key} = $value;
      else
         $this->isNew = true;
      $this->modifiedFields = array();
      $this->init();
   }

   final function __set($name, $value)
   {
      $class = get_called_class();
      if(in_array(strtolower($name), $class::$tableFields))
      {
         $this->modifiedFields[$name] = $this->outFilter($name, $value);
         $this->fields[$name] = $this->inFilter($name, $value);
      }
   }

   final function __get($name)
   {
      if(!array_key_exists($name, $this->fields))
         return null;
      return $this->fields[$name];
   }

   final function __toString()
   {
      return get_class($this).'.obj';
   }

   /**
    * Определяет является ли объект новым или уже существует в базе
    *
    * @return bool
    */

   final function isNew()
   {
      return $this->isNew;
   }

   /**
    * Определяет внесены ли изменения в поля объекта
    *
    * @return bool
    */

   final function isModified()
   {
      return $this->modifiedFields?true:false;
   }

   /**
    * Возвращает массив измененных полей объекта
    *
    * @return array
    */

   final function getModifiedFields()
   {
      return $this->modifiedFields;
   }

   /**
    * Возвращает массив всех полей объекта за исключением id,
    * изменение значения этого поля не будет отображаться в базе данных
    *
    * @return array
    */

   final function getFieldsArray()
   {
      return $this->fields;
   }

   /**
    * Сохраняет запись в базе данных
    */

   final function save()
   {
      if($this->isNew)
      {
         $this->id = self::insert($this);
         $this->modifiedFields = array();
         $this->isNew = false;
      }
      else
         if($this->isModified())
         {
            self::update($this);
            $this->modifiedFields = array();
         }
   }

   /**
    * Удаляет запись из базы данных
    */

   final function remove()
   {
      self::delete($this);
   }
}