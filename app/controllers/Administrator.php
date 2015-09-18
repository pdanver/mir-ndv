<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 27.05.15
 * Time: 12:35
 */

class Administrator extends Controller
{
    function __construct()
    {
       if(Settings::$ADMIN_REALM !== Route::getRealm())
          View::renderClosed();

        $this ->checkAuthorization();
    }

    /**
     * Метод, делигирующий выполненеие в другой контроллер. Вызывается, если
     *
     * @param $methodName по факту является названием контроллера, в который должно быть передано управление
     * @param $args
     */

    function __call($methodName, $args)
    {
        $class = Route::getDir(Settings::$controllersDir, $methodName);
        if(!$class)
            parent::__call($methodName, $args);

        include_once $class;

        $this ->loadNavigation();

        $class = new $methodName(true);
        $class ->admin();
        View::render();
    }

    function checkAuthorization()
    {
        if(!isset($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_USER'])
            || !isset($_SERVER['PHP_AUTH_PW']))
            $this ->sendAuthRequest();

        $users = Users::getAdminUsers();
        if(array_key_exists($_SERVER['PHP_AUTH_USER'], $users))
            if(!password_verify($_SERVER['PHP_AUTH_PW'], $users[$_SERVER['PHP_AUTH_USER']]))
                $this ->sendAuthRequest();

        if(!isset($_SESSION['authDate']))
            $_SESSION['authDate'] =  time();
        else
            if($_SESSION['authDate'] + Settings::$sessionLifetime < time())
            {
                unset($_SERVER['PHP_AUTH_USER']);
                unset($_SERVER['PHP_AUTH_PW']);
                $this ->sendAuthRequest();
            }

        /* TODO: Заглушка для логирования */
    }

    private function sendAuthRequest($msg = 'Для входа на страницу администрирования нужно авторизоваться')
    {
        header('WWW-Authenticate: Basic realm="'.Route::getRealm().'"');
        header('HTTP/1.0 401 Unauthorized');
        View::renderClosed($msg);
    }

    private function loadNavigation()
    {
      $arr = Array('NAME' => $_SERVER['PHP_AUTH_USER']);
      $arr['NODES'] = View::load(array('engine', 'adminNavigationNode'),
                                             array('HREF' => '/'.__CLASS__, 'alias' => 'Статистика',
                                                   'img' => View::img('chart', 'icons')));
      $files = Engine::loadControllers();
      $permissions = Users::getAdminPermissions($_SERVER['PHP_AUTH_USER']);

       if(count($permissions) == 1 && empty($permissions[0]['controller']))
          $permissions = false;

      foreach($files as $row)
      {
         if($permissions && !in_array($row['name'], $permissions))
            continue;

         if(strstr($row['name'], strtolower(__CLASS__)))
             $row['name'] = 'admin';
         $row['HREF'] = '/'.__CLASS__.'/'.$row['name'];
         $row['img'] = View::img($row['img'], 'icons');
         $arr['NODES'] .= View::load(array('engine', 'adminNavigationNode'), $row);
      }

      View::setTitle('Страница статистики');
      View::css('engine');
      View::css('admin');
      View::js('engine');
      View::js('admin');

      View::loadContent('adminNavigation', $arr, 'engine');
    }

    function index()
    {
        $this ->statistic();
    }

    function admin()
    {
        $this ->loadNavigation();

        $arr = array('CONTROLLERS' => '',
                     'SITE' => (Settings::$ENGINE['site']?' checked':null),
                     'MULTI_LANG' => Settings::$ENGINE['multiLang']?' checked':null,
                     'MASK_URI' => Settings::$ENGINE['maskURI']?' checked':null,
                     'useJS' => Settings::$ENGINE['useJS']?' checked':null);

        $controllers = Engine::loadControllers();

        foreach($controllers as $c)
        {
            if($c['status'] == 0)
                $c['CL'] = ' selected';
            else
                if($c['status'] == 1)
                    $c['AD'] = ' selected';
                else
                    if($c['status'] == 2)
                        $c['OP'] = ' selected';
            $arr['CONTROLLERS'] .= View::load(array('engine', 'controllerNode'), $c);
        }
//
        if(Settings::$ENGINE['checkIP'] == 0)
            $arr['IPN'] = ' selected';
        else
            if(Settings::$ENGINE['checkIP'] == 1)
                $arr['IPR'] = ' selected';
            else
                if(Settings::$ENGINE['checkIP'] == 2)
                    $arr['IPF'] = ' selected';

        $arr['IP_LIST'] = '';
        foreach(Engine::loadIPList() as $val)
            $arr['IP_LIST'] .= $val['ip']."\r\n";
//
        $arr['F_STUF'] = '';
        foreach(Engine::loadForbidden() as $val)
            $arr['F_STUF'] .= $val['name'].'/'.$val['version']."\r\n";

        if(Settings::$ENGINE['checkFStuff'] == 0)
                $arr['SN'] = ' selected';
            else
                if(Settings::$ENGINE['checkFStuff'] == 1)
                    $arr['SR'] = ' selected';
                else
                    if(Settings::$ENGINE['checkFStuff'] == 2)
                        $arr['SF'] = ' selected';
//
        $arr['MASK_TABLE'] = View::table(Engine::loadURIMasks(),
            array('colNames' => array('Ссылка', 'Маска'),
                  'callback' => function(&$arr)
                                {
                                    $arr[0] = '<input type="text" value="'.$arr[0].'" readonly>';
                                    $arr[1] = '<input type="text" value="'.$arr[1].'" readonly>';
                                }
                ));
//
        $langs = Engine::getLanguageList();
        $arr['LANGUAGES'] = '';
        foreach($langs as $val)
            $arr['LANGUAGES'] .= View::selectOption($val, $val, false, ((Settings::$ENGINE['default_lang'] == $val)?true:false));
//
        $templates = scandir(Route::getDir(Settings::$viewsDir));
        $arr['TEMPLATES'] = '';
        foreach($templates as $val)
            if(!in_array($val, array('.', '..', 'engine')))
               $arr['TEMPLATES'] .= View::selectOption($val, $val, false, ((Settings::$ENGINE['template'] == $val)?true:false));

        View::loadContent('admin', $arr, 'engine');
        View::render();
    }

    function statistic()
    {
        $this ->loadNavigation();
        $arr = array('NODES' => '');

        $stat = array('NAME' => 'Движок', 'STATISTIC' => 'Пользователей онлайн: '.SessionManager::getConnectedCount());
        $stat['STATISTIC'] .= ' IP-адресов в списке: '.Engine::getIpCount();
        $stat['STATISTIC'] .= ' Софт в списке: '.Engine::getStuffCount();
        $stat['STATISTIC'] .= ' Подключено контроллеров: '.Engine::getControllersCount();
        $stat['STATISTIC'] .= ', из них активно: '.Engine::getActivedControllersCount();

        $arr['NODES'] = View::load(array('engine', 'statisticNode'), $stat);

        $files = Engine::loadControllers();

        foreach($files as $file)
            if($file['name'] != strtolower(__CLASS__))
            {
                $class = Route::getDir(Settings::$controllersDir, $file['name']);
                if($class)
                {
                    include_once $class;

                    $class = new $file['name'](true);
                    $arr['NODES'] .= $class ->statistic();
                }
            }

        View::loadContent('statistic', $arr, 'engine');
        View::render();
    }

   function change()
   {
         echo json_encode(array('type' => 'warning', 'cnt' => 'Сообщение о состоянии операции'));
   }
}