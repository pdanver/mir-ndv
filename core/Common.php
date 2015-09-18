<?php
/**
 * User: DanVer
 * Date: 21.08.2015
 * Time: 20:11
 */

//общий наследуемый класс c набором общих механизмов (например обработки ошибок)
class Common {
    protected static $last_error = null; //номер последней ошибка
    protected static $last_error_msg = null; //текст последней ошибки

    //функции для работы с ошибками
    static public function getLastError() { return self::$last_error; }
    static public function getLastErrorMsg() { return self::$last_error_msg; }
    static public function setLastError($error,$errorMsg) { self::$last_error = $error; self::$last_error_msg = $errorMsg; }
    static public function isLastError() { return !empty(self::$last_error); }
    static public function echoLastError($return_to_string = false) {
        $str = "<p style='color: #0062A0;'>No errors</p>";
        if(self::isLastError()) $str = "<p style='color: red;'>Error #".self::getLastError().": ".self::getLastErrorMsg()."</p>";
        if($return_to_string) return $str;
        echo $str;
    }

    //функция общей инициализации для функций
    static public function initFunc($args = null,array $default_args = null) {
        self::setLastError(null,null);  //обнуляем полследнюю ошибку
        //и устанавливаем аргументы по умолчанию
        if(empty($default_args)) return $args;
        if(!isset($args)) $args = array();
        foreach($default_args as $key => $param)
            if(!isset($args[$key])) $args[$key] = $param;
        return $args;
    }
}