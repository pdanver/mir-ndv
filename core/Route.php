<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 08.04.15
 * Time: 9:07
 */

defined("TRUEADMIN") or die('Доступ запрещен');

include_once 'app/Settings.php';

if(Settings::$SHOW_ERRORS)
{
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(-1);
}

class Route
{
   /**
    * Функция поиска файла по введенному названию
    *
    * @param        $name - строка или массив строк, указывающие на каталоги(подкаталоги) расположения файла
    * @param string $file название файла, может не указываться, вернет путь до папки
    *
    * @return bool|string вернет false в случае неудачи и полный путь к файлу, в случае успеха
    */

   static function getDir($name, $file = '')
   {
      if(is_array($name))
         $name = join(DIRECTORY_SEPARATOR, $name);
      if(is_array($file))
         $file = join(DIRECTORY_SEPARATOR, $file);

      $tmp = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR;
		
	  if($file)
      {
         $file[0] = '*';
		 $tmp .= $file;

         $files = glob($tmp.'.*');
		 if($files)
            $tmp = $files[0];
         else
            return false;
      }

      return $tmp;
   }

   /**
    * Функция парсинга заголовка клиента пользователя. Узнает ОС, название браузера и его версию
    * (распознает Яндекс браузер как хром)
    *
    * @return array
    */

   static function getUserBrowser()
   {
      $ret = array('os' => 'unknown', 'browser' => 'unknown', 'version' => 'unknown');
      $agent = $_SERVER['HTTP_USER_AGENT'];
      preg_match("/(MSIE|Opera|Firefox|Chrome|Version)(?:\/| )([0-9.]+)/", $agent, $bInfo);

      $ret['browser'] = ($bInfo[1] == "Version") ? "Safari" : $bInfo[1];
      $ret['version'] = $bInfo[2];

      $osB = strpos($_SERVER['HTTP_USER_AGENT'], '(');
      $osE = strpos($_SERVER['HTTP_USER_AGENT'], ')');

      $ret['os'] = substr($_SERVER['HTTP_USER_AGENT'], $osB + 1, $osE - $osB - 1);

      return $ret;
   }

   /**
    * Определяет является ли запрос частью AJAX или нет
    *
    * @return bool
    */

   static function isInnerRequest()
   {
      if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
         return true;
      else
         return false;
   }

   /**
    * Проверяем список ip адресов
    * Параметр из настроек:
    *  0 - не проверять
    *  1 - разрешать доступ только из списка
    *  2 - запрещать доступ из списка
    *
    * @return bool/array
    */

   static function checkIpList()
   {
      $founded = false;
      $args = array();
      $setting = 1;

      $result = MySQLi::query('SELECT * FROM `ufo_ip_list`');

      if($result ->num_rows > 0)
         while($row = $result ->fetch_assoc())
            if($row['ip'] == $_SERVER['REMOTE_ADDR'])
            {
               $founded = true;
               $args['date'] = MySQLi::formatDate($row['date']);
               $args['reason'] = $row['reason'];
            }

      if($founded && $setting == 2)
         $args['msg'] = 'Ваш IP адресс забокирован';
      else if(!$founded && $setting == 1)
         $args['msg'] = 'Пользователям с вашего IP адресса вход не разрешен';
      else
         $args = false;

      return $args;
   }

   /**
    * Определяет переменную Realm, для работы некоторых разделов сайта
    *
    * @return string mixed
    */

   static function getRealm()
   {
      $t = explode('.', $_SERVER['HTTP_HOST']);

      return array_shift($t);
   }

   /**
    * TODO: заглушка
    *
    * @return mixed
    */

   static function getMainDomain()
   {
      return $_SERVER['HTTP_HOST'];
   }

   /**
    * Метод автозагрузки классов
    *
    * @param string $class
    */

   static function autoLoad($class)
   {
	   //echo $class;
      if(file_exists('app/models/'.$class.'.php'))
         include_once 'app/models/'.$class.'.php';
      else if(file_exists('app/controllers/'.$class.'.php'))
         include_once 'app/controllers/'.$class.'.php';
      else if(file_exists('core/'.$class.'.php'))
         include_once 'core/'.$class.'.php';
      else if(file_exists('core/exceptions/'.$class.'.php'))
         include_once 'core/exceptions/'.$class.'.php';
	  else if(file_exists('core/helpers/'.$class.'.php'))
         include_once 'core/helpers/'.$class.'.php';
   }

    static function start()
    {
        try
        {
            Session::start();

            //загрузка параметров движка
            Settings::$ENGINE = Engine::loadSettings();

            //проверка закрыт сайт или нет
            if(Settings::$ENGINE['site'] == 0)
                View::renderClosed('Извините, доступ на сайт закрыт');

            View::$template = Settings::$ENGINE['template'];
            Controller::$lang = Settings::$ENGINE['default_lang'];

            $route = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

            //считываем из сессии id пользователя (-1 назначаем неавторизованным пользователям)
            $user_id = -1;
            if(isset($_SESSION['uid'])&&(!empty($_SESSION['uid']))&&(is_numeric($_SESSION['uid']))) {
                $user_id = $_SESSION['uid'];
            }

            /*$pwd = "ruh";
            $hash = Crypt::password_hash($pwd);
            if(Crypt::password_verify($pwd,$hash)) echo "true";
            else echo "false";
            exit;*/

            /*print_r(AclModel::getRoles());
            exit;*/

            //проверяем, не был ли передан тип запроса (например при ajax запросе)
            $request_type = null;
            if(isset($_POST["request_type"])&&(is_numeric($_POST["request_type"]))) {
                $request_type = $_POST["request_type"];
                ini_set('display_errors',0);
            } else {
                $page = PageManager::findPage($route=='/'?'/main':$route);

                if(!$page)
                    throw new Exception404();

                View::setTitle($page['title']);
                View::css(explode('|', $page['css']));
                View::js(explode('|', $page['js']));
                //TODO: замутить парсер META

                $args = json_decode($page['content'], true);

                foreach($args as $key => $val)
                {
                    $class = new $args[$key]['c']();
                    View::$args[$key] = $class->$args[$key]['m'](!empty($args[$key]['a'])?$args[$key]['a']:null);
                }

                //print_r(View::$args);
                View::render('index', Settings::$ENGINE['template']);
            }

            //Если страницы не были найдены, то обращаемся напрямую к контроллеру
            $routes = explode('/', $route);
            array_shift($routes);
            if($routes)
            {
                //задаем контроллер и его метод по умолчанию
                $controller_name = Settings::$DEFAULT_CONTROLLER;
                $action = 'index';

                //распарсиваем адресную строку
                //определяем имя контроллера
                if(!empty($routes[0]))
                    $controller_name = $routes[0];
                //его метод
                if(!empty($routes[1]))
                    $action = $routes[1];
                //все остальные параметры адресной строки преобразуем в аргументы
                $i = 2;
                $arg = array();
                while(!empty($routes[$i]))
                    $arg[] = $routes[$i++];

                //создаем объект для работы контроллером
                if(!class_exists($controller_name))
                    throw new Exception404();
                $controller = new $controller_name();

                //проверяем, если это пост запрос c request_type, обрабатываем его соответственно
                if($request_type != null) {
                    //проверяем, есть ли данный метод у контроллера
                    if(method_exists($controller_name,$action)) {//если метод есть, то
                        //запускаем главную функцию обработки запроса
                        $res = $controller->$action(isset($arg)?$arg:NULL);
                        //выводим результат
                        echo json_encode(array('answer' => $res));
                    } else  //если функция не видна, то выдаем сответствующюю инфу
                        echo json_encode(array("error" => 'Не найдена функция для обработки запроса ('.$controller_name.'::'.$action.')!'));
                    exit;
                }
                //если это не запрос, просто вызываем метод
                $controller->$action(isset($arg)?$arg:NULL);

                unset($routes);
            }
            else
                throw new Exception404();

        }
        catch(mysqli_sql_exception $e)
        {
            if(Settings::$DEBUG)
                echo '<br>DEBUG: mysqli_sql_exception: '.$e->getMessage().' => '.$e->getCode().'<br>';
            Log::writeLog($e->getMessage().' '.$e->getCode());
        }
        catch(Exception404 $e)
        {
//         Здесь сделаем так, что бы 404 выдавалось в контент или куда-то еще, что бы не нарушать целостность сайта
//         View::render404();
            echo '404';
        }
        catch(Exception $e)
        {
            if(Settings::$DEBUG)
                echo '<br>DEBUG: Exception: '.$e->getMessage().' => '.$e->getCode().'<br>';
        }
////        проверка на запрещенные или разрешенные IP-адреса
//        if(Settings::$ENGINE['checkIP'] > 0)
//        {
//            $ip = Engine::checkIp($_SERVER['REMOTE_ADDR']);
//
//            if($ip && Settings::$ENGINE['checkIP'] == 1)
//                View::renderClosed($ip['reason'], $ip['date']);
//            else
//                if(!$ip && Settings::$ENGINE['checkIP'] == 2)
//                    View::renderClosed('Доступ к сайту с Вашего IP-адреса запрещен');
//        }
//
//        /**
//         * проверка на запрещенные или разрешенные ОС, браузеры или их версии
//         * TODO: тут, по-хороошему, нужно поближе рассмотреть этот мехвнизм, обточить
//         */
//        if(Settings::$ENGINE['checkFStuff'] > 0)
//        {
//            $r = Engine::checkForbiddenStuff(self::getUserBrowser());
//            if($r && Settings::$ENGINE['checkFStuff'] == 1)
//                View::renderClosed($r);
//            else
//                if(!$r && Settings::$ENGINE['checkFStuff'] == 2)
//                    View::renderClosed('Сайт не поддерживает работу с Вашими устройствами');
//        }
//
//
////        проверка включена ли мультиязычность или нет, по умолчанию используется только русский язык
//        if(Settings::$ENGINE['multiLang'])
//            if(!empty($_COOKIE['lang']))
//                Controller::$lang = $_COOKIE['lang'];
    }
}