<?php
/**
 * Created by IntelliJ IDEA.
 * User: takeru
 * Date: 24.07.15
 * Time: 3:14
 */

if(!function_exists('is_assoc'))
{
   /**
    * is_assoc
    *
    * Проверяет, является ли массив ассоциативным
    *
    * @param	array
    * @return	true/false
    */
   function is_assoc($var)
   {
      return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
   }
}