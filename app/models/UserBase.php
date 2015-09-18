<?php
/**
 * User: DanVer
 * Date: 21.08.2015
 * Time: 20:21
 */
//класс для работы с пользователями (базовый). добавлены дополнительные возвможные параметры пользователя,
//общие для всех пользователей + отдельная реализация списка телефонов
class UserBase extends User {
    //таблица телефонов пользователей
    const users_phones_table = "users_phones";
    //досупные для всех параметры пользователей
    protected $available_params = array("name","second_name","surname","sex","birthday","photo","jabber","skype");
    //список телефонов пользователей
    public $phones = array();

    public function load($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("load_phones" => true));

        //загружаем сперва все описанное в родителе
        if(!parent::load($args)) return false;
        //если не требуется дополнительно загружать телефоны, выходим
        if(!$args["load_phones"]) return true;

        //иначе вытягиваем из базы список телефонов
        $this->phones = array();
        Database::DB()->reset();
        Database::DB()->select(self::users_phones_table)->where("user_id",$this->id,'=')->exec();
        while($row = Database::DB()->getRow()) $this->phones[] = $row;

        return true;
    }

    //функция сохраняет пользователя (к родительской функции добавлено сохранение телефонов)
    //дополнительные параметры в args
    // save_phones = true - сохранять телефоны
    // delete_phones = true - удалять лишние телефоны из бд
    public function save($args = null) {
        //инициализируем функцию
        $args = Common::initFunc($args,array("save_phones" => true,"delete_phones" => true));

        //сохраняем сперва все описанное в родителе
        if(!parent::save($args)) return false;
        //если не требуется дополнительно сохранять телефоны, выходим
        if(!$args["save_phones"]) return true;

        //иначе вытягиваем из базы список доступных пользователю телефонов
        Database::DB()->reset();
        Database::DB()->select(self::users_phones_table)->where("user_id",$this->id,'=')->exec();
        $exist_phones = array();
        while($row = Database::DB()->getRow()) $exist_phones[] = $row;
        //если требуется удаление лишних телефонов
        if($args["delete_phones"]) {
            //составляем список id-шников
            $deleting_phones = array();
            foreach($exist_phones as $exist_phone) {
                $need_delete = true;
                foreach($this->phones as $phone)
                    if($exist_phone["phone"] == $phone["phone"]) { $need_delete = false; break; }
                if($need_delete) $deleting_phones[] = $exist_phone["id"];
            }
            //и удаляем записи
            if(!empty($deleting_phones)) {
                Database::DB()->reset();
                Database::DB()->delete(self::users_phones_table)->where("id",$deleting_phones,"IN")->exec();
            }
        }
        //теперь разбиваем список телефонов на те, которые нужно обновить и те, которые нужно создать
        $creating_phones = array();
        $updating_phones = array();
        foreach($this->phones as $phone) {
            $if_exist = false;
            foreach($exist_phones as $exist_phone) {
                if($exist_phone["phone"] == $phone["phone"]) {
                    $phone["id"] = $exist_phone["id"];
                    $updating_phones[] = $phone;
                    $if_exist = true;
                }
            }
            if(!$if_exist) $creating_phones[] = $phone;
        }
        //и в зависимости от массивов создаем и обновляем записи в бд
        foreach($updating_phones as $phone) {
            Database::DB()->reset();
            Database::DB()->update(self::users_phones_table,array("type" => $phone["type"]))->where("id",$phone["id"])->exec();
        }
        foreach($creating_phones as $phone) {
            Database::DB()->reset();
            Database::DB()->insert(self::users_phones_table,array("user_id" => $this->id,"phone" => $phone["phone"],"type" => $phone["type"]));
        }

        return true;
    }


    //функция для удобного вывода информации о пользователе. если $return_to_string = false - выводится
    //на экран, иначе возвращается в строке
    public function write($return_to_string = false) {
        $str = parent::write(true);
        if(!empty($this->phones)) $str .= 'phones: <br/>';
        foreach($this->phones as $phone) $str .= '&nbsp-'.$phone["phone"].': '.$phone["type"].'<br/>';
        if($return_to_string) return $str;
        echo $str;
    }

    /*//геттер. добавлен вывод номеров телефонов в виде массива объектов PhoneRow
    public function __get($name) {
        $tmp = parent::__get($name);
        if($tmp) return $tmp;

        if($name == "phones") {
            $phones_rows = array();
            for($i=0;$i < count($this->phones);$i++) $phones_rows[] = new PhoneRow($this->phones[$i]);
            return $phones_rows;
        }
    }

    //сеттер. добавлен вывод номеров телефонов в виде массива объектов PhoneRow
    public function __set($name,$value) {
        //if($name == "phones") $this->phones = $value;

        $tmp = parent::__set($name,$value);
        if($tmp) return $tmp;
    }*/
}

//вспомогательный класс для работы вложенных геттеров и сеттеров у UserBase
class PhoneRow {
    protected $array;

    public function __construct(&$array) {
        $this->array = &$array;
    }

    public function __set($name, $value) {
        if(isset($this->array[$name])) $this->array[$name] = $value;
    }

    public function __get($name) {
        return $this->array[$name];
        //if($name == "phone") return $this->array["phone"];
        //if($name == "type") return $this->array["type"];
    }
}

class UserCustomer extends UserBase {
    public function __construct() {
        //$this->available_params[] =
    }
}
