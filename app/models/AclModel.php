<?php
/**
 * User: DanVer
 * Date: 19.07.2015
 * Time: 9:07
 */
//Класс для работы с правами доступа, реализованного на основе механизма ACL
class AclModel {
    static $permission_table = "controllers";
    static $roles_table = "acl_roles";   //таблица ролей

    //функция получает список ролей. если указан $names получается список ролей перечисленных в $names. $names может
    //быть как массивом ролей, так и строкой с одной ролью. если указан $enable, ищутся роли с флагом $enable
    public static function getRoles($names = null,$enable = null) {
        //подготавливаем запрос на выборку
        Database::DB()->reset();
        Database::DB()->select(self::$roles_table,"name,enable");
        //в зависимости от $names добавляем условие
        if(isset($names)) {
            if(empty($names)) return array();
            if(is_array($names)) Database::DB()->where("name",$names,"IN");
            else Database::DB()->where("name",$names,"LIKE");
        }
        //в зависимости от $enable добавляем условие
        if(isset($enable)) {
            Database::DB()->sqlAND();
            Database::DB()->where("enable",$enable,'=');
        }
        //выполняем запрос
        Database::DB()->exec();
        //echo Database::DB()->lastQuery().'<br/>';

        //копируем результат в массив
        $roles = array();
        while($row = Database::getRow()) {
            array_push($roles,$row);
        }
        //и возвращаем результат
        return $roles;
    }

    //функция устанавливает значение $enable для списка ролей из $names. $names может быть как массивом ролей,
    //так и строкой с одной ролью (если $names = null - действует для всех ролей).
    public static function setRolesEnable($names = null,$enable) {
        //подготавливаем запрос update
        Database::DB()->reset();
        Database::DB()->update(self::$roles_table,array("enable" => $enable));

        //в зависимости от $names добавляем условие
        if(isset($names)) {
            if(empty($names)) return;
            if(is_array($names)) Database::DB()->where("name",$names,"IN");
            else Database::DB()->where("name",$names,"LIKE");
        }

        //выполняем запрос
        Database::DB()->exec();
        //echo Database::DB()->lastQuery().'<br/>';
    }

    //функция удаляет список ролей $names. $names может быть как массивом ролей, так и строкой с одной ролью
    public static function deleteRoles($names) {
        //подготавливаем запрос update
        Database::DB()->reset();
        Database::DB()->delete(self::$roles_table);

        //в зависимости от $names добавляем условие
        if(isset($names)) {
            if(empty($names)) return;
            if(is_array($names)) Database::DB()->where("name",$names,"IN");
            else Database::DB()->where("name",$names,"LIKE");
        }

        //выполняем запрос
        Database::DB()->exec();
        //echo Database::DB()->lastQuery().'<br/>';
    }
}