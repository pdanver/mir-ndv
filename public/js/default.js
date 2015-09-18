/**
 * Created by Пользователь on 05.06.15.
 */

window.onbeforeunload = function()
{
    //if(location.href.length > 0)
    //    localStorage.setItem('ref', window.status);
    //else
};

window.onload =  function()
{
    //if(window.name == 'site')
    //    post('/main/test', {href: location.href});
    //else
    window.name = 'site';
    //console.log(window);

    var table = createTable({"parent":"id","id":"tableId","class":["class1","class2"],"name":"tableName","style":"something styling","head":["id","one","two","three"],"body":[["0","1","2","3"],["wq","qwqw","qq","qs2"]],"total":100500,"offset":10,"nav":1});
    document.querySelector('body').appendChild(table);

    var nav = document.querySelectorAll('[offset]');

    for(var el in nav)
        nav[el].onclick = function() { console.log('asgjdhasgdjhgdj'); };

    //post('/main', {userId: 1, userName: "good"});

    var input = document.createElement("input");
    input.type = "date";
    input.id = "date";
    document.querySelector('body').appendChild(input);

    // проверяем браузер пользователя
    if(navigator.userAgent.search(/Firefox/) > -1)
    {
        loadScript("calendar.js", function() { calendar.set("date"); });
        loadScript("calendar.css");
    }
};