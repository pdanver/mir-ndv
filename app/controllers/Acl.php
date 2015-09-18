<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 27.05.15
 * Time: 11:57
 */
include_once "app/controllers/controller.php";

class Acl extends Controller
{
    function __construct() {
    }

    function index($args = null) {
        //
    }

    //функция выдает вьюшку админки прав пользователей
    function admin($args = null) {
        return View::load("acl/admin");
    }

    //функция возвращает массив ролей (для post запросов)
    function get_roles($args = null) {
        echo json_encode(AclModel::getRoles());
        exit;
    }

    //функция устанавливает значение enable для массив ролей (для post запросов)
    function set_roles_enable($args = null) {
        //проверяем значение в post
        if(!isset($_POST["roles"])||(!is_array($_POST["roles"]))) {
            echo json_encode(array("error" => array(
                "field" => "roles",
                "msg" => "Не указаны роли")));
            exit;
        }
        //проходимся по массиву ролей
        foreach($_POST["roles"] as $role) {
            //и устанавливаем их значения
            AclModel::setRolesEnable($role["name"],$role["enable"]);
        }
        //возвращаем положительный ответ
        echo json_encode(array("answer" => array("msg" => "ok")));
        exit;
    }

    //функция удаляет массив ролей (для post запросов)
    function delete_roles($args = null) {
        //проверяем значение в post
        if(!isset($_POST["roles"])||(!is_array($_POST["roles"]))) {
            echo json_encode(array("error" => array(
                "field" => "roles",
                "msg" => "Не указаны роли")));
            exit;
        }
        //удаляем массив ролей по параметрам из post
        AclModel::deleteRoles($_POST["roles"]);
        //возвращаем положительный ответ
        echo json_encode(array("answer" => array("msg" => "ok")));
        exit;
    }

    //функция возвращает массив пользователей (для ajax запросов)
  /*  function get_users($args = null) {
        $users = array(); //результат работы ф-ии

        $limit_start = 0;
        //делаем запрос к бд на получении пользователей
        $query = "SELECT id,uname FROM `".Settings::$DB_PREFIX."users` ORDER BY uname LIMIT 0,50";
        $result = Database::query($query);
        if($result == null) return json_encode($users);

        //копируем выборку в массив
        if($limit_start == 0) {
            $users[] = array(
                "id" => -1,
                "name" => "Гость (неавторизованный пользователь)"
            );
        }
        while($row = mysqli_fetch_assoc($result)) {
            $users[] = array(
                "id" => $row["id"],
                "name" => $row["uname"]
            );
        }
        mysqli_free_result($result);

        //возвращаем результат
        return json_encode($users);
    }*/



/*    //функция возвращает массив привилегий (для ajax запросов)
    function get_permissions($args = null) {
        //print_r(get_class_methods());
        return json_encode(AclModel::getPermissions());
    }

    //функция возвращает массив ролей для пользователя (для ajax запросов)
    function get_user_roles($args = null) {
        //проверяем id пользователя
        if(!isset($_POST["user_id"])||(!is_numeric($_POST["user_id"])))
            return json_encode(array()); //если некоректно передан, то возвращаем пустой массив

        //загружаем список всех ролей
        $roles = Acl::getRoles();
        //и список ролей, доступных пользователю
        $user_roles = Acl::getUserRoles($_POST["user_id"]);

        //и проверяем, какие роли из всех ролей включены для данного пользователя, помечяя это во флаге enable
        for($i=0;$i < count($roles);$i++) {
            //проходимся для текущей роли по массиву пользовательсчких ролей и ищем имя роли
            $is_find = false;
            $j = 0;
            while(($j < count($user_roles))&&(!$is_find)) {
                $is_find = (stripos($user_roles[$j]["role_name"],$roles[$i]["name"]) !== false);
                $j++;
            }
            $roles[$i]["enable"] = $is_find;
        }
        //возвращаем результат
        return json_encode($roles);
    }

    //функция получает устанавливает список ролей для пользователя. данные передаются через POST
    function set_user_roles($args = null) {
        //проверяем id пользователя
        if(!isset($_POST["user_id"])||(!is_numeric($_POST["user_id"])))
            return "Пользователь не определен!"; //если некоректно передан, то возвращаем текст ошибки
        $user_id = $_POST["user_id"];
        //считываем список ролей
        $roles = array();
        if(isset($_POST["roles"])&&(is_array($_POST["roles"])))
            $roles = $_POST["roles"];

        //загружаем список текущих ролей у пользователя
        $user_roles = Acl::getUserRoles($user_id);
        //копируем имена ролей в отдельный массив
        $user_roles_names = array();
        foreach($user_roles as $user_role) {
            array_push($user_roles_names,$user_role["role_name"]);
        }

         //и удаляем у пользователя все роли
        Acl::deleteUserRoles($user_id,$user_roles_names);

       //S return print_r($_POST,true);

        //после чего добавляем список выбранных ролей
        Acl::addUserRoles($user_id,$roles);

        //возвращаем результат
        return "Информация сохранена!";
    }

    //функция добавляет пользователя. данные передаются через POST
    function add_user($args = null) {
        //проверяем name пользователя
        if(!isset($_POST["user_name"]))
            return json_encode(array("error" => 1,"msg" => "Имя пользователя не распознано! Попробуйте снова указать его")); //если некоректно передан, то возвращаем текст ошибки
        $user_name = $_POST["user_name"];
        //считываем список ролей
        //проверяем pwd пользователя
        if(!isset($_POST["user_pwd"]))
            return json_encode(array("error" => 2,"msg" => "Пароль не указан! Попробуйте снова указать его")); //если некоректно передан, то возвращаем текст ошибки
        $user_pwd = $_POST["user_pwd"];
        if(!isset($_POST["user_pwd_repeat"]))
            return json_encode(array("error" => 4,"msg" => "Пароль не указан! Попробуйте снова указать его")); //если некоректно передан, то возвращаем текст ошибки
        $user_pwd_repeat = $_POST["user_pwd_repeat"];

        if(strcmp($user_pwd,$user_pwd_repeat) != 0) {
            return json_encode(array("error" => 8,"msg" => "Пароли не собвадают! Попробуйте указать из снова")); //если некоректно передан, то возвращаем текст ошибки
        }

        //загружаем список текущих ролей у пользователя

             //возвращаем результат
        return json_encode(array("success" => Users::addUser($user_name,$user_pwd),"msg" => "Пользователь \"".$user_name."\" создан!"));
    }

    //функция удаляет роль (для ajax запросов)
    function delete_user($args = null) {
        //проверяем имя роли
        if(!isset($_POST["user_name"]))
            return json_encode(array("error" => 1, "msg" => "Пользователь не определен!")); //если некоректно передана, то возвращаем текст ошибки
        if(!isset($_POST["user_id"]))
            return json_encode(array("error" => 2, "msg" => "Пользователь не определен!")); //если некоректно передана, то возвращаем текст ошибки
        Users::deleteUser($_POST["user_name"]);
            return json_encode(array("success" => true, "msg" => "Пользователь \"".$_POST["user_name"]."\" успешно удален"));
    }

    //функция возвращает массив привилегий для роли (для ajax запросов)
    function get_role_permissions($args = null) {
        //проверяем имя роли
        if(!isset($_POST["role_name"]))
            return json_encode(array()); //если некоректно передана, то возвращаем пустой массив

        //загружаем список всех привилегий
        $permissions = Acl::getPermissions();
        //print_r($permissions);
        //и список привилегий, доступных для данной роли
        $role_permissions = Acl::getRolePermissions($_POST["role_name"]);
        echo "Hi";
        //и проверяем, какие привилегиии из всех привилегий включены для данной роли, помечяя это во флаге enable
        for($i=0;$i < count($permissions);$i++) {
            //проходимся для текущей привилегии по массиву ролевых привилегий и ищем ее имя
            $is_find = false;
            $value = 0;
            $j = 0;
            while(($j < count($role_permissions))&&(!$is_find)) {
                $is_find = (stripos($role_permissions[$j]["permission_name"],$permissions[$i]["name"]) !== false);
                if($is_find) $value = $role_permissions[$j]["value"];
                $j++;
            }
            $permissions[$i]["enable"] = $is_find;
            $permissions[$i]["value"] = $value;
        }
        //возвращаем результат
        return json_encode($permissions);
    }

    //функция получает и устанавливает список привилегий для роли. данные передаются через POST
    function set_role_permissions($args = null) {
        //проверяем имя роли
        if(!isset($_POST["role_name"]))
            return "Роль не определена!"; //если некоректно передана, то возвращаем текст ошибки
        $role_name = $_POST["role_name"];
        //считываем список привилегий
        $permissions = array();
        if(isset($_POST["permissions"])&&(is_array($_POST["permissions"])))
            $permissions = $_POST["permissions"];

        //загружаем список текущих привилегий для роли
        $role_permissions = Acl::getRolePermissions($role_name);
        //копируем имена привилегий в отдельный массив
        $role_permissions_names = array();
        foreach($role_permissions as $role_permission) {
            array_push($role_permissions_names,$role_permission["permission_name"]);
        }
        //и удаляем у роли все привилегии
        Acl::deleteRolePermissions($role_name,$role_permissions_names);

        //после чего добавляем список выбранных привилегий
        foreach($permissions as $permission) {
            Acl::setRolePermissions($role_name,$permission["name"],$permission["value"]);
        }

        //возвращаем результат
        return "Информация сохранена!";
    }

    //функция добавляет роль (для ajax запросов)
    function add_role($args = null) {
        //проверяем имя роли
        if(!isset($_POST["role_name"]))
            return json_encode(array("error" => "Роль не определена!")); //если некоректно передана, то возвращаем текст ошибки
        if(Acl::addRoles($_POST["role_name"],false) == 1)
            return json_encode(array("success" => "Роль \"".$_POST["role_name"]."\" успешно добавлена"));
        else
            return json_encode(array("error" => "Не удалось добавить роль \"".$_POST["role_name"]."\"!"));
    }

    //функция удаляет роль (для ajax запросов)
    function delete_role($args = null) {
        //проверяем имя роли
        if(!isset($_POST["role_name"]))
            return json_encode(array("error" => "Роль не определена!")); //если некоректно передана, то возвращаем текст ошибки
        if(Acl::deleteRoles($_POST["role_name"]) == true)
            return json_encode(array("success" => "Роль \"".$_POST["role_name"]."\" успешно удалена"));
        else
            return json_encode(array("error" => "Не удалось удалить роль \"".$_POST["role_name"]."\"!"));
    }

    //функция добавляет привилегию (для ajax запросов)
    function add_permission($args = null) {
        //проверяем имя привилегии
        if(!isset($_POST["permission_name"]))
            return json_encode(array("error" => "Привилегия не определена!")); //если некоректно передана, то возвращаем текст ошибки
        if(Acl::addPermissions($_POST["permission_name"],false) == 1)
            return json_encode(array("success" => "Привилегия \"".$_POST["permission_name"]."\" успешно добавлена"));
        else
            return json_encode(array("error" => "Не удалось добавить привилегию \"".$_POST["permission_name"]."\"!"));
    }

    //функция удаляет привилегию (для ajax запросов)
    function delete_permission($args = null) {
        //проверяем имя привилегии
        if(!isset($_POST["permission_name"]))
            return json_encode(array("error" => "Привилегия не определена!")); //если некоректно передана, то возвращаем текст ошибки
        if(Acl::deletePermissions($_POST["permission_name"]) == true)
            return json_encode(array("success" => "Привилегия \"".$_POST["permission_name"]."\" успешно удалена"));
        else
            return json_encode(array("error" => "Не удалось удалить привилегию \"".$_POST["permission_name"]."\"!"));
    }*/

    function statistic() {
    }
}