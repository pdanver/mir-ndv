/**
 * Created by DanVer on 07.09.2015.
 */

//функция генерирует код input-а с параметрами input (шаблон ниже) и возвращает строкой.
function renderInputPos(input) {
    if(!input || !input.type || (input.name === undefined)) return "";
    //определяем тип input-а и перенаправляем вывод
    var html = '<div class="form_input ' + input.type + ((input.hint !== undefined)?' hint':'') + '"' + ((input.hint !== undefined)?(' hint="' + input.hint + '"'):'') + '>';
    if((input.type.localeCompare("checkbox") == 0) && (typeof renderInputCheckbox === "function")) html += renderInputCheckbox(input);
    else if((input.type.localeCompare("radio") == 0) && (typeof renderInputRadio === "function")) html += renderInputRadio(input);
    else if((input.type.localeCompare("hidden") == 0) && (typeof renderInputHidden === "function"))html += renderInputHidden(input);
    else if((input.type.localeCompare("phone") == 0) && (typeof renderInputPhone === "function"))html += renderInputPhone(input);
    else if((input.type.localeCompare("select") == 0) && (typeof renderInputSelect === "function"))html += renderInputSelect(input);
    else if(typeof renderInput === "function") html += renderInput(input);
    html += '</div>';
    return html;
}

//функция генерация input-а по умолчанию
function renderInput(input) {
    var html = '<div class="form_input-shell">';
    html += '<input type="' + input.type + '" name="' + input.name + '"';   //начинаем инпут
    if(input.attrs) for(var key in input.attrs) html += ' ' + key + '="' + input.attrs[key] + '"';  //атрибуты
    if(input.props) input.props.forEach(function(item) { html += ' ' + item; });   //свойства
    html += '>';   //
    html += "</div>";
    return html;
}

//функция генерации кода checkbox-а
function renderInputCheckbox(input) {
    var html = '<input type="' + input.type + '" name="' + input.name + '"';   //начинаем инпут
    if(input.attrs) for(var key in input.attrs) html += ' ' + key + '="' + input.attrs[key] + '"';  //атрибуты
    if(input.props) input.props.forEach(function(item) { html += ' ' + item; });   //свойства
    html += '>';   //
    html += input.title + '<div class="checkbox1"></div>';
    return html;
}

//функция генерирует код input-а с type=radio
function renderInputRadio(input) {
    var html = '<div class="form_input"><div class="form_input-radio">';
    if(input.title) html += '<label>' + input.title + '</label>';  //подпись
    if(!input.items) return "";
    html += '<div class="form_input-radio-items">';
    input.items.forEach(function(item) {
        if(item.title) html += '<label>' + item.title + '</label>';  //подпись
        html += '<input type="' + input.type + '" name="' + input.name + '"';   //начинаем инпут
        if(item.attrs) for(var key in item.attrs) html += ' ' + key + '="' + item.attrs[key] + '"';  //атрибуты
        if(item.props) item.props.forEach(function(item) { html += ' ' + item; });   //свойства
        if((input.value)&&(item.attrs.value)&&(input.value.localeCompare(item.attrs.value) == 0)) html += " checked";
        html += '>';   //
    });
    html += '</div><span class="form_hint"><p class="hint error" name="' + input.name + '"></p></span>'; //подсказка
    html += "</div></div>";
    //shoMsg(html);
    return html;
}

//функция генерирует код input-а с type=hidden
function renderInputHidden(input) {
    return '<input type="hidden" name="' + input.name + '">';  //выводим инпут';
}

//функция генерирует код input-а с type=select
function renderInputSelect(input) {
    var html = '<div class="form_input-shell">';
    html += '<div class="form_input-arrow"></div>';
    html += '<select name="' + input.name + '"';   //начинаем инпут
    if(input.attrs) for(var key in input.attrs) html += ' ' + key + '="' + input.attrs[key] + '"';  //атрибуты
    if(input.props) input.props.forEach(function(item) { html += ' ' + item; });   //свойства
    html += '>';
    if(input.items) input.items.forEach(function(item) { html += '<option>' + item + '</option>'; });
    html += "</select></div>";
    return html;
    /*var html = '<div class="form_input">';
    if(input.title) html += '<label for="' + input.name + '">' + input.title + '</label>';  //подпись
    html += '<select name="' + input.name + '"';   //начинаем select
    if(input.attrs) for(key in input.attrs) html += ' ' + key + '="' + input.attrs[key] + '"';  //атрибуты
    if(input.props) input.props.forEach(function(prop) { html += ' ' + prop; });   //свойства
    html += ">";
    if(input.options) input.options.forEach(function(item) {
        html += "<option"
        if(item.attrs) for(key in item.attrs) html += ' ' + key + '="' + item.attrs[key] + '"';  //атрибуты
        if(item.props) item.props.forEach(function(prop) { html += ' ' + prop; });   //свойства
        html += ">";
        if(item.title) html += item.title;
        html += "</option>";
    });
    html += "</select>";
    html += '<span class="form_hint"><p class="hint error" name="' + input.name + '"></p></span>'; //подсказка
    html += "</div>";
    return html;*/
}

//функция генерирует код input-а с type=phone
function renderInputPhone(input) {
    if(!input || !input.type || !input.name) return "";
    //если это radio, то перенаправляем вывод
    var html = '<div class="form_input phones">';
    if(input.title) html += '<label>' + input.title + '</label>';  //подпись

    html += '<div class="phone">';
    html += '<input type="phone" name="' + input.name + '"';   //начинаем инпут
    var phone_type = "";
    if(input.phones && input.phones[0]) {
        phone_type = input.phones[0].type;
        html += ' value="' + input.phones[0].phone + '"';
    }

    var attrs = "";
    if(input.attrs) for(var key in input.attrs) attrs += ' ' + key + '="' + input.attrs[key] + '"';  //атрибуты
    if(input.props) input.props.forEach(function(prop) { attrs += ' ' + prop; });   //свойства
    html += attrs + '>';
    if(input.types) {
        html += '<select class=\'phone_type\'>';
        input.types.split(';').forEach(function(item) {
            var option = item.split(',');
            html += '<option value="' + option[0] + '"';
            if(phone_type.localeCompare(option[0]) == 0) html += " selected";
            html += '>' + option[1] + '</option>';
        });
        html += '</select>';
    }
    html += '<div class="add_phone" onclick=\"inputPhoneAddItem(\'' + input.name + '\',\'' + attrs + '\',\'' + input.types + '\')\">&nbsp+</div>'; //'\",\"' + attrs + '\",\"' + select +
    html += '<span class="form_hint"><p class="hint error" name="' + input.name + '"></p></span>'; //подсказка
    html += '</div>';

    if(input.phones) for(var i = 1;i < input.phones.length;i++) {
        html += inputPhoneAddItem(input.name,attrs,input.types,input.phones[i].phone,input.phones[i].type,true);
    }

    html += "</div>";
    return html;
}
//функция добавления блока с телефоном
function inputPhoneAddItem(name,attrs,types,phone,type,return_to_string) {
    //формируем блок с новым телефоном
    var phone = '<div class="phone"><input type="phone" name="' + name + '"' + attrs + ' value="' + phone + '"/>';
    if(types) {
        phone += '<select class=\'phone_type\'>';
        types.split(';').forEach(function(item) {
            var option = item.split(',');
            phone += '<option value="' + option[0] + '"';
            if(type && type.localeCompare(option[0]) == 0) phone += ' selected';
            phone += '>' + option[1] + '</option>';
        });
        phone += '</select>';
    }
    phone += '<div class="delete_phone">&nbsp-</div></div>';
    if(return_to_string) return phone;
    //добавляем его
    $(".phones").append(phone);
    //инициализируем телефоны
    initPhones();
}

//функция инициализации телефонов
function initPhones() {
    //устанавливаем маску на телефоны
    $(".phone").find("input").mask("+7 (999) 999-9999");
    //устанавливаем обработчик на нажатие кнопки удаления телефона
    $(".phone").find(".delete_phone").click(function() {
        $(this).parent().remove(); //удаляем блок div с телефоном
    });
}

$.fn.windowShowStyle3 = function(args) {
    if(args === undefined) args = {};
    if(args.title === undefined) args.title = "";
    if(args.minimize === undefined) args.minimize = true;
    var title_html =
        '<div class="window-title">' +
            '<div class="name">' + args.title + '</div>' +
            '<div class="fr">' +
                '<div class="window-close"><a href="javascript:void(0)"></a></div>' +
                ((args.minimize)?('<div class="window-minimize"><a href="javascript:void(0)"></a></div>'):'') +
                '<div class="submit"><input type="submit" value="Найти"></div>' +
                '<div class="insearch-right w13">' +
                    '<div class="insearch-left">' +
                        '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="list-menu">' +
                            '<tr>' +
                                '<td><div class="data">Наименование</div></td>' +
                                '<td class="ico"><a href="#"></a></td>' +
                            '</tr>' +
                        '</table>' +
                    '</div>' +
                '</div>' +
                '<label>Поиск по:</label>' +
                '<div class="insearch-right w13">' +
                    '<div class="insearch-left">' +
                        '<input name="" value="Ключевое слово" onblur="if(this.value==\'\') this.value=\'Ключевое слово\';" onfocus="if(this.value==\'Ключевое слово\') this.value=\'\';" type="text" />' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

    args.title_html = title_html;
    args.class += " window-style3";

    $(this).windowShow(args);
}

$(document).on("click",".form_input .checkbox1",function() {
    $(this).toggleClass("checked");
    var checkbox = $(this).parents(".form_input").find("input");
    if($(this).hasClass("checked")) checkbox.attr("checked","true");
    else checkbox.removeAttr("checked");
    checkbox.trigger("change");
});

$(document).on("mousemove",".hint",function(e) {
    if((typeof $(this).attr("hint") == "string") && $(this).attr("hint").length > 0) {
        $("#hint span").html($(this).attr("hint"));
        $("#hint").offset({ left: e.pageX + 15, top: e.pageY + 10 });
        $("#hint").show();
    }
});
$(document).on("mouseleave",".hint",function() {
    $("#hint").hide();
});

$(document).ready(function() {
    $("body").append('<div id="hint"><span></span></div>');
    $("#hint").hide();
});

