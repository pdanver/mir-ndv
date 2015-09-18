/**
 * Created by DanVer on 14.08.15.
 * Общие скрипты
 */

//хост сайта
var host = "";  //"http://192.168.1.66";
//вкл/выкл кроссдоменые запросы
var crossDomainRequest = false;

//функция выполняет ajax запрос по ссылке url, передавая данные методом type. данные передаются в виде объекта
//через переменную data (в итоге из data заполнится массив GET или POST, в зависимости от type).
//onSuccess - функция обработки положительного ответа сервера. onError - ф-ия обработки ошибки
function ajaxRequest(url, data, type ,onSuccess, onError) {
    //var url = "http://" + window.location.hostname + "/profile/post/registrationvalidation";
    //добавляем флаг к data обзначающий, что идет ajax запрос. он будет передан через GET или POST (зависити от type)
    if(data == null) data = {
    };
    data.request_type = 1;
    //посылаем запрос
    $.ajax({
        url: host + url,
        type: type,
        crossDomain: crossDomainRequest,
        data: data,
        dataType: "json",
        success: function(answer) {  //по возвращению результата от сервера
            if (answer.error != null) { //проверяем его на ошибки
                showMsg(answer.error,MSG_ERROR);
                if(onError) onError(answer.error);
                return;
            }
            //если все нормально, вызываем callback фунцию
            if (onSuccess) onSuccess(answer.answer);
        },
        error: function(xhRequest, errorText, thrownError) { //в случае ошибки выводим её
            var error = 'Не удалось передать запрос серверу (url: ' + url + ')';
            showMsg(error,MSG_ERROR);
            if(onError) onError(error);
        },
        //перед отправкой показываем прелойдер, а после скрываем
        beforeSend: function() { var preloader = $(".preloader"); if(preloader) $(preloader).show(); },
        complete: function() {  var preloader = $(".preloader"); if(preloader) $(preloader).hide(); }
    });
}

//функция посылает запрос на сервер для генерации капчи и вставляет ее в img[id="captcha-image"]
$.fn.getCaptcha = function() {
    var image = $(this);
    ajaxRequest("/Profile/captcha",null,"POST", function(data) { image.attr('src','data:image/gif;base64,'+data); });
}

const MSG_INFO = 1;
const MSG_SUCCESS = 2;
const MSG_ERROR = 3;
const MSG_WARNING = 4;
const MSG_VALIDATION = 5;

//функция для вывода сообщения
function showMsg(text,msg_type,buttons) {
    //если в тексте объект, то преобразуем его в строку
    //if(text instanceof Object) text = objToString(text);
    //проверяем тип сообщения... если с кнопками, то выводим простое сообщение
    if(!buttons) {
        //выодим сообщение в блоке message (если он есть)
        var message = $("#message");
        if(message) {
            $(message).slideUp("fast",function() { //скрываем сперва предыдущее сообщение
                $(message).find(".message-text").html(text); //выставляем текст
                //удаляем старые классы типа сообщения и устанавливаем новый
                $(message).removeClass("msg_info")
                    .removeClass("msg_success")
                    .removeClass("msg_error")
                    .removeClass("msg_warning")
                    .removeClass("msg_validation");
                if(msg_type) switch(msg_type) {
                    case MSG_INFO: $(message).addClass("msg_info"); break;
                    case MSG_SUCCESS: $(message).addClass("msg_success"); break;
                    case MSG_ERROR: $(message).addClass("msg_error"); break;
                    case MSG_WARNING: $(message).addClass("msg_warning"); break;
                    case MSG_VALIDATION: $(message).addClass("msg_validation"); break;
                }
                //и вешаем обработчик кнопку на закрытие сообщения
                $(message).find(".message-close").click(function() { $(message).slideUp(); });
                $(message).slideDown(); //показываем сообщение
            });
        } else alert(text);
    }
    else return confirm(text); //иначе выводим конфирм-месседж
}

//функция преобразует объект в строку
function toString(obj,recursion) {
    var str = '';
    for (var p in obj) {
        if (obj.hasOwnProperty(p)) {
            str += p + ': ';
            if(recursion && (obj[p] instanceof Object)) str += '{<br/>' + toString(obj,recursion) + '}<br/>';
            else str += obj[p] + '<br/>';
        }
    }
    return str;
}

/*//функция валидации формы данные передаются объектом options: form - форма, url - адрес посыла запросов, is_submit произвести окончательный submit после
//валидации, onSuccess - дополнительные действия при успешном ответе сервера, onError -аналогично onSuccess
function validateForm(options,is_submit){
    if(!options.url) return;
    //заполняем данные для запроса
    var send_data = {
        submit: is_submit,
        validate_fields: [],
        required_fields: []
    };
    //считываем все значения input-ов
    $(options.form).find("input").each(function() {
        if($(this).attr("validate")) send_data["validate_fields"].push($(this).attr("name"));
        if($(this).attr("required")) send_data["required_fields"].push($(this).attr("name"));
        //в зависимости от инпута, соответствующие действия
        var input_type = $(this).attr("type");
        if(input_type.localeCompare("radio") == 0) {
            if($(this).prop("checked")) send_data[$(this).attr("name")] = $(this).val();
        } else if(input_type.localeCompare("phone") == 0) {
            if(send_data[$(this).attr("name")] == null) send_data[$(this).attr("name")] = [];
            if($(this).val().length > 0)
                send_data[$(this).attr("name")].push($(this).parent().find("select").val() + ',' + $(this).val());
        } else {
            send_data[$(this).attr("name")] = $(this).val();
        }
    });
    //пришлось проверку на required вынести отдельно
    //$(options.form).find("input")

    //послыаем запрос
    ajaxRequest(options.url,send_data,"POST",
        function(answer) {  //при успешном ответе сервера
            //настраиваем визуальные компоненты
            data = answer;
            $(options.form).find("input") //для input-ов
                .removeClass("valid")   //отключаем классы валидности
                .removeClass("invalid");  //и инвалидности )
            $(options.form).find("p.hint").text("");  //чистим hint-ы (подсказки)
            $(options.form).find(".form_hint").hide();
            if(data.error) {  //если были ошибки при валидации
                if(data.error.field) { //если сервер указал поле с ошибкой
                    //вешаем на него класс инвалидности и выводим подсказку
                    $(options.form).find("input[name='" + data.error.field + "']").addClass("invalid");
                    if(data.error.msg && !$(options.form).find("input[name='" + data.error.field + "']").is(":focus")) {
                        $(options.form).find("p[name=" + data.error.field + "]").text(data.error.msg);
                        $(options.form).find("p[name=" + data.error.field + "]").parents(".form_hint").show();
                    }
                } else if(data.error.msg) { //если пришло просто сообщение об ошибке, значит что-то пошло не так
                    if(options.onError) options.onError(data.error.msg);
                }
            }

            if(data.success) {  //в положительном ответе просматриваем
                if(data.success.fields)  //списосок положительно обработанных полей и помечаем их
                    for(var i = 0;i < data.success.fields.length;i++) {
                        if (data.success.fields[i] != "submit")
                            $(options.form).find("input[name='" + data.success.fields[i] + "']").addClass("valid");
                        else if (options.onSubmit) options.onSubmit(data);
                    }
                //if(data.success.msg) showMsg(data.success.msg,MSG_INFO);  //и выводим приятное сообщение, если есть
            }
            //мало ли что еще захочется добавить, поэтому вызываем callback функцию
            if(options.onSuccess) options.onSuccess(data);
        }, options.onError //ну и аналогично с ошибками
    );
}*/

/*
//функции инициализации и очистки формы. параметры в options:
//form - форма, form_data - информация о форме в виде объекта (шаблон ниже), url - адрес посыла запросов,
//onSuccess - дополнительные действия при успешном ответе сервера, onError -аналогично onSuccess, onSubmit....
function prepareForm(options) {
    //генерируем и вставляем на нужные позиции html-а input-ы
    if(options.form_data) {
        $(options.form).find(".form_input_pos").each(function() {
            $(this).replaceWith(renderInputPos(options.form_data.inputs[$(this).attr("input_name")]));
        });
    }
    $(options.form).find("input[validate]").blur(function() { validateForm(options) });  //для input -ов вешаем обработчики
}
*/
function clearForm(options) {
    $(options.form).find("input")  //для input -ов
        .removeClass("valid")  //чистим классы
        .removeClass("invalid");
    //$(options.form).find("input:not([type=radio])").val("");  //значения
    //а так же чистим hint-ы
    $(options.form).find("p.hint").text("");
    $(options.form).find(".form_hint").hide();
}

//функция вызывает диалоговое окно для формы, описаной данными form_data (шаблон ниже). dialog_options - параметры окна dialog-ui
$.fn.showFormDialog = function (form_data,dialog_options) {
    var form = $(this);
    var options = {
        form: form,
        form_data: form_data,
        url: form_data.url,
        onSubmit: function(data) {
            if(data.success.msg) showMsg(data.success.msg,MSG_INFO);  //и выводим приятное сообщение, если есть
            form.dialog("close");
            location.reload();
        },
        onError: function(msg) {
            showMsg(msg, MSG_ERROR);
            form.dialog("close");
        }
    };
    prepareForm(options);
    clearForm(options);
    //заполняем параметры для диалогового окга исходя из стандартных и дополнительно переданных
    var params = dialog_options;
    if(!params) params = new Object();
    if(!params.modal == null) params.modal = true;
    if(!params.title) params.title = form_data.title;
    if(!params.show) params.show = 'blind';
    if(!params.width) params.width = '640px';
    if(!params.position) params.position = { my: "center top", at: "center top", of: window };
    var ok_title = "Ok"; if(form_data.button_ok_title) ok_title = form_data.button_ok_title;
    var cancel_title = "Отмена"; if(form_data.button_cancel_title) cancel_title = form_data.button_cancel_title;
    if(!params.buttons) params.buttons = new Object();
    params.buttons[ok_title] = function() { validateForm(options,1); };
    params.buttons[cancel_title] = function() { form.dialog( "close" ); };
    //а после вызываем окно
    form.dialog(params);
}



/*  шаблон для объекта формы
var form_data = {   //данные формы
    url: "/controller/func/arg1/arg2/....",  //url для запросов (без host-а)
    inputs: {   //перечень input-ов
        user_surname: {   //название (во вьюшке по аттрибуту элемента input_name функция initForm вставляет сгенерированный код input-а в элемент)
            attrs: {  //атрибуты input-а, используемые при генерации (ф-ия renderInput)
                name: "user_surname",   //имя инпута
                id: "user_surname",     //id
                type: "text",           //и т.д
                tabindex : "1",
                placeholder: "Фамилия"
            },
            type: "text", - тип input-а
            props: [],  //свойства input-а, используемые при генерации (ф-ия renderInput)
            title: "Фамилия"   //титл input-а
        },
        user_name: {
             .............  //аналогично
        },  ...
    },
    title: "Регистрация",  //титл формы
    button_ok_title: "Зарегистрироваться",  //наименование кнопки submit
    button_cancel_title: "Отмена"   //наименование кнопки отмена
};
*/

//функция переводит строку формата 'dd.mm.yyyy hh:mm:ss' в Date
function StrToDate(str) {
    var tmp_arr = str.split(".");
    if(tmp_arr.length < 3) return null;
    var d = tmp_arr[0];
    var m = tmp_arr[1];
    tmp_arr = tmp_arr[2].split(" ");
    var y = tmp_arr[0];
    var res = y + "/" + m + "/" + d;
    if(tmp_arr.length >= 2) res += " " + tmp_arr[1];
    return new Date(res);
}

//функция проверки email-а на корректность. если strict = true, пробельные символы в конце и начале строки - ошибка
function isValidEmail (email) {
    //email = email.replace(/^\s+|\s+$/g, '');
    var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
    //email = "N@n.ru";
    var tmp = pattern.test(email);
    //showMsg(tmp);
    return tmp;
}

// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function renderPreloader() {
    var html = '<div class="preloader"><div class="preloader-center"><img alt="Загрузка..." src="/img/preloader.gif" /></div></div>';
    return html;
}

function renderMessage() {
    var html = '<div id="message" class="message msg_validation"><div class="message-cnt"><span class="message-close">X</span>' +
        '<span class="message-text"></span></div></div>';
    return html;
}

function tabsInit() {
    //настраиваем вклдаки
    $(".tabs-shell").each(function() {
        $(this).find(".tabs-content .tab-content").hide(); // Скрытое содержимое
        $(this).find(".tabs li:first").attr("id","current"); // Какой таб показать первым
        $(this).find(".tabs-content div:first").fadeIn(); // Показ первого контента таба
    });

    $('.tabs a').click(function(e) {
        e.preventDefault();
        $(this).parents(".tabs-shell").find(".tabs-content .tab-content").hide(); //Скрыть всё содержимое
        $(this).parents(".tabs-shell").find(".tabs li").attr("id",""); //Сброс идентификаторов
        $(this).parent().attr("id","current"); // Активация идентификаторов
        //var tmp = '#' + $(this).attr('title')
        $(this).parents(".tabs-shell").find('#' + $(this).attr('title')).fadeIn(); // Показать содержимое текущей вкладки
    });
}

$(document).ready(function() {
    $("body").append(renderPreloader);
    $("body").append(renderMessage);
});