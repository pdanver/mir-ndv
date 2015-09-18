<?php
/**
 * User: DanVer
 * Date: 13.08.15
 * Time: 15:52
 */

//если функция is_assoc не определена, определяем ее сами
if(!function_exists("is_assoc")) {
    function is_assoc( $a ) {
        return is_array( $a ) && ( count( $a ) !== array_reduce( array_keys( $a ), create_function( '$a, $b', 'return ($b === $a ? $a + 1 : 0);' ), 0 ) );
    }
}

//функция для инициализации параметров по умолчанию функций вида func($options)
function initFuncDefaultParams($options,array $params) {
    foreach($params as $key => $param)
        if(empty($options[$key])) $options[$key] = $param;
    return $options;
}

//функция выводит в документ данные из массива как переменные java script
function echoJS($js_script,$return_as_string = false) {
    $str = '<script type="application/javascript">'.$js_script.'</script>';
    if($return_as_string) return $str;
    echo $str;
}

//функция выводит в документ данные из массива как переменные java script
function echoJSVars(array $vars,$return_as_string = false) {
    $str = "";
    foreach($vars as $key => $val)
        $str .= 'var '.$key.' = '.$val.';';
    if($return_as_string) return echoJS($str,true);
    echoJS($str,false);
}

function echoVar($var,$title = "",$return_to_string = false) {
    $str = "<p>".$title;
    if(!empty($title)) $str .= "<br/>";
    $str .= print_r($var,true)."</p>";
    if($return_to_string) return $str;
    echo $str;
}