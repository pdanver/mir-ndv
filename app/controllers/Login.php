<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 27.05.15
 * Time: 11:57
 */

class Login extends Controller
{
    function __construct()
    {
        if(SessionManager::isAuthorized())
        {
            header('Location:/main');
            die;
        }
    }

    function index()
    {
        View::setTitle('Авторизация');
        View::css('engine');
        View::css();
        View::loadContent('loginForm');
        View::render();
    }

    function auth()
    {
        if(!isset($_POST['login']) || empty($_POST['login']) || !isset($_POST['pwd']) || empty($_POST['pwd']))
            throw new Exception('Нужны параметры');

        var_dump($_POST);
    }

    function admin()
    {
        View::setTitle('Админка авторизации');
        View::loadContent('loginAdmin', null, 'engine');
    }

    function statistic()
    {
        $stat = array('NAME' => 'Авторизация');
        $stat['STATISTIC'] = 'Пользователей в системе: '.Users::getUsersCount();
        $stat['STATISTIC'] .= ' Администраторов в системе: '.Users::getAdminUsersCount();

        return View::load(array('engine', 'statisticNode'), $stat);
    }
}