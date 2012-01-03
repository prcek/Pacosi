<? require("sys_begin.inc"); ?>

<? 

  //get the data from the database
  $doc_id = $_GET["doc_id"];
  $termin = $_GET["date"];

  $result=MySQL_QUERY("SELECT * FROM objednavky WHERE doc_id=$doc_id AND termin=$termin");
  if (($result) && ($record=MySQL_Fetch_Array($result))) 
  {
    $form_name=$record['name'];
    $form_surname=$record['surname'];
    $form_title=$record['title'];
    $form_birth=($record['birth']=="0000")?"":$record['birth'];
  }
  else
  {
      writeMessagePage("Chybové hlášení","Zvolený termin nelze vytisknout","");
  }

?>


<html>
 <head>
  <title>Tisk objednávky</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
 </head>
 <body onLoad="this.print();">
<div align=center>
<img src=images/logo_clr.jpg width="300"><br>

<br>
<br>
<br>

<? echo $form_title." ".$form_name." ".$form_surname." (".$form_birth.")\n"; ?>
<br>
<br>
<font size=2>
Datum objednávky: <b><? echo date("j.n.Y",$termin) ?></b><br>
Čas objednávky: <b><? echo date("G:i",$termin) ?></b><br>

<br>
PROSÍME O PŘÍCHOD 15 MIN. DŘÍVE NEŽ JE UVEDENÝ<br>ČAS OBJEDNÁVKY K LÉKAŘI.<br>
<br>
PŘED VSTUPEM DO ORDINACE JE POTŘEBA SE NAHLÁSIT<br>
V EVIDENCI K ZAPSÁNÍ DO AMBULANTNÍ KARTY<br>
A K ZAPLACENÍ REGULAČNÍHO POPLATKU 30,-Kč.<br>
<br>

<b><? echo getDocName($doc_id); ?></b>
<br>
<br>
S sebou poukaz na vyšetření/ošetření, pojišťovací kartičku,<br>
zdravotní dokumentaci k diagnoze.
</font>

<br>
<br>
<br>
<br>
<br>
<font size=1>
<?
     $a = split("/",$_SERVER["REQUEST_URI"]);
     if ($a[1] == "pacosi") {
?>
MUDr. Antonín Koukal, spol. s r.o., Viniční 235, 615 00 Brno, tel.: 533 306 376,<br>
fax.: 533 306 132, e-mail: info@clr.cz, http://www.clr.cz, IČ: 60731842, IČZ: 72678000<br>
<?     } else if (($a[1] == "clr") && ($a[2]=="pacosi2")) { ?>
MUDr. Antonín Koukal, spol. s r.o., Dobrovského 23, 612 00 Brno, tel.:541 425 260<br>
e-mail: info@clr.cz, http://www.clr.cz<br>
<?     } ?>
</font>
</div>
 </body>
</html>

<?
require("sys_end.inc");
?>
