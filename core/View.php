<?php

/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 03.06.15
 * Time: 9:19
 */
class View
{
   static $template;
   static $args = array();

   /**
    * Устанавливает заголовок страницы
    *
    * @param string $title
    */

   static function setTitle($title)
   {
      self::$args['TITLE'] = $title;
   }

   /**
    * Устанавливает атрибут Lang в html
    *
    * @param string $lang
    */

   static function setLang($lang)
   {
      self::$args['LANG'] = ' lang="'.$lang.'"';
   }

   /**
    * Устанавливает один или несколько js файлов на страницу
    *
    * @param null|string|array $name имя(имена) файлов, находящихся в папке /js или на удаленном сервере(для этого должен содержать http://)
    * @param bool $async параметр указывает что скрипты будут загружаться асинхронно
    */

   static function js($name = null, $async = false)
   {
      if(!isset(self::$args['JS']))
         self::$args['JS'] = '';

      if(!$name)
         $name = self::$template;

      if(!is_array($name))
         $name = array($name);

      foreach($name as $val)
      {
         if(strpos($val, 'http://') === false)
         {
            $val .= '.js';
            if(file_exists('./js/'.$val))
               $val = 'http://'.Route::getMainDomain().'/js/'.$val;
         }
         self::$args['JS'] .= '<script type="text/javascript" '.($async?'async ':'').'src="'.$val.'"></script>';
      }
   }

   /**
    * Аналогично методу js, только ищет css только в локальной папке
    *
    * @param null|string|array $name
    */

   static function css($name = null)
   {
      if(!isset(self::$args['CSS']))
         self::$args['CSS'] = '';

      if(!$name)
         $name = self::$template;

      if(!is_array($name))
         $name = array($name);

      foreach($name as $val)
      {
         $val .= '.css';
         if(file_exists('./css/'.$val))
            self::$args['CSS'] .= '<link type="text/css" rel="stylesheet" href="http://'.Route::getMainDomain().'/css/'.$val.'">';
      }
   }

   /**
    * Метод возвращает форматированную таблицу HTML
    *
    * @param       $data набор данных(двумерный массив)
    * @param array $args доступны следующие аргументы массива(могут и отсутствовать):
    *                    colNames = array() массив названий колонок
    *                    idCol = false параметр разрешает дописывание колонки № по порядку
    *                    showCount = false параметр дописывает в конец таблицы строку с общим количеством элементов
    *                    maxCount = 0 общее количество элементов в таблице (обязательный параметр, если указан showCount)
    *                    showButtons = false параметр дописывает в конец таблицы кнопки навигации
    *                    offset = 0 смещение в таблице, используется в навигации
    *                    class = string|array строка или массив классов для таблицы
    *                    id идентификатор таблицы
    *                    callback function функция, выполняющаяся до добавлении строки в таблицу, может быть полезной для внесения
    *                    изменений в конкретную строку данных или для добавления внутренних элементов в ячейки таблицы
    *
    * @return string
    */

   static function table($data, $args = array())
   {
      $table = '<table';
      if(isset($args['id']))
         $table .= ' id="'.$args['id'].'"';

      if(isset($args['class']))
      {
         $table .= ' class="';
         if(is_array($args['class']))
            foreach($args['class'] as $var)
               $table .= $var.' ';
         else
            $table .= $args['class'];
         $table .= '"';
      }
      $table .= '>';

      if(isset($args['colNames']) && is_array($args['colNames']))
      {
         $table .= '<thead><tr>';

         if(isset($args['idCol']))
            $table .= '<th>№ п/п</th>';

         foreach($args['colNames'] as $val)
            $table .= '<th>'.$val.'</th>';
         $table .= '</tr></thead>';
      }

      $table .= '<tfoot>';

      $cellCount = count($data[0]);
      if(isset($args['idCol']))
         $cellCount++;

      if(isset($args['showCount']) && isset($args['maxCount']) && isset($args['offset']))
         $table .= '<tr><td colspan="'.$cellCount.'">Всего записей: '.$args['maxCount'].'</td></tr>';

      if(isset($args['showButtons']) && isset($args['maxCount']))
      {
         $toBegin = '<span class="nav">&#8676;</span>';
         $toEnd = '<span class="nav">&#8677;</span>';
         $left = '<span class="nav">&#8592;</span>';
         $right = '<span class="nav">&#8594;</span>';

         $navigation = '';
         $limit = count($data);
         $pagesCount = ceil($args['maxCount'] / $limit);
         $point = intval($args['offset'] / $limit);

         $navigation .= $args['offset'] < 3 * $limit ? '' : '<span offset="0">'.$toBegin.'</span>';

         for($i = 0; $i < $pagesCount; $i++)
            if($i == $point || $i == $point + 1 || $i == $point - 1)
               $navigation .= ($i == $point ? '<strong>'.($i + 1).'</strong>' : '<span offset="'.($i * $limit).'">'.($i < $point ? $left : $right).'</span>');

         if($point < $pagesCount - 2)
            $navigation .= '<span offset="'.($args['maxCount'] - $limit).'">'.$toEnd.'</span>';

         $table .= '<tr><td colspan="'.$cellCount.'">'.$navigation.'</td></tr>';
      }

      $table .= '</tfoot><tbody>';

      $i = 1;
      foreach($data as $d)
      {
         $table .= '<tr>';

         if(isset($args['idCol']))
            $table .= '<td>'.($i++).'</td>';

         if(isset($args['callback']) && is_callable($args['callback']))
            $args['callback']($d);

         foreach($d as $sd)
            $table .= '<td>'.$sd.'</td>';
         $table .= '</tr>';
      }
      $table .= '</tbody>';

      return $table;
   }

   static function link($name, $href, $target = null)
   {
      if(Engine::loadSettings('maskURI'))
         $href = Engine::loadURIMasks($href);

      return '<a href="/'.$href.'" '.$target.'>'.$name.'</a>';
   }

   static function img($name, $subDir = null)
   {
      $default = '/img/icons/ghost.png';
      if(empty($name))
         $name = $default;

      $files = glob('./img/'.(empty($subDir) ? '' : $subDir.'/').$name.'.*');

      if($files === false || empty($files))
         $name = $default;
      else
         $name = $files[0];

      return '<img src="http://'.Route::getMainDomain().'/'.$name.'" alt="">';
   }

   static function select($options = null, $id = null, $name = null, $class = null)
   {
      if(is_array($class))
         $class = implode(' ', $class);

      $class = ' class="'.$class.'"';

      if(is_array($options))
      {
         $tmp = '';
         foreach($options as $var)
            $tmp .= self::selectOption($var);

         $options = $tmp;
      }

      return '<select'.($id?' id="'.$id.'"':'').($name?' name="'.$name.'"':'').($class).'>'.$options.'</select>';
   }

   static function selectOption($html, $value = null, $disabled = false, $selected = false)
   {
      return '<option value="'.$value.'" '.($selected ? 'selected' : $selected).' '.($disabled ? 'disabled' : $disabled).'>'.$html.'</option>';
   }

   static function renderClosed($reason = null, $date = null)
   {
      $arg = array('IMG' => self::img('private', 'icons'));

      if($reason)
         $arg['REASON'] = $reason;
      if($date)
         $arg['DATE'] = $date;

      self::setTitle('Ошибка');
      self::loadContent('closed', $arg, 'engine');
      self::render(true);
   }

   static function loadContent($view, $args = null, $subDir = null)
   {
      if(!$subDir)
         $subDir = self::$template;

      $view = array($subDir, $view);

      if(!isset(self::$args['CONTENT']))
         self::$args['CONTENT'] = '';
      self::$args['CONTENT'] .= self::load($view, $args);
   }

   static function loadHeader($view, $args = null, $subDir = null)
   {
      if(!$subDir)
         $subDir = self::$template;

      $view = array($subDir, $view);

      if(!isset(self::$args['HEADER']))
         self::$args['HEADER'] = '';
      self::$args['HEADER'] .= self::load($view, $args);
   }

   static function loadFooter($view, $args = null, $subDir = null)
   {
      if(!$subDir)
         $subDir = self::$template;

      $view = array($subDir, $view);

      if(!isset(self::$args['FOOTER']))
         self::$args['FOOTER'] = '';
      self::$args['FOOTER'] .= self::load($view, $args);
   }

   /**
    * Метод загрузки вида
    *
    * @param            $name название файла
    * @param null|array $args необязательный параметр, массив значений маркеров для загружаемого вида
    *
    * @return mixed|string строка
    */

   static function load($name, $args = null)
   {
      $view = file_get_contents(Route::getDir(Settings::$viewsDir, $name));
      preg_match_all('/{(.*?)}/', $view, $items);

      foreach($items[1] as $key => $val)
      {
         if($args && !array_key_exists($val, $args))
            $args[$val] = '';

         $view = preg_replace('{'.$items[0][$key].'}', $args[$val], $view);
      }

      return $view;
   }

   /**
    * Выводит собраный html-шаблон на страницу и завершает выполнение скрипта
    *
    * @param string $file - название файла-шаблона
    * @param string $subDir - подкаталог, где находится шаблон
    */

   static function render($file = 'tmpl', $subDir = 'engine')
   {
      die(self::load(array($subDir, $file), self::$args));
   }

   static function render404()
   {
      View::setTitle('Страница не найдена');
      View::css('engine');
      View::loadContent('error', array('ERROR_NUM' => '404', 'ERROR_DESC' => 'Стрпница не найдена'), 'engine');
      View::render(true);
   }

   static function renderError($msg)
   {
      self::setTitle('Ошибка');
      View::css('engine');
      self::loadContent('error', array('ERROR_DESC' => $msg), 'engine');
      self::render(true);
   }
}