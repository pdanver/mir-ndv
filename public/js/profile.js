//функция отправляет запрос на сервер для разлогирования пользователя
function loginOut() {
    windowCloseAll();
    ajaxRequest("/Profile/login_out",null,"POST", function() { updateLoginInfo(); });
}
//функция посылает запрос на сервер для определения, авторизован ли пользователь или нет
function updateLoginInfo() {
    ajaxRequest("/Profile/login_info",null,"POST", function(data) {
        if(data.success && data.success.login) checkLogin(data.success.login);
        else checkLogin(null);
    });
}
//функция обновляет информацию о состоянии авторизации
function checkLogin(user_name) {
    //в зависимости от авторизации, выводим соответствующий блок
    if(user_name) {
        //$("#login-user-text").html("Здравствуйте, " + user_name);
        $("#login-buttons").css("display","none");
        $("#login-user").css("display","block");
        $(".login").css("display","block");
        $(".logout").css("display","none");

        setMenuLogin();
    } else {
        $("#login-user").css("display","none");
        $("#login-buttons").css("display","block");
        $(".login").css("display","none");
        $(".logout").css("display","block");

        setMenuLogout();
    }
}
//функция посылает запрос на сервер для востановления пароля
function restorePwd(login) {
    //showMsg(login);
    if($(login).validateInput(validateLogin)) return;
    ajaxRequest("/Profile/restore_pwd",{ login: $(login).val() },"POST", function(answer) {
        var data = JSON.parse(answer);
        showMsg(data.success.msg); return;
        if(data.error) {
            if(!data.error.field) { showMsg(data.error.msg); return; }
            $(login).parents(".form_input").addClass("invalid").attr("hint",(data.error.msg)?data.error.msg:"");
        }
        if(data.success) showMsg(data.success.msg);
    });
}

$(document).on("click","#login-button_reg", function() {
    $("#login-reg").formPrepare({ form_data: form_reg });
    $("#window-login-reg").windowShow({
        id: "window-reg",
        class: "window-style1",
        title: form_reg.title,
        size: {width: 330, height: 500 },
        pos: { x: "center", y: "center" },
        minimize: false,
        resizable: false,
        movable: false,
        stay_on_top: true,
        buttons: [{
            id: "window-reg-submit",
            title: "Создать аккаунт",
            click: function() {
                submitRegForm();
            } }]
    });
    $("#window-reg #captcha-image").getCaptcha();
    $('#window-reg input[name="company"]').parents(".form_input").css("display","none");
    $('#window-reg input[name="city"]').parents(".form_input").css("display","none");

    $('#window-reg input[name="city"]').parents(".form_input").mousemove(function(e) {
        var pos = $(this).offset();
        var right = pos.left + $(this).width();
        if((e.clientX >= right - 30)&&(e.clientX < right)) $(this).css("cursor","pointer");
        else $(this).css("cursor","default");
    });
    $('#window-reg input[name="city"]').parents(".form_input").mouseleave(function() {
        $(this).css("cursor","default");
    });
    $('#window-reg input[name="city"]').parents(".form_input").click(function(e) {
        var pos = $(this).offset();
        var right = pos.left + $(this).width();
        if((e.clientX >= right - 30)&&(e.clientX < right)) showSelectCityWindow();
    });

    $("#window-reg input[name=login]").focus();
    $('#window-reg input[name="login"]').setOnValidateInputFunc(validateLogin);
    $('#window-reg input[name="pwd"]').setOnValidateInputFunc(validatePwd);
    $('#window-reg input[name="pwd2"]').setOnValidateInputFunc(validatePwd2);
    $('#window-reg input[name="captcha"]').setOnValidateInputFunc(validateCaptcha);

    $('#window-reg #auth').click(function() {
        windowClose("window-reg");
        $("#login-button_auth").trigger("click");
    });
    $('#window-auth input[name="login"]').keypress(function(e) { if(e.keyCode == 13) submitRegForm(); });
    $('#window-auth input[name="pwd"]').keypress(function(e) { if(e.keyCode == 13) submitRegForm(); });
    $('#window-auth input[name="pwd2"]').keypress(function(e) { if(e.keyCode == 13) submitRegForm(); });
    $('#window-auth input[name="captcha"]').keypress(function(e) { if(e.keyCode == 13) submitRegForm(); });
    $('#window-auth input[name="company"]').keypress(function(e) { if(e.keyCode == 13) submitRegForm(); });
    $('#window-auth input[name="city"]').keypress(function(e) { if(e.keyCode == 13) submitRegForm(); });
});

$(document).on("click","#login-button_auth", function() {
    $("#login-auth").formPrepare({ form_data: form_auth });
    $("#window-login-auth").windowShow({
        id: "window-auth",
        class: "window-style1",
        title: form_auth.title,
        size: {width: 330, height: 240 },
        pos: { x: "center", y: 100 },
        minimize: false,
        resizable: false,
        movable: false,
        stay_on_top: true
    });
    var login = getCookie("login");
    if(login && login.length > 0) {
        $("#window-auth input[name=login]").val(login).parents(".form_input").addClass("valid");
        $("#window-auth input[name=pwd]").focus();
    } else $("#window-auth input[name=login]").focus();

     $('#window-auth input[name="login"]').setOnValidateInputFunc(validateLogin);
    $('#window-auth input[name="pwd"]').setOnValidateInputFunc(validatePwd);

    $("#window-auth #window-auth-submit").click(function() { submitAuthForm(); });
    $("#window-auth #window-auth-reg").click(function() {
        windowClose("window-auth");
        $("#login-button_reg").trigger("click");
    });
    $("#window-auth #window-auth-restore_pwd").click(function() {
        restorePwd($('#window-auth input[name="login"]'));
    });
    $('#window-auth input[name="login"]').keypress(function(e) { if(e.keyCode == 13) submitAuthForm(); });
    $('#window-auth input[name="pwd"]').keypress(function(e) { if(e.keyCode == 13) submitAuthForm(); });
});

function showSelectCityWindow() {
    $("#window-select_city").windowShow({
        id: "window-city",
        class: "window-style1",
        title: "Выберите город",
        size: {width: 450, height: 300 },
        pos: { x: "center",y: 100 },
        minimize: false,
        resizable: false,
        movable: false,
        stay_on_top: true,
        buttons: [{
            id: "window-city-ok",
            title: "Ok",
            click: function() {
                var geo_value = $('#window-city input[name="geo_value"]').val();
                $('#window-reg input[name="city"]').parents(".form_input").attr("hint","");
                if(geo_value) {
                    var geo_value = JSON.parse(geo_value);
                    $('#window-reg input[name="city"]').val(geo_value["value"]);
                    $('#window-reg input[name="city"]').parents(".form_input").attr("hint",geo_value["value"]);
                }
                $('#window-reg input[name="city"]').validateInput(validateCity);
                windowClose("window-city");
            } }]
    });
    //showMsg($("#window-city .window-content"));
    $("#window-city .window-content").appendGeo({ street: false });
}

function submitRegForm() {
    $("#window-reg #login-reg").formSubmit({
        url: form_reg.url,
        onSubmit: function(data) {
            setCookieLogin($('#window-reg input[name="login"]').val());
            windowClose("window-reg");
            updateLoginInfo();
            if(data.success && data.success.msg) showMsg(data.success.msg);
        },
        onError: function (error) {
            if(error.field === undefined) showMsg(error.msg,MSG_ERROR);
            $("#window-reg #captcha-image").getCaptcha();
            $('#window-reg input[name="captcha"]').val("").parents(".form_input").removeClass("valid");
        }
    }, true);
}

function submitAuthForm() {
    $("#window-auth #login-auth").formSubmit({
        url: form_auth.url,
        onSubmit: function(data) {
            setCookieLogin($('#window-auth input[name="login"]').val());
            windowClose("window-auth");
            updateLoginInfo();
            //if(data.success && data.success.msg) showMsg(data.success.msg);
        },
        onError: function (error) {
            if(!error.field) showMsg(error.msg,MSG_ERROR);
        }
    }, true);
}

function validateLogin(val) {
    if(val.length == 0) return "Введите email";
    if(!isValidEmail(val)) return "Некорректный email";
    return "";
}
function validatePwd(val) {
    if(val.length == 0) return "Введите пароль";
    return "";
}
function validatePwd2(val) {
    if(val.length == 0) return "Введите пароль";
    if(val.localeCompare($('#window-reg input[name="pwd"]').val()) != 0) return "Пароли не совпадают";
    return "";
}
function validateCaptcha(val) {
    if(val.length == 0) return "Введите код с картинки";
    return "";
}
function validateCompany(val) {
    if(val.length == 0) return "Введите название компании";
    return "";
}
function validateCity(val) {
    if(val.length == 0) return "Укажите город";
    return "";
}

function setCookieLogin(login) {
    var cookie_time = new Date();
    cookie_time.setTime(cookie_time.getTime() + 30*24*60*60*1000);
    document.cookie = "login=" + login + "; expires=" + cookie_time.toUTCString();
}

$(document).on("change",'#window-reg input[name="agent"]',function() {
    if($(this).prop("checked")) {
        $('#window-reg input[name="company"]').parents(".form_input").show();
        $('#window-reg input[name="city"]').parents(".form_input").show();
        windowResize("window-reg",null,600);
        windowMove("window-reg","center","center");
        $('#window-reg input[name="company"]').setOnValidateInputFunc(validateCompany);
        $('#window-reg input[name="city"]').setOnValidateInputFunc(validateCity);
    } else {
        $('#window-reg input[name="company"]').parents(".form_input").hide();
        $('#window-reg input[name="city"]').parents(".form_input").hide();
        windowResize("window-reg",null,500);
        windowMove("window-reg","center","center");
        $('#window-reg input[name="company"]').setOnValidateInputFunc(null);
        $('#window-reg input[name="city"]').setOnValidateInputFunc(null);
    }
});

function initProfileEdit() {
    //настраиваем и устанавливаем datepicker для выбора дат (используется плагин jquery-ui)
    /*$("#profile_edit-customer").showFormDialog(form_edit_customer,{
        width: "auto"
    });*/

    //производим инициализацию телефонов
    initPhones();
    //устанавливаем обработчик на нажатие кнопки добавления телефона
    /*$(".phone").find(".add_phone").click(function() {

    });*/

    //$('#window-profile_edit input[name="photo"]').click(function(e) { e.preventDefault(); return false;});

    function onClickPhoto() {
        $('#window-profile_edit input[name="photo"]').parents(".form_input-shell").unbind("click",onClickPhoto);
        $('#window-profile_edit input[name="photo"]').click();
        $('#window-profile_edit input[name="photo"]').parents(".form_input-shell").bind("click",onClickPhoto);
    }
    $('#window-profile_edit input[name="photo"]').parents(".form_input-shell").bind("click",onClickPhoto);


    //вешаем обработчик на выбор даты и времени рождения
    $.datepicker.setDefaults($.datepicker.regional['ru']);  //устанавливаем русский язык
    var tmp = $("input[name='birthday']").val();
    $("input[name='birthday']").datepicker({
        changeMonth: true,  //с возможностью выбора месяца
        changeYear: true,  //с возможностью выбора года
        dateFormat: 'dd.mm.yy T',
        defaultDate: StrToDate(tmp),
        yearRange: "-120:-10"}).timePicker();  //и вызываем часики
    $("input[name='birthday']").datepicker('setDate', StrToDate(tmp));
    $("input[name='birthday']").val(tmp);

    if($("input[name=photo]").attr("file_name")) $("#user_photo-preview").attr("src",$("input[name=photo]").attr("file_name"));

    //при выборе файла картинки
    $('#window-profile_edit input[name="photo"]').change(function() {
        var input = $(this)[0];
        if (input.files && input.files[0] ) { //проверяем, что выбрали файла
            if ( input.files[0].type.match('image.*') ) {  //убеждаемся, что это картинка
                //чистим все img-ы
                //$('#window-profile_edit #user_photo-preview').attr("src","");
                //$('#photo_cutter-photo').attr("src","");

                //считываем выбранную картинку
                var reader = new FileReader();

                reader.onload = function(e) {
                    $("#photo_cutter-window").windowShow({
                        id: "window-photo_cutter",
                        class: "window-style2",
                        size: { width: 600, height: 500 },
                        title: "Редактор",
                        stay_on_top: true
                    });

                    $('#window-photo_cutter #photo_cutter-photo').attr('src', e.target.result);

                    var max_width = $('#window-photo_cutter #photo_cutter-photo').width() + 50,
                        max_height = $('#window-photo_cutter #photo_cutter-photo').height() + 140;
                    if(max_width > document.body.clientWidth) max_width = document.body.clientWidth;
                    if(max_height > document.body.clientHeight) max_height = document.body.clientHeight;
                    //showMsg(max_width + "<br/>" + max_height);
                    windowResize("window-photo_cutter",max_width,max_height);

                    //подключаем к картинки плагин выделения области
                    $('#window-photo_cutter #photo_cutter-photo').imgAreaSelect({
                        aspectRatio: '1:1',
                        handles: true
                    });

                    //вызываем диалоговое окно для выделения фрагмента картинки
                    /*$("#photo_cutter").dialog({
                        modal: true, title: 'Редактирование картинки', width: "auto", show: "blind", hide: true,
                        position: {
                            my: "center top", at: "center top", of: window },
                        beforeClose: function() {
                            $('#photo_cutter-photo').imgAreaSelect({
                                remove: true });
                        },
                        buttons: {
                            "Ok": function() { //если все ok, то
                                //узнаем область выделения
                                var img = $('#photo_cutter-photo');
                                var imgSelection = img.imgAreaSelect({
                                    instance: true}).getSelection();
                                //записываем ее в скрытый input-для передачи на сервер
                                var data = {
                                    image: e.target.result,
                                    selection: imgSelection
                                };
                                $("input[name='photo_data'").val(JSON.stringify(data));
                                //тепереь вычисляем масштаб и смещение превьюшки для умещения выбранной области
                                var scaleX = $('#user_photo-preview-cnt').width() / imgSelection.width;
                                var scaleY = $('#user_photo-preview-cnt').height() / imgSelection.height;
                                $('#user_photo-preview').css({
                                    width: Math.round(scaleX * img.width()) + 'px',
                                    height: Math.round(scaleY * img.height()) + 'px',
                                    marginLeft: '-' + Math.round(scaleX * imgSelection.x1) + 'px',
                                    marginTop: '-' + Math.round(scaleY * imgSelection.y1) + 'px'
                                });
                                //и устанавливаем картинку для превьюшки
                                $('#user_photo-preview').attr("src",$('#photo_cutter-photo').attr("src"));

                                //
                                $(this).dialog("close");
                            },
                            "Отмена": function() {
                                $('#user_photo-preview').attr("src","");
                                $("input[name='photo']").val("");
                                $(this).dialog("close"); }
                        }
                    });*/

                }
                reader.readAsDataURL(input.files[0]);

            } else showMsg('Извините, но выбранный файл не может быть использован в качестве фотографии!');
        };// else showMsg('Файл не выбран');
    });
}

$(document).ready(function() {
    //подключаем события на кнокпи авторизации и регистрации
    $("#login-button_out").click(function() { loginOut(); });
    updateLoginInfo();

    //$("#profile_edit-customer").formPrepare({ form_data: form_edit_customer });

    $("#show_window").click(function() {
        $("#profile_edit-customer-window").windowShow({
            title: form_edit_customer.title,
            id: "window-profile_edit",
            class: "window-style2",
            size: {width: 800,height: 620},
            min_width: 400,
            min_height: 200
        });
        tabsInit();
        initProfileEdit();
    });
});