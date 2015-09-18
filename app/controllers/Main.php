<?php

/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 27.05.15
 * Time: 12:00
 */
class Main extends Controller
{
   function __construct()
   {

   }

   function index()
   {
      $this ->wikipediaParse();
//      Menu::addMenuItem(1, '/login/index', 'авторизация', 0);

//      $nodes = array('NODES' => '');
//      foreach(Navigation::loadMenu('main') as $val)
//         $nodes['NODES'] .= View::load(array('default', 'navigationNode'),
//            array('HREF' => '/'.$val['alias'], 'NAME' => $val['alias']));
//
//      View::css('engine');
//      View::css();
//      View::css('hexagonalNavigation');
//      View::loadHeader('header', array('NAVIGATION' => View::load(array('default', 'navigation'), $nodes)));
//      View::loadFooter('footer');
//      View::loadContent('main');
//      View::render();
   }

   function wikipediaParse()
   {
      $result = MySQLi::query('SELECT * FROM `addr_countries`');

      while($row = $result->fetch_assoc())
      {
         echo $row['name'].'<br>';
         $page = file_get_contents('https://ru.wikipedia.org/wiki/'.$row['name']);
         if($page === false)
            continue;

         preg_match_all('/<img(?:\\s[^<>]*?)?\\bsrc\\s*=\\s*(?|"([^"]*)"|\'([^\']*)\'|([^<>\'"\\s]*))[^<>]*>/i', $page, $allTags);

         $i = 0;
         foreach($allTags[0] as $val)
         {
            if(!empty($val) && strpos($val, 'Flag of'))
            {
               $begin = strpos($val, 'src="') + 5;
               $end = strpos($val, 'png"') + 3;
               $url = 'https:'.substr($val, $begin, $end - $begin);

               $img = './img/countriesFlags/'.$row['id'].'.png';
               file_put_contents($img, file_get_contents($url));
               $i++;
            }
            else
               if(!empty($val) && strpos($val, 'Coat of'))
               {
                  $begin = strpos($val, 'src="') + 5;
                  $end = strpos($val, 'png"') + 3;
                  $url = 'https:'.substr($val, $begin, $end - $begin);

                  $img = './img/countriesCoats/'.$row['id'].'.png';
                  file_put_contents($img, file_get_contents($url));
                  $i++;
               }

            if($i == 2)
               break;
         }

         preg_match('/<td>[A-Z]{2}<\/td>/', $page, $tmp);

         $iso = substr($tmp[0], 4, 2);

         preg_match('/<td>[A-Z]{3}<\/td>/', $page, $tmp);

         $mok = substr($tmp[0], 4, 3);

         preg_match('/\+[0-9]{1,5}</', $page, $tmp);

         $phone = substr($tmp[0], 1, strpos($tmp[0], '<') - 1);

         if(!$phone)
            $phone = 0;

         MySQLi::query('UPDATE `addr_countries` SET `iso` = \''.MySQLi::checkString($iso).'\', `mok` = \''.MySQLi::checkString($mok).'\', `flag` = \''.$row['id'].'.png\', `emplem` = \''.$row['id'].'.png\', `phone` = '.$phone.' WHERE `name` = \''.MySQLi::checkString($row['name']).'\'');
      }
   }

   function admin()
   {
      View::setTitle('Админка главной страницы');
      View::loadContent('error', array('ERROR_NUM' => '404'), 'engine');
   }

   function statistic()
   {
      $stat = array('NAME' => 'Главная', 'STATISTIC' => 'Тут типа главная страница и все такое');
//        $stat['STATISTIC'] .= ' IP-адресов в списке: '.Engine::getIpCount();
//        $stat['STATISTIC'] .= ' Софт в списке: '.Engine::getStuffCount();
//        $stat['STATISTIC'] .= ' Подключено контроллеров: '.Engine::getControllersCount();
//        $stat['STATISTIC'] .= ', из них активно: '.Engine::getActivedControllersCount();

      return View::load(array('engine', 'statisticNode'), $stat);
   }
}