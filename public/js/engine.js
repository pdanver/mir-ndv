/**
 * Created by Пользователь on 04.06.15.
 */

function loadScript(filename, success)
{
   var arr = filename.split(".");
   if(arr[1] == "js")
   {
      var fileref = document.createElement('script');
      fileref.setAttribute("type", "text/javascript");
      fileref.setAttribute("src", "/js/" + filename);
   }
   else
      if(arr[1] == "css")
      {
         var fileref = document.createElement("link");
         fileref.setAttribute("rel", "stylesheet");
         fileref.setAttribute("type", "text/css");
         fileref.setAttribute("href", "/css/" + filename);
      }
   if(typeof fileref != "undefined")
   {
      document.getElementsByTagName("head")[0].appendChild(fileref);
      fileref.onload = success;
   }
}
function createTable(data)
{
   var table = document.createElement('table');
   if(typeof data.id !== "undefined")
      table.id = data.id;
   if(typeof data.name !== "undefined")
      table.name = data.name;
   if(typeof data.class !== "undefined")
   {
      if(data.class instanceof Array)
         for(var element in data.class)
            table.className += data.class[element] + ' ';
      else
         if(data.class instanceof String)
            table.className = data.class;
   }
   if(typeof data.style !== "undefined")
   {
      if(data.style instanceof Array)
         for(var element in data.style)
            table.style += data.style[element] + ' ';
      else
         if(data.style instanceof String)
            table.style = data.style;
   }
   if(typeof data.head !== "undefined" && data.head instanceof Array)
   {
      var thead = document.createElement('thead');
      var tr = document.createElement('tr');
      for(var element in data.head)
      {
         var th = document.createElement('th');
         th.innerHTML = data.head[element];
         tr.appendChild(th);
      }
      thead.appendChild(tr);
      table.appendChild(thead);
   }
   if(typeof data.body !== "undefined" && data.body instanceof Array && data.body[0] instanceof Array)
   {
      var tbody = document.createElement('tbody');
      for(var row in data.body)
      {
         var tr = document.createElement('tr');
         for(var element in data.body[row])
         {
            var td = document.createElement('td');
            td.innerHTML = data.body[row][element];
            tr.appendChild(td);
         }
         tbody.appendChild(tr);
         table.appendChild(tbody);
      }
   }
   if(typeof data.total !== "undefined")
   {
      var tfoot = document.createElement('tfoot');
      var tr = document.createElement('tr');
      var td = document.createElement('td');
      td.colSpan = data.body[0].length;
      td.innerHTML = 'total: ' + data.total;
      tr.appendChild(td);
      tfoot.appendChild(tr);
      if(typeof data.nav !== "undefined")
      {
         if(typeof data.offset == "undefined")
            data.offset = 0;
         var tr = document.createElement('tr');
         var td = document.createElement('td');
         td.colSpan = data.body[0].length;
         var pagesCount = Math.ceil(data.total / data.body.length);
         var point = parseInt(data.offset / data.body.length);
         if(data.offset >= 3 * data.body.length)
         {
            var span = document.createElement('span');
            span.setAttribute('offset', 0);
            span.innerHTML = '<span class="nav">&#8676;</span>';
            td.appendChild(span);
         }
         for(var i = 0; i < pagesCount; i++)
            if(i == point)
            {
               var strong = document.createElement('strong');
               strong.innerHTML = i + 1;
               td.appendChild(strong);
            }
            else
               if(i == point - 1)
               {
                  var span = document.createElement('span');
                  span.setAttribute('offset', i * data.body.length);
                  span.innerHTML = '<span class="nav">&#8592;</span>';
                  td.appendChild(span);
               }
               else
                  if(i == point + 1)
                  {
                     var span = document.createElement('span');
                     span.setAttribute('offset', i * data.body.length);
                     span.innerHTML = '<span class="nav">&#8594;</span>';
                     td.appendChild(span);
                  }
         if(point < pagesCount - 2)
         {
            var span = document.createElement('span');
            span.setAttribute('offset', data.total * data.body.length);
            span.innerHTML = '<span class="nav">&#8677;</span>';
            td.appendChild(span);
         }
         tr.appendChild(td);
         tfoot.appendChild(tr);
      }
      table.appendChild(tfoot);
   }
   return table;
}
function ajaxRequest()
{
   var activexmodes = ["Msxml2.XMLHTTP", "Microsoft.XMLHTTP"];
   if(window.ActiveXObject)
   {
      for(var i = 0; i < activexmodes.length; i++)
         try
         { return new ActiveXObject(activexmodes[i]); }
         catch(e)
         { console.log(e); }
   }
   else
      if(window.XMLHttpRequest)
         return new XMLHttpRequest();
      else
         return false;
}
function post(url, params, callback)
{
   var mypostrequest = new ajaxRequest();
   mypostrequest.onreadystatechange = function()
   {
      if(mypostrequest.readyState == 4)
         if(mypostrequest.status == 200 || window.location.href.indexOf("http") == -1)
         {
            if(typeof(callback) === "function")
               try
               {
                  callback(JSON.parse(mypostrequest.responseText));
               }
               catch(e)
               {
                  callback(mypostrequest.responseText);
               }
         }
         else
            console.log('Error: ' + mypostrequest.status);
   };
   mypostrequest.ontimeout = function() { console.log('timeout'); }
   var parameters = "";
   for(var key in params)
      parameters += key + "=" + params[key] + "&";
   parameters = parameters.slice(0, -1);
   mypostrequest.open("POST", url, true);
   mypostrequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
   mypostrequest.send(parameters);
}