<?php
/**
 * User: DanVer
 * Date: 21.08.2015
 * Time: 20:23
 */


//класс для работы со справочными таблицами (таблицами вида: id | name | enable | ... + возможные дополнительные поля  )
//уникальным и первостепенным полем этой таблицы является name
class DirectoryTable extends Common {
    protected $rows = array();  //записи справочника
    protected $table = null;  //таблица справочника

    //конструктор. $table - имя справочной таблицы (ее можно указать при загрузке)
    public function __construct($table = null) {
        $this->table = $table;
    }

    //функция загрузки справочника
    // аргументы в $args
    // $table = null - имя справочной таблицы
    // names = null - имя или массив имен записей таблицы. если null загружается весь справочник
    public function load($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("table" => null,"names" => null));
        //проверяем аргументы
        if(empty($args["table"]) && empty($this->table)) {
            Common::setLastError(Errors::ARGS_NOT_ENOUGH,Errors::ARGS_NOT_ENOUGH_MSG." (table)");
            return false;
        }

        //подготавливаем данные
        if(!empty($args["table"])) $this->table = $args["table"];
        $this->rows = array();

        //подготавливаем запрос
        Database::DB()->reset();
        Database::DB()->select($this->table);

        //добавляем условия в зависимости от $names
        if(is_array($args["names"])) {
            if(empty($args["names"])) return true;
            Database::DB()->where("name",$args["names"],"IN");
        }
        else if(!empty($args["names"])) Database::DB()->where("name",$args["names"],"LIKE");

        //делаем выборку
        Database::DB()->exec();
        //копируем результат в массив
        while($row = Database::DB()->getRow()) $this->rows[] = $row;

        return true;
    }

    //функция сохранения справочника
    // аргументы в $args
    // names = null - имя или массив имен записей справочника. если null, сохраняеются все записи
    // delete = false - удалять ли из таблицы несуществующие в rows записи
    public function save($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("names" => null,"delete" => false));

        if(empty($this->rows) || empty($this->table)) return true;

        //загружаем список уже существующих записей справочника
        Database::DB()->reset();
        Database::DB()->select($this->table,"id,name,enable")->exec();
        $exist_rows = array();
        while($row = Database::DB()->getRow()) $exist_rows[] = $row;

        //сперва удалим лишние записи, если установлен флаг
        if($args["delete"]) {
            $deleting_rows = array();
            //находим сперва массив записей для удаления
            foreach($exist_rows as $exist_row) {
                $need_del = true;
                foreach($this->rows as $row) {
                    if($row["name"] == $exist_row["name"]) {
                        $need_del = false;
                        break;
                    }
                }
                if($need_del) $deleting_rows[] = $exist_row["id"];
            }
            //echoVar($deleting_rows);
            //и если он не пуст, то удаялем записи
            if(!empty($deleting_rows)) {
                Database::DB()->reset();
                Database::DB()->delete($this->table)->where("id",$deleting_rows,"IN")->exec();
            }
        }

        //разделяем теперь исходный массив записей на массивы записей, которые нужно обновить в бд,
        //массив записей и которые требуется создать
        $updating_rows = array();
        $creating_rows = array();
        foreach($this->rows as $row) {
            if(is_array($args["names"])) {
                if(empty($args["names"])) return true;
                if(array_search($row["name"],$args["names"]) === false) continue;
            } else if(!empty($args["names"]) && ($row["name"] != $args["names"])) continue;
            //а теперь определяем, есть ли такая запись в бд и соответственно разобъем параметры по массивам
            $is_exist = false;
            foreach($exist_rows as $exist_row) {
                if($exist_row["name"] == $row["name"]) {
                    $row["id"] = $exist_row["id"];
                    $updating_rows[] = $row;
                    $is_exist = true;
                    break;
                }
            }
            if(!$is_exist) $creating_rows[] = $row;
        }

        //теперь обновляем записи в таблице, по созданнаму массиву
        foreach($updating_rows as $row) {
            $id = $row["id"];
            unset($row["id"]);
            unset($row["name"]);
            Database::DB()->reset();
            Database::DB()->update($this->table,$row)->where("id",$id,"=")->exec();
        }
        //и создаем новые записи
        foreach($creating_rows as $row) {
            unset($row["id"]);
            Database::DB()->reset();
            Database::DB()->insert($this->table,$row);
        }

        return true;
    }

    //геттер просматривает список записей справочника по имени + table
    public function __get($property) {
        if($property == "table") return $this->table;
        if($property == "rows_names") {
            $res = array();
            foreach($this->rows as $row) $res[] = $row["name"];
            return $res;
        }
        foreach($this->rows as $key => $row)
            if($row["name"] == $property)
                return new DirectoryRow($this->rows[$key]);
    }

    //аналогично геттеру
    public function __set($property,$value) {
        //if(!isset($this->rows[$property])) $this->rows[] = array("name" => $property,"enable" => 0);
        if(!is_array($value) && ($value !== null)) return;

        if($value !== null) {
            $value["name"] = $property;
            if(!isset($value["enable"])) $value["enable"] = 0;
        }

        foreach($this->rows as $key => $row)
            if($row["name"] == $property) {
                if($value === null) unset($this->rows[$key]);
                else {
                    if(!empty($this->rows[$key]["id"])) $value["id"] = $this->rows[$key]["id"];
                    $this->rows[$key] = $value;
                }
                return;
            }
        if(!empty($value)) $this->rows[] = $value;
    }

    public function write($return_to_string = false) {
        $str = '<p><span style="font-weight: bold; color: blue;">Directory Table Info:</span><br/>';
        $str .= 'table: '.$this->table.'<br/>Rows: <br/>';
        foreach($this->rows as $row) {
            $str .= $row["name"] . ': <br/>';
            foreach ($row as $key => $val)
                if($key != "name") $str .= '&nbsp -' . $key . ': ' . $val . '<br/>';
        }
        $str .= '<span style="font-weight: bold; color: blue;">End</span></p>';
        if($return_to_string) return $str;
        echo $str;
    }
}

//вспомогательный класс для работы вложенных геттеров и сеттеров у DirectoryTable
class DirectoryRow {
    protected $array;

    public function __construct(&$array) {
        $this->array = &$array;
    }

    public function __set($name, $value) {
        if(($name == "id") || ($name == "name")) return;
        if(isset($this->array[$name])) $this->array[$name] = $value;
    }

    public function __get($name) {
        return $this->array[$name];
    }
}
