/**
 * Created by DanVer on 02.09.2015.
 */

/*
 * Модуль для работы с адресами. Для вывода визуального компонента Geo, позволяющего выбирать адрес (Старна, Регион,
 * Город, Населенный пункт, Улицу, номер дома и кватриры), достаточно вызвать функцию appendGeo() для любого блока.
 * Например, для блока <div id="geo-place></div>, достаточно в скрипте вызывать $("#geo-place").appendGeo().
 * Данная функция добавит к html коду блока код визуального компонента Geo.
 *
 * Описание аргументов args appendGeo(args). args - объект аргументов вида { arg1: "Arg1", arg2: "Arg2" }
 *      args.name - имя для скрытого input-а (по умолчаню geo_value). в него записывается выбор пользователя в виде
 *                  объекта в json строке. структура объекта
 *                  {   geo_country: { name: наименование страны , code: код страны },
 *                      geo_region: { name: наименование региона, code: код региона },
 *                      geo_city: { name: наименование города или района, code: кладр код города или района },
 *                      geo_place: { name: наименование населенного пункта, code: кладр код населенного пункта },
 *                      geo_street: { name: наименование улицы, code: код кладр улицы },
 *                      house: номер дома,
 *                      building: корпус,
 *                      apartment: номер квартиры  }
 */
$.fn.appendGeo = function(args) {
    if(!args) args = {};
    if(args.street === undefined) args.street = true;
    //if(!args.street) args.street = true;

    //функция возвращает html код визуального элемента geo-select
    function renderGeoSelect(class_name,input_name,title) {
        var html =
            '<div class="' + class_name + ' geo-select">' +
            '<div class="geo-select-cnt active">' +
            '<div class="geo-select-btn">' +
            '<span class="geo-select-value-default">' + title + '</span>' +
            '<span style="display: none;" name="' + input_name + '" class="geo-select-value" code=""></span>' +
            '<span class="geo-select-arrow_down"></span>' +
            '</div>' +
            '<div style="display: none;" class="geo-select-options">' +
            '<div><input type="text" name=""></div>' +
            '<ul></ul>' +
            '</div>' +
            '</div>' +
            '</div>';
        return html;
    }

    //вставляем в текущий блок необходимые элементы
    $(this).append('<span class="geo-value-text">Адрес: </span><br/>');
    $(this).append(renderGeoSelect("geo-country","geo_country","Выберите страну"));
    $(this).append(renderGeoSelect("geo-region","geo_region","Выберите регион"));
    $(this).append(renderGeoSelect("geo-city","geo_city","Выберите город или район"));
    $(this).append(renderGeoSelect("geo-place","geo_place","Выберите населенный пункт"));
    if(args.street) {
        $(this).append(renderGeoSelect("geo-street","geo_street","Выберите улицу"));
        $(this).append('<div class="geo-house">' +
            renderInputPos({ type: "text", name: "", attrs: { tabindex : "1", id: "geo-house", placeholder: "Дом" } }) +
            renderInputPos({ type: "text", name: "", attrs: { tabindex : "2", id: "geo-building", placeholder: "Корпус" } }) +
            renderInputPos({ type: "text", name: "", attrs: { tabindex : "3", id: "geo-apartment", placeholder: "Квартира" } }) +
            '</div>');

    }
    $(this).append(renderInputPos({ type: "hidden", name: ((args && args.name)?args.name:"geo_value") }));
    //
    initGeo($(this));
}

//функция закрывает элемент geo-select. select_elem - любой дочерний элемент geo-select или сам geo-select
function closeSelect(select_elem) {
    var select = $(select_elem).closest(".geo-select");
    $(select).find('div.geo-select-btn').removeClass('active');
    $(select).find('div.geo-select-options').hide();
    $(select).find('span.geo-select-arrow_down').show();
}
//функция закрывает все другие элементы geo-select одного уровня вложенности с текущим.
//select_elem - любой дочерний элемент geo-select или сам geo-select
function closeOtherSelect(select_elem) {
    $(select_elem).parents('.geo-select').siblings('div').each(function(){
        closeSelect($(this));
    });
}
//устанавливаем обработчик события на раскрытие списка geo-select по клику
$(document).on('click','.geo-select-btn',function() {
    closeOtherSelect(this);
    if ($(this).parents('.geo-select-cnt').hasClass('disabled')) return;
    if ($(this).hasClass('active')) {
        $(this).removeClass('active');
        $(this).find('span.geo-select-arrow_down').show();
        $(this).siblings('div.geo-select-options').hide();
    } else {
        $(this).addClass('active');
        $(this).find('span.geo-select-arrow_down').hide();
        $(this).siblings('div.geo-select-options').show().find('input').focus();
    }
});
//функция очистки элементов geo-select и geo-house
$.fn.clear = function() {
    if($(this).is(".geo-select")) {
        $(this).removeClass("hand_enter");
        $(this).find(".geo-select-options li").removeClass('selected');
        $(this).addClass('selected');

        $(this).find(".geo-select-options ul").empty();

        $(this).find('span.geo-select-value').hide().text("").attr("code","").removeClass("selected").removeClass("no_select");
        $(this).find('span.geo-select-value-default').show();
    }
    if($(this).is(".geo-house")) { $(this).find("input").val(""); }
    return this;
}
//функция обработки клика мыши по документу, для закрытия селектов
$(document).click(function(e){
    var select_cnt = $('.geo-select-cnt');
    if(!$(e.target).closest('.geo-select-cnt').length){
        select_cnt.find('div.geo-select-btn').removeClass('active');
        select_cnt.find('div.geo-select-options').hide();
        select_cnt.find('span.geo-select-arrow_down').show();
    }
});
//функция обработки клика по пункту options у geo-select
$(document).on('click','.geo-select-options li',function(){
    var select = $(this).parents('.geo-select');
    var selected = select.find('ul li.selected');
    var selector = select.find("geo-select-cnt").attr('id');
    closeSelect(this);
    if(selected && $(this).attr('id') == selected.attr('id')) return false; //если клик по уже выбранному, возвращаемся.
    $(this).siblings().removeClass('selected');
    $(this).addClass('selected');

    select.find('span.geo-select-value').text($(this).text()).attr("code",$(this).attr("id")).addClass("selected").show();
    if($(this).hasClass("no_select")) select.find('span.geo-select-value').addClass("no_select");
    select.find('span.geo-select-value-default').hide();

    select.find("input").removeClass("changed");
    $(this).parents(".geo-select").trigger('onSelectValue',[$(this).attr("id"),select.hasClass("hand_enter")]);
});
//функция обработки потери фокуса у input-а у geo-select-а для обработки ручного ввода данных об адресном объекте
//только для элементов geo-select с классом hand_enter
$(document).on('focusout','.geo-select.hand_enter .geo-select-options input', function() {
    var select = $(this).parents(".geo-select");
    if(!select.hasClass("hand_enter")) return;

    if(!$(this).hasClass("changed")) return;
    if($(this).val().length == 0) return;

    closeSelect(this);

    select.find('span.geo-select-value').text($(this).val()).addClass("selected").show();
    select.find('span.geo-select-value-default').hide();

    $(this).parents(".geo-select").trigger('onSelectValue',[$(this).attr("id"),true]);
});
//функция обработки установки фокуса для input-ов geo-select-а
$(document).on('focusin','.geo-select-options input', function() { $(this).removeClass("changed"); });

//обработка ввода в поле input у geo-select-а для реакции поисковика
var timeout_input; //id таймаут функции для задержки поиска при вводе
//$(geo).find('div.geo-select-options input').keyup(function(){
$(document).on('keyup','div.geo-select-options input',function(){
    var text = $(this).val();
    var options = $(this).parents('.geo-select-options').find('ul li');

    $(this).addClass("changed");

    clearTimeout(timeout_input);
    timeout_input = setTimeout(function() {
        options.each(function(){
            var filter = new RegExp('(^|\\s)' + text, 'ig'),
                liText = $(this).text();
            var result = liText.match(filter);
            if(result) $(this).show();
            else $(this).hide();
        });
    }, 500);
});

//функция инициализации функций и событий элемента Geo
function initGeo(geo) {
    //функция собирает данные о выборе пользователя и вывоит их в скрытом input-e и в текстовом поле
    function setGeoValue(geo) {
        var value = "";
        var input_value = {};
        $(geo).find("span.geo-select-value.selected:not(.no_select)").each(function(i) {
            if(i > 0) value += ", ";
            value += $(this).text();
            input_value[$(this).attr("name")] = { name: $(this).text(), code: $(this).attr("code")};
        });

        var house = $(geo).find("input#geo-house").val();
        if(house && (house.length > 0)) {
            value += ", д." + house;
            input_value["house"] = house;
            var building = $(geo).find("input#geo-building").val();
            if(building && (building.length > 0)) {
                value += " к." + building;
                input_value["building"] = building;
            }
            var apartment = $(geo).find("input#geo-apartment").val();
            if(apartment && (apartment.length > 0)) {
                value += ", кв." + apartment;
                input_value["apartment"] = apartment;
            }
        }

        input_value["value"] = value;
        $(geo).find(".geo-value-text").text("Адрес: " + value);
        $(geo).find('input[name="geo_value"]').val(JSON.stringify(input_value));
        //showMsg($(geo).find('input[name="geo_value"]').val() );
    }

    //функции получения списков стран, регионов, городов, населенных пунктов и улиц от сервера через ajax запросы
    function getCountries(select) { ajaxRequest("/Geo/get_countries", null, "POST",function (data) { onGetGeoOptions(data,select); }); }
    function getRegions(code,select) { ajaxRequest("/Geo/get_regions", { code: code }, "POST", function (data) { onGetGeoOptions(data,select); }); }
    function getCities(code,select) { ajaxRequest("/Geo/get_cities", { code: code }, "POST", function (data) { onGetGeoOptions(data,select); }); }
    function getPlaces(code,select) { ajaxRequest("/Geo/get_places", { code: code }, "POST", function (data) { onGetGeoOptions(data,select); }); }
    function getStreets(code,select) { ajaxRequest("/Geo/get_streets", { code: code }, "POST", function (data) { onGetGeoOptions(data,select); }); }
    //функция заполняет список options у селекта из массива data. (для обработки ajax ответов сервера)
    function onGetGeoOptions(data,select) {
        if(data.error) { showMsg(data.error,MSG_ERROR); return; }
        if(data.success) {
            var options = $(select).find("ul");
            if(!options) return;
            options.empty();
            data.success.res.forEach(function(item) { $(options).append('<li id="' + item.code + '"' + ((item.no_select)?' class="no_select"':'') + '>' + item.name + '</li>'); }); //
            if(data.success.hand_enter) $(select).addClass("hand_enter");
            $(select).show();
        }
    }
    //устанавливаем обработчики выбора селектов
    $(geo).find(".geo-country").bind('onSelectValue',function(e,id) {
        $(this).nextAll().hide().clear();
        setGeoValue(geo);
        getRegions(id,$(this).next());
    });
    $(geo).find(".geo-region").bind('onSelectValue',function(e,id,hand_enter) {
        $(this).nextAll().hide().clear();
        setGeoValue(geo);
        if(hand_enter) $(this).next().addClass("hand_enter").show();
        else getCities(id,$(this).next());
    });

    $(geo).find(".geo-city").bind('onSelectValue',function(e,id,hand_enter) {
        $(this).nextAll().hide().clear();
        setGeoValue(geo);
        if(hand_enter) {
            var data = {success: { res: [ {code: 0, name: "[не указывать]"}], hand_enter: 1 }};
            onGetGeoOptions(data,$(this).next());
        }
        else getPlaces(id,$(this).next());
    });
    $(geo).find(".geo-place").bind('onSelectValue',function(e,id,hand_enter) {
        $(this).nextAll().hide().clear();
        setGeoValue(geo);
        if(!$(this).parent().find(".geo-street")) return;
        if(hand_enter) $(this).next().addClass("hand_enter").show();
        else getStreets(id,$(this).next());
    });
    $(geo).find(".geo-street").bind('onSelectValue',function(e,id,hand_enter) {
        $(this).next().show().clear();
        setGeoValue(geo);
    });
    $(geo).find(".geo-house input").focusout(function() {
        setGeoValue(geo);
    });

    //инициализируем пероночальное состояние элемента Geo
    $(geo).find(".geo-country").nextAll().hide().clear();
    getCountries($(geo).find(".geo-country"));
}



