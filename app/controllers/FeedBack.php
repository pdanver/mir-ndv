<?php
class FeedBack extends Controller
{
	function index()
	{
		$arr = array();
		$arr["select"] = FeedBackModel::getSelect();
		$arr["name"] = "Имя";
		$arr["siti"] = "Город";
		$arr["Email"] = "Email";
		return View::load(array(Settings::$ENGINE['template'], 'FeedBackForm'), $arr);
	}

	function ad()
	{
		$resultat = array("msg" => "", "f" => false);
		if(!isset($_POST["setid"]) &&  !isset($_POST["name"])  &&  !isset($_POST["siti"]) &&  !isset($_POST["email"]) &&  !isset($_POST["msg"]) &&  !isset($_POST["captcha"]) )
		{
			$resultat["mag"] = "Все поля обязательны к заполнению";
			$resultat["f"] = true;
		}

		////////////////Проверка КАПЧИ///////////////////
		
		if(strtolower($_POST["captcha"]) != strtolower($_SESSION["captcha"]))
		{
			$resultat["mag"] = "Неверный текст с картинки";
			$resultat["f"] = true;
		}
         unset($_SESSION["captcha"]);
		/* моя проверка капчи для примера. нужный код записан в сессии

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

		//очищаем курчу в сессии, чтобы не прошла второй раз
		unset($_SESSION["captcha"]);

		*/


		if($resultat["f"] == true){exit (json_encode($resultat));}

		$r = FeedBackModel::goFeedBack($_POST);
		if($r == null){$resultat["mag"] = "Неизвестная ошибка"; $resultat["f"] = true;exit (json_encode($resultat));}
		$resultat["msg"] = $r;
		exit (json_encode($resultat));
		//  exit ($resultat);


	}

	function admin()
	{
	}

	function statistic()
	{
	}
}
?>