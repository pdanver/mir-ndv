<?php
/**
 * User: DanVer
 * Date: 27.05.15
 * Time: 11:57
 */

include_once 'core/helpers/utils.php';
include_once 'app/models/UserBase.php';

class Profile extends Controller {
    function __construct() {
        
    }

    function index($args = null) {
        //
    }    
	
	function view() {
		return View::load(func_get_args()[0]);
    }

	//функция вызывает вьюшку карточки пользователя
	function view_edit() {
		$_SESSION["user"]["id"] = 1;

		if(isset($_SESSION["user"]["id"])) {
			$user = new UserCustomer();
			//if(!$user->load(array("id" => $_SESSION["user"]["id"])))
			//	return echoJS("alert('Не удалось загрузить информацию о пользователе. (".Common::getLastErrorMsg().")')");

			//$user->auth();

			$photo_file_name = "";
			$tmp = $user->photo;
			if(!empty($tmp)) $photo_file_name = ', file_name: "/'.$tmp.'"';

			$phones_str = "";
			if(!empty($user->phones)) foreach($user->phones as $phone) {
				if(strlen($phones_str) > 0 ) $phones_str .= ',';
				$phones_str .= '{ type: "'.$phone["type"].'", phone: "'.$phone["phone"].' "} ';
			}

			echoJS('var form_edit_customer = {
				url: "/Profile/edit/customer",
				inputs: {
					user_surname: { type: "text", name: "surname", attrs: { value: "'.$user->surname.'", tabindex : "1", placeholder: "Фамилия" }, title: "Фамилия" },
					user_name: { type: "text", name: "name", attrs: { value: "'.$user->name.'", tabindex : "2", placeholder: "Имя"}, title: "Имя"},
					user_old_name: { type: "text", name: "second_name", attrs: { value: "'.$user->second_name.'", tabindex : "3", placeholder: "Отчество"}, title: "Отчество" },
					user_sex: { type: "radio", name: "sex", title: "Пол: ", value: "'.$user->sex.'",
						items: [ { attrs: { value: "1", tabindex : "4"}, title: "Муж" }, { attrs: { value: "2", tabindex : "5"}, title: "Жен" }]
					},
					user_birthday: { type: "text", name: "birthday", attrs: { value: "'.$user->birthday.'", tabindex : "6", placeholder: "Дата рождения"}, title: "Дата рождения" },
					user_photo: { type: "file", name: "photo", attrs: { tabindex : "7", title: "Выберите картинку", accept: "image/bmp,image/jpeg,image/png,image/gif", placeholder: "Фото" '.$photo_file_name.' }, title: "Выберите фото" },
					user_photo_data: { type: "hidden", name: "photo_data" },

					user_phones: { type: "phone", name: "phone", title: "Телефоны", types: "mobile,Мобильный;home,Домашний;work,Рабочий", phones: ['.$phones_str.'] },
					user_jabber: { type: "email", name: "jabber", attrs: { value: "'.$user->jabber.'", placeholder: "Jabber" }, title: "Jabber" },
					user_skype: { type: "text", name: "skype", attrs: { value: "'.$user->skype.'", placeholder: "Skype"}, title: "Skype"},

					user_old_pwd: { type: "password", name: "old_pwd", attrs: { value: "", placeholder: "Пароль"}, title: "Текущий пароль"},
					user_pwd1: { type: "password", name: "pwd1", attrs: { value: "", placeholder: "Пароль"}, title: "Новый пароль"},
					user_pwd2: { type: "password", name: "pwd2", attrs: { value: "", placeholder: "Пароль"}, title: "Подтверждение"},

					user_country: {
						type: "select",
						name: "country",
						title: "Страна",
						options: [ {attrs: { value: "0"}, title: "-выберите страну-" },
								   {attrs: { value: "1"}, title: "Россия" }]
					},

					user_region: {
						type: "select",
						name: "region",
						title: "Регион",
						options: [ {attrs: { value: "0"}, title: "-выберите регион-" } ]
					}
				},
				title: "Карточка пользователя",
				button_ok_title: "Сохранить",
				button_cancel_title: "Отмена"
			};');

			echoJS('alert(form_edit_customer);');
			//echoJS('if(is_login === undefined) var is_login; is_login = true; if(user_name === undefined) var user_name; user_name = "'.$_SESSION["user"]["login"].'"');

			return View::load("profile/edit");
		}

		//echoJSVars(array("is_login" => "false","user_name" => "''"));
		echoJS("alert('Авторизуйтесь')");
	}

	//функция сохрананяет информацию о пользователе (для POST запросов)
	function edit($args = null) {
		$answer = array();
		$answer["success"]["fields"] = array();

		if(empty($_SESSION["user"]) || empty($_SESSION["user"]["id"])) {
			//$answer["error"]["field"] = "auth";
			$answer["error"]["msg"] = "Вы не авторизованы!";
			return $answer;
		}

		$user = new UserCustomer();
		if(!$user->load(array("id" => $_SESSION["user"]["id"]))) {
			//$answer["error"]["field"] = null;
			$answer["error"]["msg"] = Common::echoLastError();
			return $answer;
		}

		$validate_fields = array();
		if(isset($_POST["validate_fields"])) $validate_fields = $_POST["validate_fields"];
		$required_fields = array();
		if(isset($_POST["required_fields"])) $required_fields = $_POST["required_fields"];

		//считываем фамилию
		$user->surname = readPostField($answer,"surname",$validate_fields,$required_fields); if($user->surname === null) return $answer;
		//считываем имя
		$user->name = readPostField($answer,"name",$validate_fields,$required_fields); if($user->name === null) return $answer;
		//считываем отчество
		$user->second_name = readPostField($answer,"second_name",$validate_fields,$required_fields); if($user->second_name === null) return $answer;
		//считываем пол
		$user->sex = readPostField($answer,"sex",$validate_fields,$required_fields); if($user->sex === null) return $answer;
		//считываем дату рождения
		$user->birthday = readPostField($answer,"birthday",$validate_fields,$required_fields); if($user->birthday === null) return $answer;

		//$user->write();
		//если указана фотография
		if(!empty($_POST["photo_data"])) {
			//парсим ее в MIME
			$photo = new MIME();
			$photo_data = json_decode($_POST["photo_data"]);
			$photo->parseString($photo_data->image);
			//считываем область выделения и проверяем, установлена ли она
			$selection = $photo_data->selection;
			if(empty($selection) || empty($selection->width) || empty($selection->height)) {
				//если не установлена, то берем максимально большую область по размерам картинки
				$photo_size = $photo->imageSize();
				if($photo_size === false) {
					$answer["error"] = array("field" => "photo","Не удалось сохранить фотографию (".Common::getLastErrorMsg().")");
					return $answer;
				}
				$selection->x1 = 0; $selection->y1 = 0;
				if($photo_size["width"] <= $photo_size["height"]) {
					$selection->width = $photo_size["width"];
					$selection->height = $photo_size["width"];
				} else {
					$selection->width = $photo_size["height"];
					$selection->height = $photo_size["height"];
				}
			}
			//удаляем старую фотку, если есть
			$file_name = $user->photo;
			if(!empty($file_name) && file_exists($file_name)) unlink($file_name);
			//и сохраняем новую картинку с изменением размеров
			$file_name = $photo->saveToFile(User::$user_photo_dir.$user->id,
				array("x1" => 0,"y1" => 0,"width" => 200,"height" => 200),
				array("x1" => $selection->x1,"y1" => $selection->y1,"width" => $selection->width,"height" => $selection->height));
			if($file_name === false) {
				$answer["error"] = array("field" => "photo","msg" => "Не удалось сохранить фотографию (".Common::getLastErrorMsg().")");
				return $answer;
			}

			$user->photo = $file_name;
			$answer["success"]["fields"][] = "photo";
		}

		//считываем телефоны
		$phones = readPostField($answer,"phone",$validate_fields,$required_fields); if($phones === null) return $answer;
		$user->phones = array();
		foreach($phones as $phone) {
			$phone_data = explode(',',$phone);
			if(count($phone_data) != 2) {
				$answer["error"] = array("field" => "phone","msg" => "Телефоны указаны некорректно");
				array_pop($answer["success"]["fields"]);
				return $answer;
			}
			$user->phones[] = array("phone" => preg_replace('/[^0-9]/', '', $phone_data[1]),"type" => $phone_data[0]);
		}

		//считываем jabber
		$user->jabber = readPostField($answer,"jabber",$validate_fields,$required_fields); if($user->jabber === null) return $answer;
		//считываем skype
		$user->skype = readPostField($answer,"skype",$validate_fields,$required_fields); if($user->skype === null) return $answer;

		/*$old_pwd = readPostField($answer,"old_pwd",$validate_fields,$required_fields); if($old_pwd === null) return $answer;
		if(!empty($old_pwd)) {
			$pwd1 = $_POST["pwd1"];
			$pwd2 = $_POST["pwd2"];
			$pwd_strong = 0;
			$error = Users::validatePassword($pwd1,$pwd2,$pwd_strong);
			if($error !== Users::ERROR_NOT) {
				//в случае ошибки возвращаем текст ошибки
				//если пароли отличаются или второй пароль пустой, указываем фокус на второй пароль
				if(($error === Users::ERROR_PWD_DIFFERENT)||(($error === Users::ERROR_PWD_EMPTY)&&(!empty($pwd1)))) {
					$answer["error"]["field"] = "pwd2";
					array_push($answer["success"]["fields"],"pwd");
				} else
					$answer["error"]["field"] = "pwd";

				array_pop($answer["success"]["fields"]);
				return $answer;
			}
		}*/

		//если нужна была только валидация, выходим
		if((!isset($_POST["submit"]))||(!$_POST["submit"])) return $answer;

		//сохраняем
		if(!$user->save()) {
			$answer["error"] = array("field" => "submit","msg" => Common::echoLastErrorMsg());
			return $answer;
		}
		//указываем об успешности опреации и выходим
		$answer["success"]["msg"] = "Данные успешно сохранены";
		$answer["success"]["fields"][] = "submit";
		return $answer;
	}

	//функция вызывает вьюшку авторизации-регистрации, предварительно создавая переменный в js
	//если пользователь залогирован
	function view_login() {
		$user = null;
		//проверяем сперва, залогирован пользователь или нет и создаем необходимые переменные в js
		/*if(isset($_SESSION["user"]["id"]))
			echoJS('if(is_login === undefined) var is_login; is_login = true; if(user_name === undefined) var user_name; user_name = "'.$_SESSION["user"]["login"].'"');
		else
			echoJS('if(is_login === undefined) var is_login; is_login = false;');*/

		//форма регистрации
		echoJS('var form_reg = {
			url: "/Profile/reg",
			title: "Зарегестрироваться в mir-ndv",
			inputs: {
				company: { type: "text", name: "company", hint: "", attrs: { tabindex : "1", placeholder: "Название компании" } },
				city: { type: "search", name: "city", hint: "", attrs: { tabindex : "2", placeholder: "Город" } },
				login: { type: "email", name: "login", hint: "", attrs: { tabindex : "3", placeholder: "Email" } },
				pwd: { type: "password", name: "pwd", hint: "", attrs: { tabindex : "4", placeholder: "Пароль"  } },
				pwd2: { type: "password", name: "pwd2", hint: "", attrs: { tabindex : "5", placeholder: "Пароль еще раз" } },
				agent: { type: "checkbox", name: "agent", hint: "", title: "Я агент - " },
				captcha: { type: "text", name: "captcha", hint: "", attrs: { tabindex : "6", placeholder: "Код с картинки" } }
			}
		};');
		//форма авторизации
		echoJS('var form_auth = {
			url: "/Profile/auth",
			title: "Войти в mir-ndv",
			inputs: {
				login: { type: "email", name: "login", hint: "", attrs: { tabindex : "1", placeholder: "Email"} },
				pwd: { type: "password", name: "pwd", hint: "", attrs: { tabindex : "2", placeholder: "Пароль"} }
			}
		};');

		//и выводим вьюшку
		return View::load("profile/login");
	}

	//функця генерации капчи
	function captcha() {
		$width = 150;                  //Ширина изображения
		$height = 90;                  //Высота изображения
		$font_size = 17.5;   			//Размер шрифта
		$let_amount = 6;               //Количество символов, которые нужно набрать
		$fon_let_amount = 30;          //Количество символов, которые находятся на фоне
		$path_fonts = 'font/cour.ttf';        //Путь к шрифтам
		//доступные быквы и цвета 
		$letters = array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','2','3','4','5','6','7','9');
		$colors = array('10','30','50','70','90','110','130','150','170','190','210');
		//чистим фон 
		$src = imagecreatetruecolor($width,$height);
		$fon = imagecolorallocate($src,255,255,255);
		imagefill($src,0,0,$fon);
		//загружаем шрифт 
		$fonts = array();
		$dir=opendir($path_fonts);
		while($fontName = readdir($dir))
		{
		    if($fontName != "." && $fontName != "..")
			{
				$fonts[] = $fontName;
			}
		}
		closedir($dir);
		//забиваем фон 
		for($i=0;$i<$fon_let_amount;$i++)
		{
			$color = imagecolorallocatealpha($src,rand(0,255),rand(0,255),rand(0,255),100); 
			$font = $path_fonts.$fonts[rand(0,sizeof($fonts)-1)];
			$letter = $letters[rand(0,sizeof($letters)-1)];
			$size = rand($font_size-2,$font_size+2);
			imagettftext($src,$size,rand(0,45),rand($width*0.1,$width-$width*0.1),rand($height*0.2,$height),$color,$font,$letter);
		}
		//выводим текст
		for($i=0;$i<$let_amount;$i++)
		{
			$color = imagecolorallocatealpha($src,$colors[rand(0,sizeof($colors)-1)],$colors[rand(0,sizeof($colors)-1)],$colors[rand(0,sizeof($colors)-1)],rand(20,40)); 
			$font = $path_fonts.$fonts[rand(0,sizeof($fonts)-1)];
			$letter = $letters[rand(0,sizeof($letters)-1)];
			$size = rand($font_size*2.1-2,$font_size*2.1+2);
			$x = ($i+1)*$font_size + rand(4,7);
			$y = (($height*2)/3) + rand(0,5);
			$cod[] = $letter;   
			imagettftext($src,$size,rand(0,15),$x,$y,$color,$font,$letter);
		}
		//сохраняем в сессии код 
		$_SESSION['captcha'] = implode('',$cod);
		//генерируем файл 
		$file_name = "img/captcha/".session_id().".gif"; 
		imagegif($src,$file_name);
		//и выводим его ответ 
		$res = base64_encode(file_get_contents($file_name));
		//после чего удаляем файл и выходим
		unlink($file_name);
		return $res;
	}
	
	//функция регистрации
    //входные параметры: $_POST[
	function reg($args = null) {
		$answer = array();  //ответ
		$answer["success"]["fields"] = array(); //массив удачно заполненных полей
		/*$answer["error"]["msg"] = print_r($_POST,true);
		return $answer;*/

		//проводим валидацию логина
		$login = null;
		if(isset($_POST["login"])) $login = $_POST["login"];

		$error = Users::validateLogin($login,true,Users::LOGIN_TYPE_EMAIL);
        if($error !== Users::ERROR_NOT) {
			//в случае ошибки возвращаем текст ошибки
            $answer["error"]["field"] = "login";
			$answer["error"]["msg"]	= Users::errorMsg($error);
			return $answer;
		}
		array_push($answer["success"]["fields"],"login");

		//проводим валидацию пароля
		$pwd_strong = 0;
		$pwd1 = null;
		$pwd2 = null;
		if(isset($_POST["pwd"])) $pwd1 = $_POST["pwd"];
		if(isset($_POST["pwd2"])) $pwd2 = $_POST["pwd2"];
		$error = Users::validatePassword($pwd1,$pwd2,$pwd_strong);
		if($error !== Users::ERROR_NOT) {
			
			//в случае ошибки возвращаем текст ошибки
			//если пароли отличаются или второй пароль пустой, указываем фокус на второй пароль
			if(($error === Users::ERROR_PWD_DIFFERENT)||(($error === Users::ERROR_PWD_EMPTY)&&(!empty($pwd1)))) {
				$answer["error"]["field"] = "pwd2";
				array_push($answer["success"]["fields"],"pwd");
			} else
				$answer["error"]["field"] = "pwd";
			
			$answer["error"]["msg"]	= Users::errorMsg($error);
			return $answer;
		}
		array_push($answer["success"]["fields"],"pwd");
		array_push($answer["success"]["fields"],"pwd2");
		
		//проверяем капчу
		//считываем ее из поста и сессии
		$captcha = null;
		if(isset($_POST["captcha"])) $captcha = $_POST["captcha"];
		$session_captcha = null;
		if(isset($_SESSION["captcha"])) $session_captcha = $_SESSION["captcha"];
		if((empty($captcha))||(empty($session_captcha))) {
			//в случае ошибки возвращаем текст ошибки
            $answer["error"]["field"] = "captcha";
			$answer["error"]["msg"]	= "Текст с картинки не указан";
			return $answer;
		}

		//сверяем капчу
		if(strcmp(strtolower($captcha),strtolower($session_captcha)) != 0) {
			//в случае ошибки возвращаем текст ошибки
			$answer["error"]["field"] = "captcha";
			$answer["error"]["msg"]	= "Неверный текст с картинки";
			return $answer;
		}
		array_push($answer["success"]["fields"],"captcha");

		//если нужна была только валидация, выходим
		if((!isset($_POST["submit"]))||(!$_POST["submit"])) return $answer;

		//очищаем курчу в сессии, чтобы не прошла второй раз
		unset($_SESSION["captcha"]);

		$agent = false;
		$company = "";
		$city = "";
		$type = 1;
		if(isset($_POST["agent"]) && ($_POST["agent"] == true)) {
			if(isset($_POST["city"])) $city = $_POST["city"];
			if(isset($_POST["company"])) $company = $_POST["company"];
			$type = 2;
		}

		//добавляем пользователя
		$error = Users::addUser($login,$_POST["pwd"],$_POST["pwd2"], $type, $company, $city, Users::LOGIN_TYPE_EMAIL);
		if($error !== Users::ERROR_NOT) {
			$answer["error"]["field"] = "submit";
			$answer["error"]["msg"] = Users::errorMsg($error);
			return $answer;
		}

		Users::getUser(array("login" => $login,"auth" => true));

		//возвращаем код успешной операции
		array_push($answer["success"]["fields"],"submit");
		$answer["success"]["msg"] = "Поздравляю, вы успешно зарегистрировались";
		//выводим ответ
		return $answer;
	}

	//функция авторизации
	function auth($args = null) {
		$answer = array();  //ответ
		$answer["success"]["fields"] = array(); //массив удачно заполненных полей

        //верифицируем пользователя
		$login = $_POST["login"];
		$pwd = $_POST["pwd"];
		$user = array();
		$error = Users::verifyUser($login,$pwd,$user);
		if($error !== Users::ERROR_NOT) {
			//в случае ошибки возвращаем текст ошибки
			if(($error == Users::ERROR_LOGIN_EMPTY)||($error == Users::ERROR_USER_NOT_FOUND))
				$answer["error"]["field"] = "login";
			else {
				array_push($answer["success"]["fields"],"login");
				if(($error == Users::ERROR_PWD_EMPTY)||($error == Users::ERROR_PWD_NOT_MATCH))
					$answer["error"]["field"] = "pwd";
				else {
					array_push($answer["success"]["fields"], "pwd");
					$answer["error"]["field"] = "submit";
			}
			}

			$answer["error"]["msg"]	= Users::errorMsg($error);
			return $answer;
		}

		//если нужна была только валидация, выходим
		if((!isset($_POST["submit"]))||(!$_POST["submit"])) return $answer;

		//авторизуемся, если все ок
		Users::authUser($user);

		//возвращаем код успешной операции
		array_push($answer["success"]["fields"],"submit");
		$answer["success"]["msg"] = "Поздравляю, вы успешно авторизовались";
		return $answer;
	}

	//вункция выхода пользователя (для запросов)
	function login_out() {
		unset($_SESSION["user"]);
		return "";
	}

	//функция проверяет, авторизован ли пользовать по переменной uid в сессии, и если да, возвращает
	//информацию о пользователе (его логин) (для запросов)
	function login_info() {
		$user = null;
		if(!empty($_SESSION["user"]["id"])) { //если uid задан
			$user = Users::getUser(array("id" => $_SESSION["user"]["id"]));
			if(!empty($user)) { //и пользователь найден
				//выводим его логин
				$answer["success"]["login"] = $user["login"];
				return $answer;
			} else { //если пользователь не найден
				//возвращаем текст ошибки
				$answer["error"]["msg"] = "Пользователь не найден. Пожалуйста, повторите попытку авторизации.";
				return json_encode($answer);
			}
		} else {  //если uid не задан
			//возвращаем текст ошибки
			$answer["error"]["msg"]	= "Вы не авторизованы. Пожалуйста, пройдите авторизацию.";
			return json_encode($answer);
		}
	}
	
	function set_pwd($args = null) {
		$answer = array();  //ответ
		$answer["success"]["fields"] = array(); //массив удачно заполненных полей
		//считываем данные
		$login = null;
		$pwd1 = null;
		$pwd2 = null;
		$old_pwd = null;
		if(isset($_POST["login"])) $login = $_POST["login"];
		if(isset($_POST["pwd"])) $pwd1 = $_POST["pwd"];
		if(isset($_POST["pwd2"])) $pwd2 = $_POST["pwd2"];
		if(empty($_POST["old_pwd"])) {
			//в случае ошибки возвращаем текст ошибки
			$answer["error"]["field"] = "old_pwd";
			$answer["error"]["msg"]	= "Старый пароль не указан";
			return $answer;
		}
		$old_pwd = $_POST["old_pwd"];

		//устанавливаем новый пароль
		$error = Users::setUserPassword(array("login" => $login,"pwd1" => $pwd1,"pwd2" => $pwd2,"old_pwd" => $old_pwd));
		if($error !== Users::ERROR_NOT) {
			//в случае ошибки возвращаем текст ошибки
			$answer["error"]["field"] = "submit";
			$answer["error"]["msg"]	= Users::errorMsg($error);
			return $answer;
		}
		
		//возвращаем код успешной операции
		array_push($answer["success"]["fields"],"submit");
		$answer["success"]["msg"] = "Поздравляю, вы успешно изменили пароль";
		return $answer;
	}

	function restore_pwd() {
		$answer = array();  //ответ

		//считываем данные
		$login = null;
		if(isset($_POST["login"])) {
			$login = $_POST["login"];
			$user = Users::getUser(array("login" => $login));
			if(empty($user)) { //если пользователь не найден
				//возвращаем текст ошибки
				$answer["error"] = array("msg" => "Пользователь не найден", "field" => "login");
				return json_encode($answer);
			}

			$pwd = Crypt::password_generate(6);

			$error = Users::setUserPassword(array("login" => $login,"pwd1" => $pwd,"pwd2" => $pwd));
			if($error !== Users::ERROR_NOT) {
				//в случае ошибки возвращаем текст ошибки
				$answer["error"]["field"] = "submit";
				$answer["error"]["msg"]	= Users::errorMsg($error);
				return $answer;
			}

			if(!mail($login,"Пароль на MIR-NDV","Ваш новый пароль: ".$pwd)) {
				$answer["error"] = array("msg" => "Извините, но не удалось отправить письмо на Ваш почтовый ящик ".$login);
				return json_encode($answer);
			}

			$answer["success"] = array("msg" => "На Ваш почтовый ящик ".$login." отправлено письмо с новым паролем ");
			return json_encode($answer);
		} else {  //если uid не задан
			//возвращаем текст ошибки
			$answer["error"] = array("msg" => "Пользователь не указан", "field" => "login");
			return json_encode($answer);
		}

		$answer["error"] = array("msg" => "Извините, но не удалось восстановить пароль. Попробуйте повторить еще раз.");
		return json_encode($answer);
	}

	function admin() {
        View::setTitle('Админка авторизации');
        View::loadContent('loginAdmin', null, 'engine');
    }

    function statistic() {
        $stat = array('NAME' => 'Авторизация');
        $stat['STATISTIC'] = 'Пользователей в системе: '.Users::getUsersCount();
        $stat['STATISTIC'] .= ' Администраторов в системе: '.Users::getAdminUsersCount();

        return View::load(array('engine', 'statisticNode'), $stat);
    }
}

function addErrorToAnswer($answer,$field,$msg) {
	$answer["error"]["field"] = $field;
	$answer["error"]["msg"] = $msg;
	return $answer;
}

//функция считывает поле из пост-а
function readPostField(&$answer,$field_name,$validate_fields,$required_fields) {
	if(!empty($_POST[$field_name])) {
		$answer["success"]["fields"][] = $field_name;
		return $_POST[$field_name];
	} else {
		if(array_search($field_name,$required_fields) !== false) {
			$answer["error"] = array("field" => $field_name, "msg" => "Поле не может быть пустым"); return null;
		}
		if(!isset($_POST[$field_name]) && (array_search($field_name,$validate_fields) !== false)) {
			$answer["error"] = array("field" => $field_name, "msg" => "Поле не указано"); return null;
		}
	}
	return "";
}