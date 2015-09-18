/**
 * Created by Пользователь on 04.06.15.
 */

window.onload = function()
{
   var elements = document.querySelectorAll('input,select');
   for(var i = 0; i < elements.length; i++)
      if(elements[i].type == 'checkbox')
         elements[i].onclick = function() { adminPost(this.id, this.checked); };
      else
         if(elements[i].tagName === 'SELECT')
            elements[i].onchange = function() { adminPost(this.id, this.value); };
};
function adminPost(id, val) { post('/administrator/change', {id: id, st: val}, function(data)
{
   addNotify(data.type, data.cnt); });
};
function addNotify(type, text)
{
   var not = document.createElement('div');
   type = typeof type !== 'undefined' ?  type : 'info';

   var img = document.createElement('img');
   img.src = 'http://'+location.host+'/img/icons/'+type+'_icon.png';
   img.alt = type;
   not.appendChild(img);

   not.innerHTML += ' '+text;
   not.className = "icon one";

   var body = document.getElementById('notify');
   var br = document.createElement('br');
   body.appendChild(not);
   body.appendChild(br);

   setTimeout(function()
   {
      not.className = "icon five";
      setTimeout(function()
      {
         body.removeChild(not);
         body.removeChild(br);
      }, 1000);
   }, 5000);
}