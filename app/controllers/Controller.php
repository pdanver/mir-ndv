<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 27.05.15
 * Time: 10:23
 */

abstract class Controller
{
    static $lang;

    /**
     * Конструктор класса, по умолчанию реализующий поведение страниц, для открытия которых необходима авторизация
     * Для обхода запрета нужно реализовать конструктор с одним аргументом в контроллере
     *
     * @param bool $admin параметр используется в разделе администрирования, для обхода авторизации
     */

    function __construct($admin = false)
    {
//        if(!SessionManager::isAuthorized() && !$admin)
//        {
//            header('Location:/login');
//            die;
//        }
    }

    /**
     * Метод перехвата обращений к несуществующим страницам
     *
     * @param $methodName
     * @param $args
     */

    function __call($methodName, $args)
    {
        if(!method_exists($this, $methodName))
            View::render404();
        $this ->$methodName($args);
    }

    /**
     * Метод отображения главной страницы контроллера
     *
     * @return mixed
     */

    abstract function index();

    /**
     * Метод отображения страницы администрирования.
     * Страница реализует управление только тем модумем, который реализует контроллер
     *
     * @param array $args массив маркеров и элементов навигации из контроллера администрирования
     * @return mixed
     */

    abstract function admin();

    /**
     * Метод отображения страницы статистики, работает по аналогии с администрированием,
     * но не должен иметь элементов управления
     *
     * @param $args
     * @return mixed
     */

    abstract function statistic();
}