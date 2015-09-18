<?php

/**
 * User: DanVer
 * Date: 01.08.15
 * Time: 17:27
 */

include_once "core/helpers/Crypt.php";
include_once "core/helpers/utils.php";

 //класс для работы с пользователями
class Users
{
	//константы ошибок
	const ERROR_NOT = 0;  				//ошибок нет
	const ERROR_LOGIN_EMPTY = 1;  		//логин не указан 
	const ERROR_LOGIN_TYPE = 2;  		//некорректный тип логина 
	const ERROR_LOGIN_VALIDATE = 4;		//логин не прошел валидацию
	const ERROR_USER_NOT_FOUND = 8;  	//пользователь не найден
	const ERROR_USER_REGISTERED = 16;	//пользователь уже зарегистрирован
	const ERROR_USER_ADD = 32;  		//не удалось добавить пользователя
	const ERROR_USER_UPDATE = 64;  		//ошибка изменения данных пользователя
	const ERROR_USER_DELETE = 128;		//ошибка удаления пользователя
	const ERROR_USER_STATUS = 256; 		//Некорректный статус пользователя
	
	const ERROR_PWD_EMPTY = 512; 		//пароль не указан
	const ERROR_PWD_DIFFERENT = 1024; 	//пароли не совпадают (при валидации, когда передаются две строки с одним паролем)
	const ERROR_PWD_NOT_MATCH = 2048; 	//пароли не совпадают (при верификации)
	const ERROR_PWD_NOT_CORRECT = 4096; //пароль не корректен (т.е. содержит запрещенные символы)
	const ERROR_PWD_EASY = 8192; 		//пароль слишком простой
	const ERROR_PWD_SHORT = 16384; 		//пароль слишком короткий
	
	//типы логинов
	const LOGIN_TYPE_EMAIL = 1;  	//логин в виде email-а

	//таблица пользователей
	static $users_table = "users";
	static $users_table_cols = array("id","login","pwd","type","status","regDate");  //поля таблицы пользователей
	//таблица пользовательских параметров
	static $users_params_table = "users_params";
	static $default_user_type = 2;
	
	//функция ищет пользователя по id или логину.
	//параметры в options:
	// id = null - идентификатор (предпочтение отдается ему)
	// login = null - логин
	// hide_pwd = true - не возвращать пароль
	// auth = false - записывать пользователя в сессию
	static function getUser(array $options) {
		//инициализируем параметры по умолчанию
		$options = Common::initFunc($options,array("id" => null,"login" => null,"hide_pwd" => true,"auth" => false));
		Database::DB()->reset();
		//делаем запрос в зависимости от параметра поиска
		if(!empty($options["id"]))
			Database::DB()->select(self::$users_table,self::$users_table_cols)->where("id",$options["id"],"=")->exec();
		else if(!empty($options["login"]))
			Database::DB()->select(self::$users_table,self::$users_table_cols)->where("login",$options["login"],"LIKE")->exec();
		else return null;
		//достаем пользователя из выборки
		$res = Database::DB()->getRow();
		//если требуется, скрываем пароль
		if(!empty($res)) {
			if($options["hide_pwd"] == true) $res["pwd"] = null; //скрываем пароль
			//пишем информацию о пользователе в сессию
			if($options["auth"]) self::authUser($res);
		}
		return $res;
	}

	//функция авторизации пользователя (т.е. ф-ия заносит в сессиию информацию о пользователе)
	static function authUser($user) {
		//проверяем входящие данные
		if(empty($user["id"]) || empty($user["type"]) || empty($user["status"])) return false;
		//заносим данные в сессию
		$_SESSION["uid"] = $user["id"]; //для совместимости
		$_SESSION["user"]["id"] = $user["id"];
		$_SESSION["user"]["login"] = $user["login"];
		$_SESSION["user"]["type"] = $user["type"];
		$_SESSION["user"]["status"] = $user["status"];
		return true;
	}

	//функция добавления нового пользователя
	static function addUser($login, $pwd1, $pwd2, $type, $company, $city, $login_type = self::LOGIN_TYPE_EMAIL) {
		//проводим валидацию логина
		$error = self::validateLogin($login,true,$login_type);
		if($error !== self::ERROR_NOT) return $error;
		
		//проводим валидацию пароля
		$pwd_strong_level = 0;	
		$error = self::validatePassword($pwd1,$pwd2,$pwd_strong_level);
		if($error !== self::ERROR_NOT) return $error;
		
		//информация о пользователе
		$user = array(
			"login" => $login,
			"pwd" => Crypt::password_hash($pwd1, Crypt::CRYPT_HASH_DEFAULT),
			"type" => $type,
			"company" => $company,
			"city" => $city,
			"status" => 1);
			
		//пытаемся добавить пользователя в бд
		if(Database::DB()->insert(self::$users_table,$user) <= 0) return self::ERROR_USER_ADD;
		//сразу авторизуем пользователя, задав его в сессии
		self::authUser();

		return self::ERROR_NOT;
	}
		
	//верификация пользователя через логин и пароль. в переменную $user считывается информация о пользователе
	static function verifyUser($login, $password,&$user,$auth = false) {
		//проверяем данные
		if(empty($login)) return self::ERROR_LOGIN_EMPTY;
		//ищем пользователя по логину
		//и проверяем пароли
		$user = self::getUser(array("login" => $login, "hide_pwd" => false, "auth" => false));
		//если не нашли, возвращаем соотвествующую ошибку
		if(empty($user)) return self::ERROR_USER_NOT_FOUND;
		//проверяем пароль
		if(empty($password)) return self::ERROR_PWD_EMPTY;
		if(!Crypt::password_verify($password, $user['pwd'])) return self::ERROR_PWD_NOT_MATCH;
		return self::ERROR_NOT;
	}

	//функция устанавливает новый пароль для пользователя по id или логину.
	//параметры в options:
	// id = null - идентификатор (предпочтение отдается ему)
	// login = null - логин
	// pwd1, pwd2 - пароль и его подверждение
	// old_pwd = null - старый пароль, если требуется подверждение
	static function setUserPassword($options) { //} $login,$pwd1,$pwd2,$old_pwd = null) {
		//инициализируем параметры по умолчанию
		$options = initFuncDefaultParams($options,array("id" => null,"login" => null,"old_pwd" => null));
		//ищем пользователя
		$user = self::getUser(array("id" => $options["id"],"login" => $options["login"],"hide_pwd" => false,"auth" => false));
		if(empty($user)) return self::ERROR_USER_NOT_FOUND;  //
		//проверяем старый пароль, если он указан
		if((!empty($old_pwd) && !empty($user['pwd']))&&(!Crypt::password_verify($old_pwd, $user['pwd']))) return self::ERROR_PWD_NOT_MATCH;

		//проводим валидацию нового пароля
		$pwd_strong_level = 0;
		$error = self::validatePassword($options["pwd1"],$options["pwd2"],$pwd_strong_level);
		if($error !== self::ERROR_NOT) return $error;

		//обновляем запись в бд
		Database::DB()->reset();
		Database::DB()->update(self::$users_table,array("pwd" => Crypt::password_hash($options["pwd1"], Crypt::CRYPT_HASH_DEFAULT)))->where("id",$user["id"])->exec();
		return self::ERROR_NOT;
	}

	//функция проверки логина. если $reg = true, то проверка для регистрации. если type = 1 - логин представляет из себя email
	//возвращает код ошибки или ERROR_NOT, в случае успеха ($login при этом модифицируется к коррекнтому виду).
	static function validateLogin(&$login,$reg = false,$type = self::LOGIN_TYPE_EMAIL) {
		//если логин не указан, возвращаем ошибку
		if(empty($login)) return self::ERROR_LOGIN_EMPTY;

		if($type == 1) { //если проверка как email-а
			//делаем валидвацию строки $login
			$login = filter_var(trim($login), FILTER_VALIDATE_EMAIL);
			if(!$login) //если это не email, возвращаем ошибку
				return self::ERROR_LOGIN_VALIDATE;
		} else //если тип логина не определен, возвращаем ошибку
			return self::ERROR_LOGIN_TYPE;

		//ищем id пользователя
		$user = self::getUser(array("login" => $login));

		//в зависиости от того, нашли id или нет, а так же от флага $reg, возвращаем результат
		if(!isset($user)||($user["id"] < 1)) {
			if($reg) return self::ERROR_NOT;
			else return self::ERROR_USER_NOT_FOUND;

		}
		if($reg) return self::ERROR_USER_REGISTERED;
		return self::ERROR_NOT;
	}

	//функция проверки пароля. проверяет два пароля на пустоту и равенство между собой, после чего один из паролей проверяется
	//на стойкость. возвращает код ошибки. в переменную $strong_level записывается уровень стойкости пароля от 0 до 4
	static function validatePassword($pwd1,$pwd2,&$strong_level) {
		//проверяем, не пустые ли пароли
		if(empty($pwd1)) return self::ERROR_PWD_EMPTY;
		//проверяем коррекнтость пароля
		if(!preg_match('/[A-Za-z0-9]/', $pwd1)) return self::ERROR_PWD_NOT_CORRECT;
		//проверяем уровень стойкости пароля
		if(strlen($pwd1) < 4) return self::ERROR_PWD_SHORT;
		$strong_level = 0;
		if(strlen($pwd1) >= 6) $strong_level++;
		if(strlen($pwd1) >= 12) $strong_level++;
		if(preg_match('/[A-Za-z]/', $pwd1)) $strong_level++;
		if(preg_match('/[0-9]/', $pwd1)) $strong_level++;
		//если в настройках указан минимальный уровень стойкости пароля, делаем сравнение
		if((!empty(Settings::$password_level))&&(Settings::$password_level > $strong_level)) return self::ERROR_PWD_EASY;
		//
		//проверяем, одинаковы ли пароли
		if((empty($pwd2))||(strcmp($pwd1,$pwd2) !== 0)) return self::ERROR_PWD_DIFFERENT;

		return self::ERROR_NOT;
	}

	//функция возвращает текстовое сообщение об ошибке
	static function errorMsg($error_type) {
		switch($error_type) {
			case self::ERROR_NOT: 				return "Ошибок нет";
		
			case self::ERROR_LOGIN_EMPTY: 		return "Логин не указан";
			case self::ERROR_LOGIN_TYPE: 		return "Тип логина не определен";
			case self::ERROR_LOGIN_VALIDATE: 	return "Логин не корректен";
			case self::ERROR_USER_NOT_FOUND:	return "Пользователь не найден";
			case self::ERROR_USER_REGISTERED:	return "Пользователь уже зарегистрирован";
			case self::ERROR_USER_ADD:			return "Не удалось добавить пользователя";
			case self::ERROR_USER_UPDATE:		return "Не удалось обновить информацию о пользователе";
			case self::ERROR_USER_DELETE:		return "Не удалось удалить пользователя";
			case self::ERROR_USER_STATUS:		return "Некорректный статус пользователя";
			
			case self::ERROR_PWD_EMPTY: 		return "Пароль не указан";
			case self::ERROR_PWD_DIFFERENT: 	return "Пароли не совпадают";
			case self::ERROR_PWD_NOT_MATCH: 	return "Неверный пароль";
			case self::ERROR_PWD_NOT_CORRECT:	return "Пароль содержит запрещенные символы";
			case self::ERROR_PWD_EASY:			return "Пароль слишком простой";
			case self::ERROR_PWD_SHORT:			return "Пароль слишком короткий (менее 4 символов)";
			
			default: return "";
		}
	}
}