/**
 * Created by DanVer on 15.09.2015.
 */

var form_edit_customer = {
    url: "/Profile/edit/customer",
    inputs: {
        user_surname: { type: "text", name: "surname", attrs: { value: "", tabindex : "1", placeholder: "Фамилия" }, title: "Фамилия" },
        user_name: { type: "text", name: "name", attrs: { value: "", tabindex : "2", placeholder: "Имя"}, title: "Имя"},
        user_old_name: { type: "text", name: "second_name", attrs: { value: "", tabindex : "3", placeholder: "Отчество"}, title: "Отчество" },
        user_test_select: { type: "select", name: "test_select",
            attrs: { value: "", placeholder: "test"},
            items: ["one","two","three"],
            title: "Test" },
        user_sex: { type: "radio", name: "sex", title: "Пол: ", value: "'.$user->sex.'",
            items: [ { attrs: { value: "1", tabindex : "4"}, title: "Муж" }, { attrs: { value: "2", tabindex : "5"}, title: "Жен" }]
        },
        user_birthday: { type: "text", name: "birthday", attrs: { value: "'.$user->birthday.'", tabindex : "6", placeholder: "Дата рождения"}, title: "Дата рождения" },
        user_photo: { type: "file", name: "photo", attrs: { tabindex : "7", title: "Выберите картинку", accept: "image/bmp,image/jpeg,image/png,image/gif", placeholder: "Фото"}, title: "Выберите фото" },
        user_photo_data: { type: "hidden", name: "photo_data" },

        user_phones: { type: "phone", name: "phone", title: "Телефоны", types: "mobile,Мобильный;home,Домашний;work,Рабочий", phones: ['.$phones_str.'] },
        user_jabber: { type: "email", name: "jabber", attrs: { value: "'.$user->jabber.'", placeholder: "Jabber" }, title: "Jabber" },
        user_skype: { type: "text", name: "skype", attrs: { value: "'.$user->skype.'", placeholder: "Skype"}, title: "Skype"},

        user_old_pwd: { type: "password", name: "old_pwd", attrs: { value: "", placeholder: "Пароль"}, title: "Текущий пароль"},
        user_pwd1: { type: "password", name: "pwd1", attrs: { value: "", placeholder: "Пароль"}, title: "Новый пароль"},
        user_pwd2: { type: "password", name: "pwd2", attrs: { value: "", placeholder: "Пароль"}, title: "Подтверждение"},

        user_country: {
            type: "select",
            name: "country",
            title: "Страна",
            options: [ {attrs: { value: "0"}, title: "-выберите страну-" },
                {attrs: { value: "1"}, title: "Россия" }]
        },

        user_region: {
            type: "select",
            name: "region",
            title: "Регион",
            options: [ {attrs: { value: "0"}, title: "-выберите регион-" } ]
        }
    },
    title: "Карточка пользователя",
    button_ok_title: "Сохранить",
    button_cancel_title: "Отмена"
};

$(document).on("click","#test-btn",function() {
    $("#window-test").formPrepare({form_data: form_edit_customer});
    $("#window-test").windowShowStyle3({
        id: "test-window",
        title: "Test",
        size: { width: 850, height: 400}
        //min_width: 1050
    });
    //$("#test-window .form_input").addClass("valid");
});

$(document).on("click",".window-style3 .close-btn",function() {
    windowClose($(this).parents(".window-shell").attr("id"));
});
$(document).on("click",".window-style3 .minimize-btn",function() {
    windowMinimize($(this).parents(".window-shell").attr("id"));
});

$(document).on("click",".window-style4 .close-btn",function() {
    windowClose($(this).parents(".window-shell").attr("id"));
});

function tabsInitGarbage() {
    //настраиваем вклдаки
    $(".window-style4 .tabs-shell").each(function() {
        $(this).find(".tabs-content .tab-content").hide();

        var tabs = $(this).find(".tabs li a");
        var width = Math.floor(96/tabs.length);
        tabs.each(function(i,item) {
            $(item).css("z-index",tabs.length - i);
            $(item).css("width",width*(i+1) + "%");
            $(item).css("padding-left",width*(i) + "%");
        });

        $(this).find(".tabs li:first").attr("id","current"); // Какой таб показать первым
        $(this).find(".tabs-content div:first").fadeIn(); // Показ первого контента таба
    });

    $('.window-style4 .tabs a').click(function(e) {
        e.preventDefault();
        $(this).parents(".tabs-shell").find(".tabs-content .tab-content").hide(); //Скрыть всё содержимое
        $(this).parents(".tabs-shell").find(".tabs li").attr("id",""); //Сброс идентификаторов
        $(this).parent().attr("id","current"); // Активация идентификаторов
        $(this).parents(".tabs-shell").find('#' + $(this).attr('title')).fadeIn(); // Показать содержимое текущей вкладки
    });
}

$(document).on("click","#top_r_menu-btn2",function() {
    var width = document.body.clientWidth;
    var height = $(document).height() - 150;

    $("#window-profile").windowShow({
        id: "profile-window",
        class: "window-style4",
        size: { width: width, height: height},
        pos: { x: "center", y: 150},
        min_width: 910
    });
    //$('.window-style4 .garbage-title').css("cursor","move").bind("mousedown",onWindowTitleMouseDown);
    tabsInitGarbage();
});

$(document).on("click","#profile-window .user-add",function() {
    $("#window-profile_edit").windowShowStyle3({
        id: "profile_edit-window",
        //class: "window-style3",
        title: "Карточка нового пользователя",
        size: { width: 1150, height: 500},
        pos: { x: "center", y: 100 },
        min_width: 1150
    });
});

function windowShowAddAd() {
    $("#window-add_ad").windowShow({
        title: "Форма добавления нового объявления",
        id: "add_ad-window",
        class: "window-style2",
        size: { width: 900, height: $(document).height() - 70},
        pos: { x: "center", y: 50 },
        min_width: 900
    });
}

function windowShowClaims() {
    var width = document.body.clientWidth;
    var height = $(document).height() - 150;

    $("#window-claims").windowShowStyle3({
        title: "Заявки",
        id: "claims-window",
        size: { width: width, height: height},
        pos: { x: "center", y: 150 },
        resizable: false,
        movable: false,
        min_width: width
    });
}

function windowShowClients() {
    var width = document.body.clientWidth;
    var height = $(document).height() - 150;

    $("#window-clients").windowShowStyle3({
        title: "Клиенты",
        id: "clients-window",
        size: { width: width, height: height},
        pos: { x: "center", y: 150 },
        resizable: false,
        movable: false,
        min_width: width
    });
}

function windowShowObjects() {
    var width = document.body.clientWidth;
    var height = $(document).height() - 150;

    $("#window-objects").windowShowStyle3({
        title: "Объекты",
        id: "objects-window",
        size: { width: width, height: height},
        pos: { x: "center", y: 150 },
        resizable: false,
        movable: false,
        min_width: width
    });
}

$(document).on("click","#menu-add_list-li1",function() {
    $("#window-client_add").windowShowStyle3({
        id: "client_add-window",
        title: "Добавить клиента",
        size: { width: 1100, height: 500},
        pos: { x: "center", y: 100 },
        min_width: 1100
    });
});


$(document).on("click","#menu-add_list-li2",function() {
    $("#window-add_claims").windowShowStyle3({
        id: "add_claims-window",
        title: "Добавление заявки",
        size: { width: 1100, height: 500},
        pos: { x: "center", y: 100 },
        min_width: 1100
    });
});

$(document).on("click","#menu-add_list-li3",function() {
    $("#window-object-add").windowShowStyle3({
        id: "client_add-window",
        title: "Добавление объекта",
        size: { width: 1100, height: 500},
        pos: { x: "center", y: 100 },
        min_width: 1100
    });
    $("#client_add-window .next-btn").click(function() {
        var images = $(this).siblings("img");
        for(var i = 0;i < images.length;i++) {
            if($(images[i]).is(":visible")) {
               images.hide();
               if(i == images.length-1) images.first().show();
               else $(images[i+1]).show();
               return;
           }
        }
        //images.find("img")
    });
});

$(document).on("click","#top_r_menu-btn4",function() {
    loginOut();
});

function setMenuLogin() {
    var menu = $("#navigationMenu");
    menu.empty();
    menu.append('<li><a class="home" id="navigationMenu-login-li1" href="javascript:void(0)"><span>Рабочий стол</span></a></li>' +
        '<li><a class="home" id="navigationMenu-login-li2" href="javascript:void(0)"><span>Клиенты</span></a></li>' +
        '<li><a class="home" id="navigationMenu-login-li3" href="javascript:void(0)"><span>Объекты</span></a></li>' +
        '<li><a class="home" id="navigationMenu-login-li4" href="javascript:void(0)"><span>Заявки</span></a></li>');

    $("#navigationMenu-login-li2").click(function() {
        windowShowClients();
    });
    $("#navigationMenu-login-li3").click(function() {
        windowShowObjects();
    });
    $("#navigationMenu-login-li4").click(function() {
        windowShowClaims();
    });
}

function setMenuLogout() {
    var menu = $("#navigationMenu");
    menu.empty();
    menu.append('<li><a class="home" id="navigationMenu-logout-li1" href="javascript:void(0)"><span>Главная</span></a></li>' +
        '<li><a class="home" id="navigationMenu-logout-li2" href="javascript:void(0)"><span>Подать объявление</span></a></li>' +
        '<li><a class="home" id="navigationMenu-logout-li3" href="javascript:void(0)"><span>Регистрация</span></a></li>' +
        '<li><a class="home" id="navigationMenu-logout-li4" href="javascript:void(0)"><span>Контакты</span></a></li>');

    $("#navigationMenu-logout-li2").click(function() {
        windowShowAddAd();
    });
    $("#navigationMenu-logout-li3").click(function() { $("#login-button_reg").trigger("click"); });
}

$(document).on("click","#top_l_menu-btn4",function(e) {
    $("#menu-add_list").fadeIn();
    return false;
});
$(document).click(function() { $("#menu-add_list").fadeOut(); });

$(document).ready(function() {
    $("#menu-add_list").fadeOut();
});

$(window).resize(function() {
    var width = document.body.clientWidth;
    var height = $(document).height() - 150;
    windowResize("profile-window",width,height);
    windowMove("profile-window","center",150);
    windowResize("clients-window",width,height);
    windowMove("clients-window","center",150);
});