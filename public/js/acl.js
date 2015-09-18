var host = "";  //"http://192.168.1.66";
var crossDomainRequest = false;


//обработка нажатия чекбокса checkbox_select_all
function onCheckboxSelectAllClick() {
    var checked = $("#checkbox_select_all").prop("checked");
    $("input[type=checkbox]").prop("checked",checked);
    if(checked) $("#span_select_all").html("Снять все");
    else $("#span_select_all").html("Выбрать все");
}

//функция посылает запрос на сервер для включения и выключения ролей
function setRolesEnable(enable) {
    //подготавливаем массив ролей с нужными значениями enable
    var roles = [];
    $(".role_checkbox input[type=checkbox]:checked").each(function() {
        var role = {
            "name":  $(this).parents(".role").attr("role_name"),
            "enable": enable
        };
        roles.push(role);
    });
    //проверяем массив ролей
    if(roles.length == 0) {
        alert("Роли не выбраны");
        return;
    }
    //делаем запрос
    ajaxRequest("/Acl/set_roles_enable", {
            "roles": roles
        }, "POST",
        function (answer) { //при успешной отработке запроса
            getRoles();  //обновляем список ролей
        });
}

//функция установки
function deleteRoles(enable) {
    var roles = [];
    var confirm_text = "Вы уверены, что хотите удалить следующие роли? \r\n";
    $(".role_checkbox input[type=checkbox]:checked").each(function() {
        var role_name = $(this).parents(".role").attr("role_name");
        confirm_text += " - " + role_name + "\r\n";
        roles.push(role_name);
    });
    //проверяем массив ролей
    if(roles.length == 0) {
        alert("Роли не выбраны");
        return;
    } else if(!confirm(confirm_text)) return;
    //делаем запрос
    ajaxRequest("/Acl/delete_roles", {
            "roles": roles
        },
        "POST",
        function (answer) { //при успешной отработке запроса
            getRoles();
            //for(var i=0;i < roles.length;i++)
            //    alert($('tr[role_name="' + roles[i].name + '"] .role_checkbox input').prop("checked"));
        });
}


//функция посылает запрос на получение списка ролей
function getRoles() {
    //делаем запрос
    ajaxRequest("/Acl/get_roles",null,"POST",
        function(answer) { //при успешной отработке запроса
            //var roles = JSON.parse(answer);  //распарсиваем ответ
            var roles = answer;
            //и формируем код страницы с ролями
            //формируем титл
            var html = '<p><span class="tabs-title">Список ролей</span></p>';
            html += '<table id="acl-tab-roles-roles_table" class="acl-table">';
            //формируем список ролей
            for(var i = 0;i < roles.length;i++) {
                html += '<tr class="role" role_name="' + roles[i].name + '">';
                html += '<td class="role_checkbox"><input type="checkbox"></td>';
                html += '<td class="role_name"><span>' + roles[i].name + '</span></td>'; //onclick="getRolePermissions(\''+roles[i].name+'\');"
                html += '<td class="role_enable ' + ((roles[i].enable == 0)? 'disable':'enable') + '"></td>';
                html += '</tr>';
            }
            html += '<tr>';
            html += '<td><input type="checkbox" id="checkbox_select_all" onclick="onCheckboxSelectAllClick();"></td>';
            html += '<td width="140px"><span id="span_select_all" style="color: grey;">Выбрать все</span></td>'; //onclick="getRolePermissions(\''+roles[i].name+'\');"
            html += '<td></td>';
            html += '</tr>';

            html += '</table><br/>';
            //формируем кнопки:
            //кнопку добавления роли
            html += '<span class="acl-button" id="acl-tab-roles-role_enable" onclick="setRolesEnable(1)">Включить</span>';
            html += '<span class="acl-button" id="acl-tab-roles-role_disable" onclick="setRolesEnable(0)">Выключить</span>';
            html += '<span class="acl-button" id="acl-tab-roles-role_del" onclick="deleteRoles()">Удалить</span>';
            html += '<span class="acl-button" id="acl-tab-roles-role_add" style="margin-left: 20px;" onclick="addRoleFormOpen()">Добавить роль</span>';

            //вставляем код на нужное место страницы
            $("#acl-tab-roles").html(html);
        },null);
}

$(document).ready(function() {

});