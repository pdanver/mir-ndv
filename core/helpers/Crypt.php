<?php
/**
 * User: DanVer
 * Date: 13.08.15
 * Time: 11:20
 */
//класс для работы с шифрованием и хешированием
class Crypt {
    //типы алгоритмов шифрования
    const CRYPT_HASH_SHA512 = 1;    //алгоритм хеширования расчитанный по sha-512
    const CRYPT_HASH_BCRYPT1 = 2;	//алгоритм хеширования расчитанный по bcrypt c 30-знаковой солью
    const CRYPT_HASH_DEFAULT = 2;	//алгоритм хеширования по умолчанию по умолчанию

    //функция создает hash пароля pwd по алгоритму $algo. тип алгоритма впоследствии добавляется к вычисленному хешу.
    //в алгоритмах с солью, соль так же смешивается с вычеслинным хешем (обычно чередуясь с хешем).
    static function password_hash($pwd,$algo = self::CRYPT_HASH_DEFAULT) {
        //это строка добавится к вычисленному хешу для идентификации алгоритма хеширования
        $algo_str = "$".$algo."$";
        //в зависимости от алгоритма...
        switch($algo) {
            case(self::CRYPT_HASH_SHA512):  //если это sha-512, то ничего сложного нет
                return $algo_str.hash('sha512', $pwd);
            case(self::CRYPT_HASH_BCRYPT1):  //если это bcrypt (1-ый)
                //заводим набор доступных для генерации соли символов, определяем параметры соли и т.д.
                $Allowed_Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                $Chars_Len = 61;
                $Salt_Length = 22;
                $salt = "";
                //генерируем соль
                for ($i = 0; $i < $Salt_Length; $i++) {
                    $salt.= $Allowed_Chars[mt_rand(0, $Chars_Len) ];
                }
                //хешируем пароль с этой солью
                $crypt_pwd = crypt($pwd,'$2y$12$'.$salt.'$');
                //после чего смешиваем тип алгоритма, вычисленный хеш и соль в одной строке
                $blend_pwd = $algo_str.substr($crypt_pwd,0,8);
                for($i=0;$i < $Salt_Length;$i++) {
                    $blend_pwd .= $crypt_pwd[$i + 8].$salt[$i];
                }
                $blend_pwd .= substr($crypt_pwd,30);

                return $blend_pwd; //возвращаем результат
        }
        return "";
    }

    //функция сравнивает хеши $str1 и $str2
    static function hash_compare($str1,$str2) {
        if(strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
            return !$ret;
        }
    }

    //функия проверяет пароль с его хэшем
    static function password_verify($pwd,$hash) {
        //ищем сперва вхождение в хеше подстроки с типом алгоритма хеширования
        $matches = null;
        if(preg_match_all('/^[$][0-9][0-9]*[$]/',$hash,$matches) == 0) return false;
        $algo = substr($matches[0][0],1,strlen($matches[0][0])-2);
        //в зависимости от типа алгоритма...
        switch($algo) {
            case(self::CRYPT_HASH_SHA512): {  //если это sha-512, то все просто
                return self::hash_compare(self::password_hash($pwd,self::CRYPT_SHA512),$hash);
            }
            case(self::CRYPT_HASH_BCRYPT1): {  //иначе
                //распасриваем hash на соль, чистый хем и тип алгоритма
                $blend_pwd = substr($hash,strlen($matches[0][0]));
                $salt = "";
                $crypt_pwd = substr($blend_pwd,0,8);
                $blend_pwd = substr($blend_pwd,8);
                for($i=0;$i < 22;$i++) {
                    $crypt_pwd .= $blend_pwd[$i*2];
                    $salt .= $blend_pwd[$i*2 + 1];
                }
                $crypt_pwd .= substr($blend_pwd,44);
                //вычисляем по паролю и определенной из хеша соли новый хеш
                $crypt_pwd_new = crypt($pwd,'$2y$12$'.$salt.'$');

                //сравниваем что получилось и возвращаем результат
                return self::hash_compare($crypt_pwd_new,$crypt_pwd);
            }
        }
    }

    //функция генерации пароля. $pwd_length - длина пароля
    static public function password_generate($pwd_length) {
        $letters = array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','2','3','4','5','6','7','9');
        $pwd = "";
        for($i = 0;$i < $pwd_length;$i++) $pwd .= $letters[rand(0,sizeof($letters)-1)];
        return $pwd;
    }
}