<?php

/**
 * Class StaticContent
 */
class StaticContent
{
   static function getStaticPage($v)
   {
      Database::DB()->select('static_pages')->where('title', $v)->exec();

      return Database::get()[0];
	}
}