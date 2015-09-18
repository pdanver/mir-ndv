/**
 * Создал Казаков А.Ф. 07.06.15.
 *
 * Канвас сценарий, рисующий в центре экрана галактику
 */

window.onload = function() { draw(); }

var canvas = document.querySelector('canvas');
var ctx = canvas.getContext('2d');
canvas.width = innerWidth;
canvas.height = innerHeight;

var rotation = 0.35;
var speed = 0.005;

var starsPerArm = 10000; //Количество точек вокруг центра
var starsX = [];
var starsY = [];
var mod = [];
var stepSize = 2 / starsPerArm;
var starDispersion = 9;
var galaxyTilt = 0.6;
var galaxyRadius = 500; //радиус, как и написано
var centerX = innerWidth / 2;
var centerY = innerHeight / 2;

var requestAnimFrame = function()
{
   return (
       window.requestAnimationFrame(draw) ||
       window.webkitRequestAnimationFrame(draw) ||
       window.mozRequestAnimationFrame(draw) ||
       window.oRequestAnimationFrame(draw) ||
       window.msRequestAnimationFrame(draw)
   );
};

window.resize = function()
{
   canvas.width = innerWidth;
   canvas.height = innerHeight;
   centerX = innerWidth / 2;
   centerY = innerHeight / 2;
};

function rotate()
{
   if (rotation + speed >= 2)
      rotation = 0;
   else
      rotation += speed;
}

function createGalaxy()
{
   for(var i = 0; i < starsPerArm; i++)
   {
      starsX[i] = Math.random() * (starDispersion * 2);
      starsY[i] = Math.random() * (starDispersion * 2);
      mod[i] = Math.random() * (starDispersion / 2)
   }
}

createGalaxy();

function draw()
{
   ctx.clearRect(0, 0, innerWidth, innerHeight);

   var fog1 = ctx.createRadialGradient(centerX, centerY, 0, centerX, centerY, galaxyRadius);
   fog1.addColorStop(0, 'rgba(255,255,255,0.5)');
   fog1.addColorStop(1, 'rgba(255,255,255,0)');
   ctx.fillStyle = fog1;
   ctx.fillRect(0, 0, innerWidth, innerHeight);

   ctx.fillStyle = '#fff';

   for(var i = 0; i < 2; i += stepSize + (stepSize * i))
   {
      var index = Math.floor(i / stepSize);
      var angle1 = Math.PI * (2 * i + rotation);
      var angle2 = Math.PI * (2 * i + rotation + 1);
      var angle3 = Math.PI * (2 * i + rotation + 0.5);
      var angle4 = Math.PI * (2 * i + rotation + 1.5);
      var modSq = mod[index] * mod[index];
      var x1 = centerX + starsX[index] + Math.cos(angle1 + galaxyTilt) * (galaxyRadius * (i / 2));
      var y1 = centerY + starsY[index] + Math.sin(angle1) * ((galaxyRadius / 4) * (i / 2));
      var x2 = centerX + starsX[index] + Math.cos(angle2 + galaxyTilt) * (galaxyRadius * (i / 2));
      var y2 = centerY + starsY[index] + Math.sin(angle2) * ((galaxyRadius / 4) * (i / 2));
      var x3 = centerX + starsX[index] + Math.cos(angle3 + galaxyTilt) * (galaxyRadius * (i / 2));
      var y3 = centerY + starsY[index] + Math.sin(angle3) * ((galaxyRadius / 4) * (i / 2));
      var x4 = centerX + starsX[index] + Math.cos(angle4 + galaxyTilt) * (galaxyRadius * (i / 2));
      var y4 = centerY + starsY[index] + Math.sin(angle4) * ((galaxyRadius / 4) * (i / 2));
      var size = mod[index] * 0.66 - i;

      ctx.fillRect(x1, y1, size, size);
      ctx.fillRect(x2, y2, size, size);
      ctx.fillRect(x3, y3, size, size);
      ctx.fillRect(x4, y4, size, size);
      ctx.fillRect(x1 + modSq, y1 + modSq, 0.5 + Math.random(), 0.5 + Math.random());
      ctx.fillRect(x2 + modSq, y2 + modSq, 0.5 + Math.random(), 0.5 + Math.random());
      ctx.fillRect(x3 + modSq, y3 + modSq, 0.5 + Math.random(), 0.5 + Math.random());
   }

   var core = ctx.createRadialGradient(centerX + starDispersion, centerY + starDispersion, 0, centerX + starDispersion, centerY + starDispersion, galaxyRadius / 8);
   core.addColorStop(0.5, 'rgba(255, 255, 255, 1)');
   core.addColorStop(0.8, 'rgba(255, 255, 255, 0.8)');
   core.addColorStop(1, 'rgba(255, 255, 255, 0)');
   ctx.fillStyle = core;
   ctx.beginPath();
   ctx.arc(centerX + starDispersion, centerY + starDispersion, galaxyRadius / 8, 0, Math.PI * 2, true);
   ctx.fill();

   rotate();
   requestAnimFrame(draw);
}