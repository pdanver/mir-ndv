function nospace(str) {
// var VRegExp = new RegExp(/^(\s|\u00A0)+/g);
    var VRegExp = new RegExp(/\s+/g);
    var VResult = str.replace(VRegExp, '');
    return VResult ;
}
function iagentFeedBack(){
    var i =	"Имя";
    var k =	"Название компании";

    if($("#igentFeedBack").val() == 0){
        $("#igentFeedBack").val("1");
        $("#nameFeedBack").attr("placeholder", k);
    }else{
        $("#igentFeedBack").val("0");
        $("#nameFeedBack").attr("placeholder", i);
    }
}
function vakidinvalidFeedBack(thi){
    if($(thi).hasClass("valid")){$(thi).removeClass("valid");}
    if($(thi).hasClass("invalid")){$(thi).removeClass("invalid");}
    if($.trim(thi.value) != ''){
        $(thi).addClass("valid");
    }else{
        $(thi).addClass("invalid");
    }
}

function goFeedBack(){

    var s =	$(".FeedBack option:selected").val();
    var t = $(".FeedBack textarea").val();
    var m = {};
    var f = "";
    if(s >= 0) {m["setid"] = s;}else{ f = "Все поля обязательны к заполнению";}
    if($.trim(t) != '') {m["msg"] = t;}else{ f = "Все поля бязательны к заполнению";}

    $(".FeedBack input").each(function(indx, element){

        if($.trim($(element).val()) == "") {f = "Все поля обязательны к заполнению";}

        var name_element = $(element).attr("name");
        m[name_element] = $(element).val();

    });
    m["request_type"] = 1;

    if(f != ""){
        $(".FeedBack p.error").text(f);
    }else{
        $(".FeedBack p.error").text("");
        $.ajax({
            url: "/FeedBack/ad",
            type: "POST",
            data: m,
            success: function(data){
				getCaptcha();
                var d = JSON.parse(data);
                if (d.f == false) {
                    var gut = '<h1>Спасибо за обращение</h1><p>Наши специалисты свяжутся с вами в ближайшее время</p>';
                    gut += '<p>Ваш контактный код для связи:</p>';
                    gut += '<h2>'+d.msg+'</h2>';
                    $(".FeedBack").html(gut);
                }else
                {
                    $(".FeedBack p.error").text(d.msg);
                }
            }
        });
    }
}

$(document).ready(function(){
    getCaptcha();
    $(".FeedBack").dialog({
        modal: true ,
// position: 'center',
        title: 'Обратная связь',
        show: 'blind' ,
        width:  "auto",
        buttons: {
            "Отправить": function(){ goFeedBack(); },
            "Отмена": function() { $(this).dialog( "close" );}}}
    );
});