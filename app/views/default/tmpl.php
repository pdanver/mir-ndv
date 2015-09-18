<!DOCTYPE html>
<html>
   <head>
      <title>{TITLE}</title>
      <meta charset="utf-8">
      {META}
      {CSS}
      {JS}
      <style>
         header
         {
            background-color: red;
         }
         footer
         {
            background-color: darkgray;
         }
         section:nth-child(2n)
         {
            background-color: green;
         }
         section:nth-child(2n+1)
         {
            background-color: blue;
         }
      </style>
   </head>
   <body>
      <header>{LOGO}{HEADER}</header>
      <section>{CONTENT}{PLACE1}{PLACE2}</section>
      <section>{CONTENT1}{PLACE13}</section>
      <section>{CONTENT2}{PLACE4}{PLACE5}</section>
      <footer>{FOOTER}</footer>
   </body>
</html>