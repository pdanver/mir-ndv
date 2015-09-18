<?php
/**
 * Created by IntelliJ IDEA.
 * User: takeru
 * Date: 29.07.15
 * Time: 4:36
 */

class Menu extends Controller
{
   /**
    * Метод отображения главной страницы контроллера
    *
    * @return mixed
    */

   function index()
   {
      if(func_num_args())
      {
         $nav = Navigation::loadMenu(func_get_args()[0]);

         $str = array();
         foreach($nav as $key => $val)
         {
            $val['alias'] = preg_replace('/[~^$\/|]/', '', $val['alias']);

            if($val['parentId'])
            {
               if(!array_key_exists($val['parentId'], $str))
                  $str[$val['parentId']] = array('HREF' => $val['href'],  'NAME' => $val['alias'], 'SUBMENU' => '');
               $str[$val['parentId']]['SUBMENU'] = array('HREF' => $val['href'], 'NAME' => $val['alias']);
               continue;
            }

            if(array_key_exists($val['id'], $str))
            {
               $str[$val['id']]['HREF'] = $val['href'];
               $str[$val['id']]['NAME'] = $val['alias'];
            }
            else
               $str[$val['id']] = array('HREF' => $val['href'],  'NAME' => $val['alias']);
         }

         $arr = array('NODES' => '');
         foreach($str as $val)
         {
            if(!empty($val['SUBMENU']))
            {
               $tmp = array('NODES' => View::load(array(Settings::$ENGINE['template'], 'navigationNode'), $val['SUBMENU']));
               $val['SUBMENU'] = View::load(array(Settings::$ENGINE['template'], 'navigation'), $tmp);
            }
            $arr['NODES'] .= View::load(array(Settings::$ENGINE['template'], 'navigationNode'), $val);
         }

         return View::load(array(Settings::$ENGINE['template'], 'navigation'), $arr);
      }
      return null;
   }

   /**
    * Метод отображения страницы администрирования.
    * Страница реализует управление только тем модумем, который реализует контроллер
    *
    * @param array $args массив маркеров и элементов навигации из контроллера администрирования
    *
    * @return mixed
    */

   function admin()
   {
      // TODO: Implement admin() method.
   }

   /**
    * Метод отображения страницы статистики, работает по аналогии с администрированием,
    * но не должен иметь элементов управления
    *
    * @param $args
    *
    * @return mixed
    */

   function statistic()
   {
      // TODO: Implement statistic() method.
   }
}