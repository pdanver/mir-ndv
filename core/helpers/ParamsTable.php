<?php
/**
 * User: DanVer
 * Date: 21.08.2015
 * Time: 20:09
 */

//класс для работы с параметрическими таблицами (таблицами вида: id | paramID | paramName | paramValue)
class ParamsTable extends Common {
    protected $paramID = -1;  //идентификатор объекта
    protected $table = null;  //имя таблицы, с которой загружались параметры
    public $params = null;  //параметры объекта

    //конструктор. $table - имя параметрической таблицы, $paramID - идентификатор объекта (их можно указать при загрузке)
    public function __construct($table = null,$paramID = -1) {
        $this->table = $table;
        $this->paramID = $paramID;
    }

    //функция загрузки параметров для объекта
    // аргументы в $args
    // $table = null - имя параметрической таблицы
    // paramID = -1 - идентификатор объекта
    // params = null - имя или массив имен параметров. если null загружаются все параметры для объекта
    public function load($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("table" => null,"paramID" => -1,"params" => null));
        //проверяем аргументы
        if(empty($args["table"]) && empty($this->table)) {
            Common::setLastError(Errors::ARGS_NOT_ENOUGH,Errors::ARGS_NOT_ENOUGH_MSG." (table)");
            return false;
        }
        if((!is_numeric($args["paramID"]) || ($args["paramID"] <= 0)) && ($this->paramID <= 0)) {
            Common::setLastError(Errors::ARGS_INCORRECT,Errors::ARGS_INCORRECT_MSG." paramID");
            return false;
        }

        //подготавливаем данные
        if(!empty($args["table"])) $this->table = $args["table"];
        if(is_numeric($args["paramID"]) && ($args["paramID"] > 0)) $this->paramID = $args["paramID"];
        //if(isset($this->params)) unset($this->params);
        $this->params = array();

        //подготавливаем запрос
        Database::DB()->reset();
        Database::DB()->select($this->table);

        //добавляем условия в зависимости от $params
        if(is_array($args["params"])) {
            if(empty($args["params"])) return true;
            Database::DB()->where("paramName",$args["params"],"IN","AND");
        }
        else if(!empty($args["params"])) Database::DB()->where("paramName",$args["params"],"LIKE","AND");

        //делаем выборку
        Database::DB()->where("paramID",$this->paramID,"=")->exec();

        //копируем результат в массив
        while($row = Database::DB()->getRow()) {
            $this->params[$row["paramName"]] = $row["paramValue"];
        }

        //и возвращаем его
        return true;
    }

    //функция сохранения параметров объекта
    // аргументы в $args
    // params = null - имя или массив имен параметров для записи. если null, то сохраняется все
    public function save($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("params" => null));

        if(empty($this->params) || ($this->paramID <= 0) || empty($this->table)) return true;

        //загружаем список уже существующих параметров
        Database::DB()->reset();
        Database::DB()->select($this->table,"paramName,paramValue")->
            where("paramName",array_keys($this->params),"IN","AND")->
            where("paramID",$this->paramID,"=")->exec();

        $exist_params = array();
        while($row = Database::DB()->getRow()) $exist_params[] = $row;

        //разделяем теперь исходный массив параметров на массивы параметров, которые нужно обновить в бд и
        //массив параметров, которые требуется создать
        $updating_params = array();
        $creating_params = array();
        foreach($this->params as $key => $val) {
            //проверяем сперва параметры, требуется ли учитывать текущюю запись
            if (is_array($args["params"])) {
                //if(empty($args["params"])) return true;
                if (array_search($key, $args["params"]) === false) continue;
            } else if (!empty($args["params"]) && ($args["params"] != $key)) continue;

            //а теперь определяем, есть ли такая запись в бд и соответственно разобъем параметры по массивам
            $is_exist = false;
            foreach($exist_params as $exist_val) {
                if($exist_val["paramName"] == $key) {
                    //так же отбрасываем те параметры, которые не изменили своего значения
                    if($exist_val["paramValue"] != $val) $updating_params[$key] = $val;
                    $is_exist = true;
                    break;
                }
            }
            if(!$is_exist) $creating_params[$key] = $val;
        }

        //теперь обновляем записи в таблице, по созданнаму массиву
        foreach($updating_params as $key => $val) {
            Database::DB()->reset();
            Database::DB()->update($this->table,array("paramValue" => $val))->
            where("paramID",$this->paramID,"=","AND")->
            where("paramName",$key,"LIKE")->exec();
        }
        //и создаем новые записи
        foreach($creating_params as $key => $val) {
            Database::DB()->reset();
            Database::DB()->insert($this->table,array("paramID" => $this->paramID,"paramName" => $key,"paramValue" => $val));
        }

        return true;
    }

    //геттер просматривает массив параметров по ключю и выдает занчение из него, если есть
    public function __get($property) {
        if($property == "paramID") return $this->paramID;
        if($property == "table") return $this->table;
        if(isset($this->params[$property])) return $this->params[$property];
    }

    //аналогично геттеру
    public function __set($property,$value) {
        if(!isset($this->params)) $this->params = array();
        $this->params[$property] = $value;
    }

    public function write($return_to_string = false) {
        $str = '<p><span style="font-weight: bold; color: blue;">Params Table Info:</span><br/>';
        $str .= 'table: '.$this->table.'<br/>paramID: '.$this->paramID.'<br/>';
        foreach($this->params as $key => $val) $str .= $key.': '.$val.'<br/>';
        $str .= '<span style="font-weight: bold; color: blue;">End</span></p>';
        if($return_to_string) return $str;
        echo $str;
    }

    //функция загрузки параметров для объекта
    // аргументы в $args
    // $table - имя параметрической таблицы
    // paramID - идентификатор объекта
    // params = null - имя или массив имен параметров. если null загружаются все параметры для объекта
    static function load_s($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("params" => null));
        //проверяем аргументы
        if(!isset($args["table"]) || !isset($args["paramID"])) {
            Common::setLastError(Errors::ARGS_NOT_ENOUGH,Errors::ARGS_NOT_ENOUGH_MSG);
            return null;
        }
        if(!is_numeric($args["paramID"]) || ($args["paramID"] <= 0) || empty($args["table"])) {
            Common::setLastError(Errors::ARGS_INCORRECT,Errors::ARGS_INCORRECT_MSG);
            return null;
        }

        //подготавливаем запрос
        Database::DB()->reset();
        Database::DB()->select($args["table"]);

        //добавляем условия в зависимости от $params
        if(empty($args["params"])) return array();
        if(is_array($args["params"])) Database::DB()->where("paramName",$args["params"],"IN","AND");
        else Database::DB()->where("paramName",$args["params"],"LIKE","AND");

        //делаем выборку
        Database::DB()->where("paramID",$args["paramID"],"=")->exec();
        //echo "<p>".Database::DB()->lastQuery()."</p>";
        //копируем результат в массив
        $res = array();
        while($row = Database::DB()->getRow()) $res[] = $row;
        //и возвращаем его
        return $res;
    }

    //функция сохранения параметров объекта
    // аргументы в $args
    // $table - имя параметрической таблицы
    // paramID - идентификатор объекта
    // params - ассоциативный массив параметров вида (paramName => paramValue)
    static function save_s($args = null) {
        //инициализируем функцию
        Common::initFunc();

        //проверяем аргументы
        if(!isset($args["table"]) || !isset($args["paramID"]) || !isset($args["params"])) {
            Common::setLastError(Errors::ARGS_NOT_ENOUGH,Errors::ARGS_NOT_ENOUGH_MSG);
            return false;
        }
        if(!is_numeric($args["paramID"]) || ($args["paramID"] <= 0)) {
            Common::setLastError(Errors::ARGS_INCORRECT,Errors::ARGS_INCORRECT_MSG." (paramID)");
            return false;
        }
        if(empty($args["table"])) {
            Common::setLastError(Errors::ARGS_INCORRECT,Errors::ARGS_INCORRECT_MSG." (table)");
            return false;
        }

        if(!isset($args["params"]) || !is_array($args["params"] ) || !is_assoc($args["params"])) {
            Common::setLastError(Errors::ARGS_INCORRECT,Errors::ARGS_INCORRECT_MSG." (params)");
            return false;
        }

        //загружаем список уже существующих параметров
        $exist_params = self::load_s(array("table" => $args["table"],"paramID" => $args["paramID"],"params" => array_keys($args["params"])));
        if(Common::isLastError()) return null;

        //разделяем теперь исходный массив параметров на массивы параметров, которые нужно обновить в бд и
        //массив параметров, которые требуется создать
        $updating_params = array();
        $creating_params = array();
        foreach($args["params"] as $key => $val) {
            $is_exist = false;
            foreach($exist_params as $exist_val) {
                if($exist_val["paramName"] == $key) {
                    //так же отбрасываем те параметры, которые не изменили своего значения
                    if($exist_val["paramValue"] != $val) $updating_params[$key] = $val;
                    $is_exist = true;
                    break;
                }
            }
            if(!$is_exist) $creating_params[$key] = $val;
        }

        //теперь обновляем записи в таблице, по созданнаму массиву
        foreach($updating_params as $key => $val) {
            Database::DB()->reset();
            Database::DB()->update($args["table"],array("paramValue" => $val))->
            where("paramID",$args["paramID"],"=","AND")->
            where("paramName",$key,"LIKE")->exec();
        }
        //и создаем новые записи
        foreach($creating_params as $key => $val) {
            Database::DB()->reset();
            Database::DB()->insert($args["table"],array("paramID" => $args["paramID"],"paramName" => $key,"paramValue" => $val));
        }

        return true;
    }
}