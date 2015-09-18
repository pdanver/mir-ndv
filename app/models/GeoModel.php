<?php
/**
 * User: DanVer
 * Date: 26.08.2015
 * Time: 11:13
 */

//Модуль с Geo моделями

//общий базовый класс для Geo моделей
class GeoModel extends Model {
    //переопределения абстрактных функций
    function init()  {}
    function filter($name) { return $name; }
    function inFilter($name, $value) { return $value; }
    function outFilter($name, $value) { return $value; }

    //переопределение функции для совместимости с базовой моделью Model. заведена для того, чтобы обеспечить работу
    //модуля, пока базовый класс не примет окончательную форму. После чего надо будет избавиться
    static function findByField($field, array $args = null) {
        $class = get_called_class();
        if(!is_string($field) || !in_array(strtolower($field), $class::$tableFields))
            throw new InvalidArgumentException($class.' - '.__FUNCTION__.': Invalid argument "field"');

        Database::DB()->select($class::$tableName, empty($args['fields'])?'*':$args['fields']);

        if(!empty($args['value'])) {
            Database::DB()->where($field, $args['value']);
            if(!empty($args['where'])) Database::DB()->sqlAND();
        }
        if(!empty($args['where'])) {
            if(is_array($args["where"])) Database::DB()->where(implode(' ',$args["where"]));
            else {
                if (!is_string($args["where"]))
                    throw new InvalidArgumentException(get_called_class().' - '.__FUNCTION__.': Invalid argument "where"');
                Database::DB()->where($args["where"]);
            }
        }

        if(!empty($args['group']) && is_string($args['group']))
            Database::DB()->group($args['group']);
        if(!empty($args['order']) && (is_string($args['order']) || is_array($args['order'])))
            Database::DB()->order($args['order'], empty($args['orderDown'])?false:true);
        if(!empty($args['limit']))
            Database::DB()->limit($args['limit'], empty($args['offset'])?$args['offset']:0);

        Database::DB()->exec();

        $res = array();
        $rows = Database::DB()->get();
        for($i = 0;$i < count($rows);$i++)
            $res[] = new $class($rows[$i]);

        return $res;
    }
}

//класс для работы с таблицей geo_country
class GeoCountry extends GeoModel {
    protected static $tableName = 'geo_country';
    protected static $tableFields = array('id','countrycode','name','phonecode','enable');
}

//класс для работы с таблицей geo_region
class GeoRegion extends GeoModel {
    protected static $tableName = 'geo_region';
    protected static $tableFields = array('aoid','code','regioncode','autocode','formalname','shortname','streettable');
}

//класс для работы с таблицей geo_city
class GeoCity extends GeoModel {
    protected static $tableName = 'geo_city';
    protected static $tableFields = array('aoid','code','aolevel','regioncode','areacode','citycode','postalcode','formalname','shortname');
}

//класс для работы с таблицами вида geo_street.[postfix]. так как таких таблиц несколько, заведена функция для
//указания имени нужной таблицы
class GeoStreet extends GeoModel {
    protected static $tableName = 'geo_street';
    protected static $tableFields = array('aoid','code','aolevel','regioncode','areacode','citycode','ctarcode','placecode','streetcode','extrcode','sextcode','postalcode','formalname','shortname');

    //функция для установки имени таблицы
    static public function setTableName($name) {
        if(!is_string($name)) throw new InvalidArgumentException(get_called_class().' - '.__FUNCTION__.': Invalid argument name');
        self::$tableName = 'geo_street'.$name;
    }

    static public function getTableName() {
        return self::$tableName;
    }

    //переопределяем функция для добавления проверки существования требуемой таблицы
    static function findByField($field, array $args = null) {
        if(Database::DB()->query('SHOW TABLES LIKE \''.Settings::$DB_PREFIX."_".self::$tableName.'\'')->exec()->getCount() < 1) {
            throw new InvalidArgumentException(get_called_class().' - '.__FUNCTION__.': Invalid table "'.self::$tableName.'"');
        }
        return parent::findByField($field, $args);
    }
}

//класс для работы с таблицей geo_place
class GeoPlace extends GeoModel {
    protected static $tableName = 'geo_place';
    protected static $tableFields = array('aoid','code','aolevel','regioncode','areacode','citycode','ctarcode','placecode','postalcode','formalname','shortname');
}