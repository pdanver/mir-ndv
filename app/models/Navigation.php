<?php
/**
 * Created by IntelliJ IDEA.
 * User: takeru
 * Date: 18.07.15
 * Time: 20:20
 */

class Navigation
{
//   private static function menuTableName()
//   {
//      return Settings::$DB_PREFIX.'_menu';
//   }

//   private static function menuItemsTableName()
//   {
//      return Settings::$DB_PREFIX.'_menu_items';
//   }

   private static function prepareWhere($id)
   {
//      if(is_string($id))
//         $where = '`name` LIKE \''.MySQLi::checkString($id).'\'';
//      else
//         if(is_numeric($id))
//            $where = '`id` = '.intval($id);
//         else
//            if(is_array($id))
//            {
//               $where = '`'.(is_numeric($id[0])?'id':'name').'` IN(';
//               foreach($id as $val)
//                  $where .= MySQLi::checkString($val).',';
//               $where = substr_replace($where,')',-1);
//            }
//
//      return $where;

      if(!is_array($id))
         Database::DB()->where('name', $id);
      else
         Database::DB()->where(is_numeric($id[0])?'id':'name', $id, 'IN');
   }

   static function addMenu($name, $alias, $publish = true)
   {
//      MySQLi::insert(self::menuTableName(), array('name' => $name, 'alias' => $alias, 'published' => $publish));
      Database::DB()->insert('menu', array('name' => $name, 'alias' => $alias, 'published' => $publish));
   }

   static function getMenu($id = null, $all = false)
   {
//      $result = Database::DB()->select('menu', '*', self::prepareWhere($id).($all?' AND `published` = 1':null));
      Database::DB()->select('menu', '*');
      self::prepareWhere($id);
      Database::DB()->exec();

      return Database::get();
   }

   static function updateMenu($name, $publish, $id = null)
   {
//      MySQLi::update(self::menuTableName(), array('name' => $name, 'published' => $publish), self::prepareWhere($id));
      Database::DB()->update('menu', array('name' => $name, 'published' => $publish));
      self::prepareWhere($id);
      Database::DB()->exec();
   }

   static function deleteMenu($id = null)
   {
      self::deleteMenuItems($id);
//      MySQLi::delete(self::menuTableName(), self::prepareWhere($id));
      Database::DB()->delete('menu');
      self::prepareWhere($id);
      Database::DB()->exec();
   }

   static function addMenuItem($menu, $link, $alias, $group)
   {
      $menuId = self::getMenu($menu);

//      MySQLi::insert(self::menuItemsTableName(),
//         array('menuId' => $menuId[0]['id'],
//            'link' => $link,
//            'alias' => $alias,
//            'groupId' => 0));
      Database::DB()->insert('menu_items', array('menuId' => $menuId[0]['id'], 'link' => $link, 'alias' => $alias,
         'roleId' => 0));
   }

   static function getMenuItems($menu)
   {
      if(!is_numeric($menu))
         $menu = self::getMenu($menu)[0]['id'];

//      $result = MySQLi::select(self::menuItemsTableName(), '*', '`menuId` = '.$menu);

      Database::DB()->select('menu_items')->where('menuId', $menu)->exec();
      return Database::get();
   }

   static function deleteMenuItems($menu, $items = null)
   {
//      $where = '`menuId` IN(';
//      $menu = self::getMenu($menu);
//
//      foreach($menu as $val)
//         $where .= $val['id'].',';
//      $where = substr_replace($where,')',-1);
//
//      if($items)
//      {
//         $where .= ' AND `id` IN(';
//         if(is_array($items))
//         {
//            foreach($items as $val)
//               $where .= intval($val).',';
//            $where = substr_replace($where,')',-1);
//         }
//         else
//            $where .= intval($items).')';
//      }
//
////      MySQLi::delete(self::menuItemsTableName(), $where);
//      Database::DB()->delete('menu_items')->where()->exec();
   }

   static function loadMenu($name)
   {
      $arr = array();
      foreach(self::getMenuItems($name) as $val)
         if($val['roleId'] == 0)
            $arr[] = $val;

      return $arr;
   }

   static function parseURI($uri)
   {
      $uri = substr($uri,1);

      $result = MySQLi::query('SELECT B.`published`, A.`link`, A.`groupId`
      FROM `'.self::menuItemsTableName().'` A, `'.self::menuTableName().'` B
      WHERE A.`menuId` = B.`id` AND A.`alias` LIKE \''.MySQLi::checkString($uri).'\'');

      $result = $result->fetch_assoc();

//      if(!$result || !$result['published'] || $result['groupId'] != 0)
//         throw new Exception('permission denied');

      return $result['link'];
   }
}

//INSERT INTO `p0oth_menu_types` (`id`, `menutype`, `title`, `description`) VALUES
//(1, 'mainmenu', 'Главное меню', 'Главное меню сайта');
//INSERT INTO `p0oth_menu`
//(`id`,
//`menutype`,
//`title`,
//`alias`,
//`note`,
//`path`,
//`link`,
//`type`,
//`published`,
//`parent_id`,
//`level`,
//`component_id`,
//`checked_out`,
//`checked_out_time`,
//`browserNav`,
//`access`,
//`img`,
//`template_style_id`,
//`params`,
//`lft`, `rgt`, `home`, `language`, `client_id`) VALUES
//(132, 'mainmenu', 'Цены', 'tseny', '', 'tseny', 'index.php?option=com_content&view=article&id=15', 'component', 1, 1, 1, 22, 0, '0000-00-00 00:00:00', 0, 1, '', 0, '{"show_title":"0","link_titles":"","show_intro":"0","info_block_position":"","show_category":"0","link_category":"","show_parent_category":"0","link_parent_category":"","show_author":"0","link_author":"","show_create_date":"0","show_modify_date":"0","show_publish_date":"0","show_item_navigation":"0","show_vote":"0","show_icons":"0","show_print_icon":"0","show_email_icon":"0","show_hits":"0","show_tags":"0","show_noauth":"","urls_position":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 43, 44, 0, '*', 0);
