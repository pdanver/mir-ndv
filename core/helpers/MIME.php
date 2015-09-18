<?php
/**
 * User: DanVer
 * Date: 22.08.2015
 * Time: 12:15
 */
//класс для работы с данными MIME
class MIME {
    public $data = "";  //данные
    public $type = "";  //тип MIME
    public $encoding = "";  //кодировка

    //функция распарсивает строку на тип, кодировку и данные
    function parseString($data) {
        list($type, $data) = explode(';', $data);
        list($encoding, $data) = explode(',', $data);
        list(,$type) = explode(':',$type);
        $this->type = $type;
        $this->encoding = $encoding;
        if($encoding == "base64") $this->data = base64_decode($data);
    }

    //функция сохранение в файл. имя файла задается без расширения. расширение автоматически добавляется в зависимости
    //от типа MIME. если указано dst_rect и src_rect происходит масштабирование и вырезание
    function saveToFile($file,$dst_rect = null,$src_rect = null) {
        Common::initFunc();

        if(empty($this->data) || empty($this->type)) {
            Common::setLastError(Errors::ARGS_NOT_ENOUGH,Errors::ARGS_NOT_ENOUGH_MSG);
            return false;
        }

        switch(trim($this->type)) {
            case "image/jpeg": $file .= ".jpg"; break;
            case "image/png": $file .= ".png"; break;
            case "image/bmp": $file .= ".bmp"; break;
            case "image/gif": $file .= ".gif"; break;
            case "image/vnd.microsoft.icon": $file .= ".ico"; break;
            case "image/tiff": $file .= ".tif"; break;
            case "image/x-windows-bmp": $file .= ".bmp"; break;
            default: {
                Common::setLastError(Errors::MIME_TYPE_UNDEFINED,Errors::MIME_TYPE_UNDEFINED_MSG);
                return false;
            }
        }
        if(empty($dst_rect) || empty($src_rect) ||
            !isset($src_rect["x1"]) || !isset($src_rect["y1"]) || !isset($dst_rect["x1"]) || !isset($dst_rect["y1"]) ||
            !isset($src_rect["width"]) || !isset($src_rect["height"]) || !isset($dst_rect["width"]) || !isset($dst_rect["height"])) {
            file_put_contents($file,$this->data);
            return true;
        }
        $src = imagecreatefromstring($this->data);
        $res = imagecreatetruecolor($dst_rect["width"],$dst_rect["height"]);
        if(!imagecopyresized($res,$src,$dst_rect["x1"],$dst_rect["y1"],$src_rect["x1"],$src_rect["y1"],
            $dst_rect["width"],$dst_rect["height"],$src_rect["width"],$src_rect["height"])) {
            Common::setLastError(Errors::MIME_RESIZE_IMAGE,Errors::MIME_RESIZE_IMAGE_MSG);
            return false;
        }

        $is_save_file = false;
        switch(trim($this->type)) {
            case "image/jpeg": $is_save_file = imagejpeg($res,$file);
            case "image/png": $is_save_file = imagepng($res,$file);
            case "image/x-windows-bmp": $is_save_file = imagewbmp($res,$file);
            case "image/bmp": $is_save_file = imagewbmp($res,$file);
            case "image/gif": $is_save_file = imagegif($res,$file);
        }
        if(!$is_save_file) {
            Common::setLastError(Errors::MIME_SAVE_FILE,Errors::MIME_SAVE_FILE_MSG);
            return false;
        }

        return $file;
    }

    //функция возвращает размер картинки (если type - это картинка)
    function imageSize() {
        if(empty($this->data) || empty($this->type)) {
            Common::setLastError(Errors::ARGS_NOT_ENOUGH,Errors::ARGS_NOT_ENOUGH_MSG);
            return false;
        }

        switch(trim($this->type)) {
            case "image/jpeg": break;
            case "image/png": break;
            case "image/bmp": break;
            case "image/gif": break;
            case "image/vnd.microsoft.icon": break;
            case "image/tiff": break;
            case "image/x-windows-bmp": break;
            default: {
                Common::setLastError(Errors::MIME_TYPE_INCORRECT,Errors::MIME_TYPE_INCORRECT_MSG);
                return false;
            }
        }
        //
        $src = imagecreatefromstring($this->data);
        return array("width" => imagesx($src),"height" => imagesy($src));
    }

    //функция собирает все в одну строку и возвращает
    function toString() {
        if(empty($this->data) || empty($this->type) || empty($this->encoding)) return "";
        return "data:".$this->type.";".$this->encoding.",".$this->data;
    }
}
