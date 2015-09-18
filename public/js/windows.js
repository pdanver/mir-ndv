/**
 * Created by DanVer on 05.09.2015.
 */

const WINDOW_STATE_SHOW = 1;
const WINDOW_STATE_MINIMIZE = 2;
const WINDOW_STATE_STAYONTOP = 3;

//общее состояние среды windows
var windowsStates = {
    options: {  //настройки
        //минимальный размер окна
        minWindowWidth: 130,
        minWindowHeight: 130,
        maxWindowWidth: 3000,
        maxWindowHeight: 2000,
        defaultWindowWidth: 400,
        defaultWindowHeight: 450,
        //стартовое значение для раздачи z-index-ов и интервал z-index-ов, выделяемый на окно
        zIndexStart: 100,
        zIndexInterval: 10
    },
    windowsCounter: 0,
    mouseLastPageX: 0,
    mouseLastPageY: 0,
    windows: []
};

$.fn.windowShow = function(args){
    //устанавливаем значения по умолчанию
    if(!args) args = {};
    if(args.id === undefined) args.id = "window_" + windowsStates.windowsCounter;
    var window_index = windowFindIndex(args.id);
    if(window_index >= 0) {
        if(windowsStates.windows[window_index].state == WINDOW_STATE_MINIMIZE) windowMaximize(args.id);
        return;
    }
    if(!args.title) args.title = 'Окно ' + args.id;
    if(args.pos === undefined) args.pos = { x: "center", y: "center" };
    if(!args.size) args.size = { width: windowsStates.options.defaultWindowWidth, height: windowsStates.options.defaultWindowHeight };
    if(args.minimize === undefined) args.minimize = true;
    if(args.resizable === undefined) args.resizable = true;
    if(args.movable === undefined) args.movable = true;
    if(args.stay_on_top === undefined) args.stay_on_top = false;
    if(args.stay_on_top) args.minimize = false;
    if(args.min_width === undefined) args.min_width = windowsStates.options.minWindowWidth;
    if(args.min_height === undefined) args.min_height = windowsStates.options.minWindowHeight;
    if(args.max_width === undefined) args.max_width = windowsStates.options.maxWindowWidth;
    if(args.max_height === undefined) args.max_height = windowsStates.options.maxWindowHeight;


    var window_info = {
        window_id: args.id,
        title: args.title,
        minimize: args.minimize,
        resizable: args.resizable,
        movable: args.movable,
        min_width: args.min_width,
        min_height: args.min_height,
        max_width: args.max_width,
        max_height: args.max_height,
        state: (args.stay_on_top)?WINDOW_STATE_STAYONTOP:WINDOW_STATE_SHOW,
        buttons: []
        };
    //showMsg(toString(window_info));

    var window_html =
        ((args.stay_on_top)?('<div class="window-background">'):'') +
        '<div class="window-shell ' + ((args.class)?args.class:'') + '" id="' + args.id + '">' +
        '<div class="window-cnt">';
    if(args.title_html !== undefined) window_html += args.title_html;
    else window_html += '<div class="window-title">' +
        '<div class="window-title-text"><span>' + args.title + '</span></div>' +
        '<div class="window-close">X</div>' +
        ((args.minimize)?('<div class="window-minimize">_</div>'):'') +
        '</div>';

    window_html += '<div class="window-content">' +
        $(this).html() +
        '</div>' +
        '<div class="window-buttons">';

    //добавляем кнопки
    if(args.buttons) args.buttons.forEach(function(item,i) {
        window_html += '<button class="window-button"';
        if(!item.id) item.id = "button_" + i;
        window_html += ' id="' + item.id + '"';
        //if(item.click) window_html += ' onclick="javascript: ' + item.click + '"';
        window_html += '>';
        if(item.title) window_html += item.title;
        window_html += '</button>';
        //window_info.buttons.push(item);
    });
    window_html += '</div></div></div>' + ((args.stay_on_top)?('</div>'):'');
    $("body").append(window_html);

    //устанавливаем обработчики для кнопок
    if(args.buttons) args.buttons.forEach(function(item) {
        if(item.click) $('#' + item.id).click(item.click);
    });

    windowsStates.windowsCounter++;

    updateWindowsQueue();
    window_info.z_index = windowsStates.options.zIndexStart + windowsStates.options.zIndexInterval*windowsStates.windows.length;
    windowsStates.windows.unshift(window_info);
    $('#' + args.id).css("z-index",window_info.z_index);
    if(args.stay_on_top) $('#' + args.id).parent().css("z-index",window_info.z_index-1);

    if(args.resizable) $('#' + args.id).bind("mousemove",onWindowShellMouseMove);
    else $('#' + args.id).unbind("mousemove",onWindowShellMouseMove);

    if(args.movable) $('#' + args.id + ' .window-title').css("cursor","move").bind("mousedown",onWindowTitleMouseDown);
    else $('#' + args.id + ' .window-title').css("cursor","default").unbind("mousedown", onWindowTitleMouseDown);

    windowResize(args.id,args.size.width,args.size.height);
    windowMove(args.id,args.pos.x,args.pos.y);

    $('#' + args.id).fadeIn(); //плавное появление блока
}
function windowClose(window_id){
    $('#' + window_id).unbind("mousemove",onWindowShellMouseMove);
    $('#' + window_id + ' .window-title').unbind("mousedown",onWindowTitleMouseDown);
    if($('#' + window_id).parents(".window-background")) $('#' + window_id).parents(".window-background").fadeOut().remove();
    $('#' + window_id).fadeOut().remove(); //плавное исчезание блока

    windowsStates.windows.forEach(function(item,i) {
        if(item["window_id"].localeCompare(window_id) == 0) windowsStates.windows.splice(i,1);
    });
}
function windowCloseAll() {
    var windows_id = [];
    for(var i = 0;i < windowsStates.windows.length;i++) windows_id.push(windowsStates.windows[i].window_id);
    for(var i = 0;i < windows_id.length;i++) windowClose(windows_id[i]);
}

$.fn.windowContent = function(html,is_append) {
    if(is_append) $(this).find(".window-content").append(html);
    else $(this).find(".window-content").html(html);
}

function sortWindowsQueue() {
    function compareWindowsZIndex(a, b) { if (a.z_index > b.z_index) return -1; if (a.z_index < b.z_index) return 1; }
    windowsStates.windows.sort(compareWindowsZIndex);
}
function updateWindowsQueue() {
    windowsStates.windows.forEach(function(item,i) {
        item["z_index"] = windowsStates.options.zIndexStart + (windowsStates.windows.length - i - 1)*windowsStates.options.zIndexInterval;
        $('#' + item["window_id"]).css("z-index",item["z_index"]);
    });
}
function popupWindow(window_id) {
    for(var i = 0;i < windowsStates.windows.length;i++) {
        if(windowsStates.windows[i]["window_id"] == window_id) {
            windowsStates.windows.unshift(windowsStates.windows.splice(i,1)[0]);
            break;
        }
    }
    updateWindowsQueue();
}

function windowFindIndex(window_id) {
    for(var i = 0;i < windowsStates.windows.length;i++)
        if(windowsStates.windows[i]["window_id"] == window_id) return i;
    return -1;
}

function windowMinimize(window_id) {
    var window_index = windowFindIndex(window_id);
    if(window_index < 0) return;
    if(windowsStates.windows[window_index].state == WINDOW_STATE_MINIMIZE) return;

    $("#" + window_id).fadeOut();
    var html_button = '<div class="taskbar-button" id="taskbar-button-' + window_id + '" onclick="windowMaximize(\'' + window_id + '\')">' +
        windowsStates.windows[window_index].title + '</div>';
    $("#window-taskbar").append(html_button);
    windowsStates.windows[window_index].state = WINDOW_STATE_MINIMIZE;

    if($("#window-taskbar").is(":hidden")) $("#window-taskbar").slideDown();
}
function windowMaximize(window_id) {
    var window_index = windowFindIndex(window_id);
    if(window_index < 0) return;
    if(windowsStates.windows[window_index].state != WINDOW_STATE_MINIMIZE) return;

    $("#window-taskbar #taskbar-button-" + window_id).remove();
    $("#" + window_id).fadeIn();
    windowsStates.windows[window_index].state = WINDOW_STATE_SHOW;
    popupWindow(window_id);

    for(var i = 0;i < windowsStates.windows.length;i++)
        if(windowsStates.windows[i].state == WINDOW_STATE_MINIMIZE) return;
    $("#window-taskbar").slideUp();
}

function windowMove(window_id,x,y) {
    var win = $("#" + window_id);
    if(!win) return;
    var window_index = windowFindIndex(window_id);
    if(window_index < 0) return;
    var window = windowsStates.windows[window_index];

    var pos = win.position();
    var offset = { left: pos.left, top: pos.top };
    if(x) {
        if(x == "center") offset.left = Math.floor((document.body.clientWidth - win.width())/2);
        else if(x == "left") offset.left = 0;
        else if(x == "right") offset.left = document.body.clientWidth - win.width();
        else offset.left = x;
    }
    if(y) {
        if(y == "center") offset.top = $(document).scrollTop() + Math.floor((document.body.clientHeight - $(win).height())/2);
        else if(y == "top") offset.top = $(document).scrollTop();
        else if(y == "bottom") offset.top = $(document).scrollTop() + (document.body.clientHeight - win.height());
        else {
            if(window.state == WINDOW_STATE_STAYONTOP) offset.top = $(document).scrollTop() + y;
            else offset.top = y;
        }
        if(window.state != WINDOW_STATE_STAYONTOP) offset.top += $(document).scrollTop();
        if(offset.top < 0) offset.top = 0;
    }
    $(win).offset(offset);
}
function windowResize(window_id,width,height,animate_time) {
    var window = $("#" + window_id);
    if(!window) return;
    var window_index = windowFindIndex(window_id);
    if(window_index < 0) return;
    var window_info = windowsStates.windows[window_index];

    var new_width = 0, new_height = 0;
    var title_height = window.find(".window-title").outerHeight(true);
    var buttons_height = window.find(".window-buttons").outerHeight(true);
    //if(buttons_height) buttons_height += 30;
    if(width) new_width = width; else new_width = window.width();
    if(height) new_height = height; else new_height = window.height();

    if(new_width < window_info.min_width) new_width = window_info.min_width;
    if(new_width > window_info.max_width) new_width = window_info.max_width;
    var content_height = title_height + buttons_height + 10;
    if(new_height < window_info.min_height) new_height = window_info.min_height;
    if(new_height < content_height) new_height = content_height;
    if(new_height > window_info.max_height) new_height = window_info.max_height;

    /*if(animate_time && (animate_time > 0)) {
        $(window).animate({
            width: new_width,
            height: new_height
        }, animate_time, function() { window.find(".window-content").height(new_height - content_height); });
        return;
    }*/
    //showMsg(new_height - buttons_height);
    window.width(new_width).height(new_height);
    window.find(".window-content").height(new_height - content_height);
}

function onMoveWindow(e) {
    $(".window-shell[drag_drop=1]").each(function() {
        var pos = $(this).position();
        $(this).offset({
            left: pos.left + (e.pageX - windowsStates.mouseLastPageX),
            top: pos.top + (e.pageY - windowsStates.mouseLastPageY)});
    });
    windowsStates.mouseLastPageX = e.pageX;
    windowsStates.mouseLastPageY = e.pageY;
}

function onWindowResize(e) {
    var delta_x = e.pageX - windowsStates.mouseLastPageX;
    var delta_y = e.pageY - windowsStates.mouseLastPageY;

    $(".window-shell[resize_window=1]").each(function() {
        var resize_border = parseInt($(this).attr("resize_border"));
        if((resize_border < 1)||(resize_border > 8)) return;
        var width = $(this).width();
        var height = $(this).height();
        var pos = $(this).position();

        if((resize_border == 1)||(resize_border == 2)||(resize_border == 8)) { pos.left += delta_x; delta_x = - delta_x; }
        if((resize_border == 2)||(resize_border == 3)||(resize_border == 4)) { pos.top += delta_y; delta_y = -delta_y; }
        if((resize_border != 3)&&(resize_border != 7)) width += delta_x;
        if((resize_border != 1)&&(resize_border != 5)) height += delta_y;

        windowResize($(this).attr("id"),width,height);
        $(this).offset({ left: pos.left, top: pos.top});
    });

    windowsStates.mouseLastPageX = e.pageX;
    windowsStates.mouseLastPageY = e.pageY;
}

function onWindowTitleMouseDown(e) {
    windowsStates.mouseLastPageX = e.pageX;
    windowsStates.mouseLastPageY = e.pageY;

    $(this).parents(".window-shell").attr("drag_drop","1");
    $(document).bind("mousemove",onMoveWindow);
}

$(document).on("click",".window-shell .window-close",function() {
    windowClose($(this).parents(".window-shell").attr("id"));
});
$(document).on("click",".window-shell .window-minimize",function() {
    windowMinimize($(this).parents(".window-shell").attr("id"));
});

$(document).on("mousedown",".window-shell",function(e) {
    windowsStates.mouseLastPageX = e.pageX;
    windowsStates.mouseLastPageY = e.pageY;

    var resize_border = 0;
    if($(this).attr("resize_border")) resize_border = $(this).attr("resize_border");

    if(resize_border != 0) {
        $(this).attr("resize_window","1");
        $("body").css("cursor",$(this).css("cursor"));
        $(document).bind("mousemove", onWindowResize);
    }

    popupWindow($(this).attr("id"));
});

$(document).on("mouseup",function() {
    $(document).unbind("mousemove",onMoveWindow);
    $(document).unbind("mousemove",onWindowResize);
    $(".window-shell").attr("drag_drop","0");
    $(".window-shell").attr("resize_window","0");
    $("body").css("cursor","default");
});

function onWindowShellMouseMove(e) {
    if($(this).attr("resize_window") == 1) return;
    var width = $(this).width();    //ширина окна
    var height = $(this).height();  //высота окна
    var pos = $(this).position();  //положение окна
    var mouse_x = e.pageX - pos.left;  //вычисляем относительные координаты курсора
    var mouse_y = e.pageY - pos.top;
    var x_border = 0, border = 0;  //информация о границе, на которую наведен курсор (1-левая, 2-левая верхняя, и т.д. по часовой стрелке)
    var border_width = 5; //ширина границы выделения

    if((mouse_x >= 0)&&(mouse_x < border_width)) { x_border = -1; border = 1; }
    else if((mouse_x >= width-border_width)&&(mouse_x < width)) { x_border = 1; border = 5; }
    if((mouse_y >= 0)&&(mouse_y < border_width)) {
        if(x_border == 0) border = 3;
        else if(x_border == -1) border = 2;
        else border = 4;
    } else if((mouse_y >= height-border_width)&&(mouse_y < height)) {
        if(x_border == 0) border = 7;
        else if(x_border == -1) border = 8;
        else border = 6;
    }
    $(this).attr("resize_border",border);

    switch(border) {
        case 1: case 5: $(this).css("cursor","w-resize"); return;
        case 3: case 7: $(this).css("cursor","n-resize"); return;
        case 2: case 6: $(this).css("cursor","nw-resize"); return;
        case 4: case 8: $(this).css("cursor","ne-resize"); return;
    }

    $(this).css("cursor","default");
}

$(document).ready(function() {
    $("body").append('<div id="window-taskbar"></div>');
    $("#window-taskbar").hide();
});
