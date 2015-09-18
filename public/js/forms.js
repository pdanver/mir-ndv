/**
 * Created by DanVer on 07.09.2015.
 */

//функции инициализации и очистки формы. параметры в options:
//form - форма, form_data - информация о форме в виде объекта (шаблон ниже), url - адрес посыла запросов,
//onSuccess - дополнительные действия при успешном ответе сервера, onError -аналогично onSuccess, onSubmit....
$.fn.formPrepare = function(args) {
    var form = $(this);
    //генерируем и вставляем на нужные позиции html-а input-ы
    if(args && args.form_data) {
        form.find(".form_input_pos").each(function() {
            if(renderInputPos && (typeof renderInputPos === "function")) {
                $(this).replaceWith(renderInputPos(args.form_data.inputs[$(this).attr("input_name")]));
            }
        });
    }
    //form.find("input[validate]").blur(function() { validateForm(options) });  //для input -ов вешаем обработчики
}

$.fn.validateInput = function(func) {
    if(func === null) return null;
    var func_res = func($(this).val());
    var form_input = $(this).parents(".form_input").removeClass("invalid").removeClass("valid").attr("hint","");
    if(func_res === null) return null;
    if(func_res.length > 0) form_input.attr("hint",func_res).addClass("invalid");
    else {
        form_input.attr("hint",func_res).addClass("valid");
        $("#hint").hide();
    }
    return func_res;
}

$.fn.setOnValidateInputFunc = function(func) {
    if(!$(this)) return;
    $(document).on("blur",$(this).selector,function() { $(this).validateInput(func); });
    $(this)[0].validate = function() { return $(this).validateInput(func); }
}

//функция валидации формы данные передаются объектом options: form - форма, url - адрес посыла запросов, is_submit произвести окончательный submit после
//валидации, onSuccess - дополнительные действия при успешном ответе сервера, onError -аналогично onSuccess
$.fn.formSubmit = function(options,is_submit){
    if(!options.url) return;
    //заполняем данные для запроса
    var send_data = {
        submit: is_submit
    };
    //проводим валидацию формы
    var is_validate = true;
    $(this).find("input").each(function() {
        if ((typeof $(this)[0].validate === "function")) {
            var tmp = $(this)[0].validate();
            if((tmp !== null) &&(tmp.length > 0)) is_validate = false;
        }
    });
    if(!is_validate) return false;

    //считываем все значения input-ов
    $(this).find("input").each(function() {
        //if($(this).attr("validate")) send_data["validate_fields"].push($(this).attr("name"));
        //if($(this).attr("required")) send_data["required_fields"].push($(this).attr("name"));
        //в зависимости от инпута, соответствующие действия
        var input_type = $(this).attr("type");
        if(input_type.localeCompare("checkbox") == 0) {
            if($(this).attr("checked")) send_data[$(this).attr("name")] = true;
            else send_data[$(this).attr("name")] = false;
        } else if(input_type.localeCompare("radio") == 0) {
            if($(this).prop("checked")) send_data[$(this).attr("name")] = $(this).val();
        } else if(input_type.localeCompare("phone") == 0) {
            if(send_data[$(this).attr("name")] == null) send_data[$(this).attr("name")] = [];
            if($(this).val().length > 0)
                send_data[$(this).attr("name")].push($(this).parent().find("select").val() + ',' + $(this).val());
        } else {
            send_data[$(this).attr("name")] = $(this).val();
        }
    });

    var form = $(this);
    //послыаем запрос
    ajaxRequest(options.url,send_data,"POST",
        function(answer) {  //при успешном ответе сервера
            //настраиваем визуальные компоненты
            var data = answer;
            /*form.find(".form_input") //для input-ов
                .removeClass("valid")   //отключаем классы валидности
                .removeClass("invalid")  //и инвалидности )
                .attr("hint","");*/
            if(data.error) {  //если были ошибки при валидации
                if(data.error.field) { //если сервер указал поле с ошибкой
                    //вешаем на него класс инвалидности и выводим подсказку
                    var invalid_input = form.find("input[name='" + data.error.field + "']").parents(".form_input");
                    invalid_input.addClass("invalid").attr("hint",(data.error.msg)?data.error.msg:"");
                    /*if(data.error.msg && !$(form).find("input[name='" + data.error.field + "']").is(":focus")) {
                        $(options.form).find("p[name=" + data.error.field + "]").text(data.error.msg);
                        $(options.form).find("p[name=" + data.error.field + "]").parents(".form_hint").show();
                    }*/
                //} else if(data.error.msg) { //если пришло просто сообщение об ошибке, значит что-то пошло не так
                    if(options.onError) options.onError(data.error);
                }
            }

            if(data.success) {  //в положительном ответе просматриваем
                if(data.success.fields)  //списосок положительно обработанных полей и помечаем их
                    for(var i = 0;i < data.success.fields.length;i++) {
                        if (data.success.fields[i] != "submit") {
                            form.find("input[name='" + data.success.fields[i] + "']").parents(".form_input").addClass("valid");
                        }
                        else if (options.onSubmit) options.onSubmit(data);
                    }
                //if(data.success.msg) showMsg(data.success.msg,MSG_INFO);  //и выводим приятное сообщение, если есть
            }
            //мало ли что еще захочется добавить, поэтому вызываем callback функцию
            if(options.onSuccess) options.onSuccess(data);
        }, options.onError //ну и аналогично с ошибками
    );
}