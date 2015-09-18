<?php

/**
 * Class StaticPages
 */

class StaticPage extends Controller
{
   function index()
   {
      $var = func_get_args()[0];
      if(!empty($var))
      {
         $r =  StaticContent::getStaticPage($var);

         return $r['text'];
      }
      else
         View::render404();
   }
   
   function admin() {}

   function statistic() { }
}