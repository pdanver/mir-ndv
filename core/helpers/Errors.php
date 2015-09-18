<?php
/**
 * User: DanVer
 * Date: 21.08.2015
 * Time: 20:10
 */

class Errors {
    //константы ошибок
    const NOT =                 0x00000000; const NOT_MSG = "Ошибок нет";

    const ARGS_NOT_ENOUGH =     0x00000002; const ARGS_NOT_ENOUGH_MSG = "Недостаточное кол-во аргументов";
    const ARGS_INCORRECT =      0x00000004; const ARGS_INCORRECT_MSG = "Некорректные аргументы";
    const TYPE_UNDEFINED =      0x00000008; const TYPE_UNDEFINED_MSG = "Тип не определен";

    const LOGIN_EMPTY =         0x00100002; const LOGIN_EMPTY_MSG = "Логин не указан";
    const LOGIN_TYPE =          0x00100004; const LOGIN_TYPE_MSG = "Тип логина не определен";
    const LOGIN_VALIDATE =      0x00100008;	const LOGIN_VALIDATE_MSG = "Тип логина не определен";
    const USER_NOT_FOUND =      0x00100010; const USER_NOT_FOUND_MSG = "Пользователь не найден";
    const USER_REGISTERED =     0x00100020;	const USER_REGISTERED_MSG =  "Пользователь уже зарегистрирован";
    const USER_ADD =            0x00100040; const USER_ADD_MSG = "Не удалось добавить пользователя";
    const USER_UPDATE =         0x00100080; const USER_UPDATE_MSG = "Не удалось обновить информацию о пользователе";
    const USER_DELETE =         0x00100100;	const USER_DELETE_MSG = "Не удалось удалить пользователя";
    const USER_STATUS =         0x00100200; const USER_STATUS_MSG = "Некорректный статус пользователя";

    const PWD_EMPTY =           0x00200002; const PWD_EMPTY_MSG = "Пароль не указан";
    const PWD_DIFFERENT =       0x00200004; const PWD_DIFFERENT_MSG = "Пароли не совпадают";
    const PWD_NOT_MATCH =       0x00200008; const PWD_NOT_MATCH_MSG = "Неверный пароль";
    const PWD_NOT_CORRECT =     0x00200010; const PWD_NOT_CORRECT_MSG = "Пароль содержит запрещенные символы";
    const PWD_EASY =            0x00200020; const PWD_EASY_MSG = "Пароль слишком простой";
    const PWD_SHORT =           0x00200040; const PWD_SHORT_MSG = "Пароль слишком короткий (менее 4 символов)";

    const MIME_TYPE_UNDEFINED = 0x00300002; const MIME_TYPE_UNDEFINED_MSG = "Тип MIME не определен";
    const MIME_TYPE_INCORRECT = 0x00300004; const MIME_TYPE_INCORRECT_MSG = "Некорректный тип MIME";
    const MIME_RESIZE_IMAGE =   0x00300008; const MIME_RESIZE_IMAGE_MSG = "Не удалось изменить размеры картинки";
    const MIME_SAVE_FILE =      0x00300010; const MIME_SAVE_FILE_MSG = "Ошибка сохранения файла";
}