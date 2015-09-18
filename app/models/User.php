<?php
/**
 * User: DanVer
 * Date: 20.08.2015
 * Time: 15:13
 */

//базовый класс для работы с пользователем
class User extends Common {
    //таблицы с пользовательскими данными
    const users_table = "users";
    const users_params_table = "users_params";
    const users_types_table = "users_types";

    static public $user_photo_dir = "img/photo/";

    //константы статусов пользователя
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;

    protected $id = -1;   //идентификатор
    protected $login = "";   //логин
    protected $pwd = "";   //пароль
    protected $status = -1;  //статус пользователя
    protected $type = null;   //тип пользователя
    protected $reg_date = null;   //дата регистрации
    protected $params = array();  //параметры пользователя
    //массив доступных параметров
    protected $available_params = array();
    //массив доступных типов пользователя (загружается из бд)
    //protected $usersTypes = null;

    //конструктор
    public function __construct() {
        //$this->usersTypes = new DirectoryTable(self::users_types_table);
    }

    //загрузка информации о пользователе из бд
    //параметры в options:
    // id = null - идентификатор (предпочтение отдается ему)
    // login = null - логин
    // load_pwd = false - загружать ли пароль
    // load_params = true - загружать дополнительные параметры по пользователю
    // *** (not work) params = null - массив необходимых параметров (если null - загружаются все)... заменено на available_params
    public function load($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("id" => null,"login" => null,"load_pwd" => false,"load_params" => true,"params" => null));
        //формируем запрос
        Database::DB()->reset();
        Database::DB()->select(self::users_table);
        //в зависимости от параметров формируем условия
        if(!empty($args["id"])) Database::DB()->where("id",$args["id"],"=");
        else if (!empty($args["login"])) Database::DB()->where("login",$args["login"],"LIKE");
        else {
            Common::setLastError(Errors::ARGS_NOT_ENOUGH,Errors::ARGS_NOT_ENOUGH_MSG);
            return false;
        }
        //загружаем пользователя
        Database::DB()->exec();
        $user = Database::DB()->getRow();
        if(empty($user)) {
            Common::setLastError(Errors::USER_NOT_FOUND,Errors::USER_NOT_FOUND_MSG);
            return false;
        }
        //считываем данные о нем
        $this->id = $user["id"];
        $this->login = $user["login"];
        if($args["load_pwd"]) $this->pwd = $user["pwd"];
        $this->status = $user["status"];
        $this->type = $user["type"];
        $this->reg_date = $user["regDate"];

        //загружаем дополнительные параметры пользователя
        if(!empty($args["load_params"])) {
            $params = ParamsTable::load_s(array("table" => self::users_params_table, "paramID" => $this->id, "params" => $this->available_params));
            if (Common::isLastError()) return false;
            foreach ($params as $param) {
                if(array_search($param["paramName"],$this->available_params) !== false)
                    $this->params[$param["paramName"]] = $param["paramValue"];
            }
        }
        return true;  //
    }

    //функция сохраняет в бд информацию о пользователе
    public function save($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("save_params" => true));
        //проверяем информацию о пользователе
        if(empty($this->id) || ($this->id < 0)) {
            Common::setLastError(Errors::USER_NOT_FOUND,Errors::USER_NOT_FOUND_MSG);
            return false;
        }
        //и формируем массив для записи в бд
        $save_data = array("status" => $this->status,"type" => $this->type);
        //обновляем информацию в бд
        Database::DB()->reset();
        Database::DB()->update(self::users_table,$save_data)->where('id',$this->id)->exec();
        //сохраняем дополнительные параметры пользователя
        if($args["save_params"] && !empty($this->params)) {
            ParamsTable::save_s(array("table" => self::users_params_table,"paramID" => $this->id,"params" => $this->params));
            if(Common::isLastError()) return false;
        }

        return true;
    }

    //!!!!!! (не работает)
    public function add($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("save_params" => true));
        //проверяем информацию о пользователе

        //и формируем массив для записи в бд
        $save_data = array("login" => "NewDanVer@gmail.com","status" => $this->status,"type" => $this->type,"pwd" => "$2$$2y$12YDYEDJEeJ0eb0mbnmYnfYVfgVVgaVQapQvpcvscWseWyejZTGEuMeLaxO36c4w9CaQsyaYaIs2");
        //обновляем информацию в бд
        Database::DB()->reset();
        Database::DB()->insert(self::users_table."_copy",$save_data);
        $this->id = Database::DB()->lastInsertId();
        //сохраняем дополнительные параметры пользователя
        if($args["save_params"] && !empty($this->params)) {
            ParamsTable::save_s(array("table" => self::users_params_table,"paramID" => $this->id,"params" => $this->params));
            if(Common::isLastError()) return false;
        }

        return true;
    }

    //функция авторизует (заносит в сессию) пользователя
    public function auth() {
        Common::initFunc();
        //проверяем информацию о пользователе
        if(empty($this->id) || ($this->id < 0)) {
            Common::setLastError(Errors::USER_NOT_FOUND,Errors::USER_NOT_FOUND_MSG);
            return false;
        }
        //заносим данные в сессию
        $_SESSION["uid"] = $this->id; //для совместимости
        $_SESSION["user"]["id"] = $this->id;
        $_SESSION["user"]["login"] = $this->login;
        $_SESSION["user"]["type"] = $this->type;
        $_SESSION["user"]["status"] = $this->status;
        return true;
    }

    //функция для удобного вывода информации о пользователе. если $return_to_string = false - выводится
    //на экран, иначе возвращается в строке
    public function write($return_to_string = false) {
        $str = '<p><span style="font-weight: bold; color: blue;">User Info:</span><br/>';
        $str .= 'id: '.$this->id.'<br/>login: '.$this->login.'<br/>';
        if(!empty($this->pwd)) $str .= 'pwd: '.$this->pwd.'<br/>';
        $str .= 'type: '.$this->type.'<br/>status: '.$this->status.'<br/>reg_date: '.$this->reg_date.'<br/>';
        foreach($this->params as $key => $val) $str .= $key.': '.$val.'<br/>';
        if($return_to_string) return $str;
        echo $str;
    }

    //геттер (без комментариев)
    public function __get($property) {
        switch($property) {
            case 'id': return $this->id;
            case 'login': return $this->login;
            case 'type': return $this->type;
            case 'status': return $this->status;
            case 'reg_date': return $this->reg_date;
        }
        //если нет нужного свойства, то просматриваем массив доступных параметров пользователя
        $key = array_search($property,$this->available_params);
        //если находим такой доступный параметр и он существует, то возвращаем его
        if(($key !== false)&&(isset($this->params[$property])))
            return $this->params[$property];
    }

    //сеттер (без комментариев)
    public function __set($property,$value) {
        switch($property) {
            case 'type': if(is_numeric($value) && ($value > 0)) $this->type = $value; break;
            case 'status': if(is_numeric($value) && ($value >= 0)) $this->status = $value; break;
        }
        //если нет нужного свойства, то просматриваем массив доступных параметров пользователя
        $key = array_search($property,$this->available_params);
        //если находим такой доступный параметр, то устанавливаем его
        if($key !== false) $this->params[$property] = $value;
    }
}


