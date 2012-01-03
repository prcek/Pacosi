<? require("sys_begin.inc"); ?>

<? 

  //get the data from the database
  $doc_id = $_GET["doc_id"];
  $termin = $_GET["date"];

?>


<html>
 <head>
  <title>Tisk denního přehledu</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
 </head>
 <body onLoad="this.print();">

    <h2><? echo getDocName($doc_id); ?></h2>
    <b>Datum:</b> <? echo date("j.n.Y",$termin) ?><br><br>

    <table>
        <tr>
            <td width=80><b>Čas</b></td><td width=200><b>Pacient</b></td><td width=100><b>Akce</b></td><td><b>Poznámka</b></td>
        </tr>

        <?
        global $actions;

        $result = MySQL_Query("SELECT * FROM objednavky WHERE doc_id=$doc_id AND $termin<=termin AND termin<$termin+60*60*24 ORDER BY termin;");
        if (!$result)
        {
            die("chyba databaze (SELECT * FROM objednavky WHERE doc_id=$doc_id AND $termin<=termin AND termin<$date+60*60*24 ORDER BY termin;)");
        }

        while ($record = MySQL_Fetch_Array($result))
        {
            $termin = $record['termin'];
            $name   = $record['title']." ".$record['name']." ".$record['surname']." (".$record['birth'].") ";
            $action = $record['action'];
	    $poznamka = $record['note'];
            $time_str=date("H:i", $termin);
            if (!$action)
            {
                continue;
            }

            
            echo "\t\t<tr>\n";
            echo "\t\t\t<td>".$time_str."</td><td>".$name."</td><td>".$actions[$action]."</td><td>".$poznamka."</td>\n";
            echo "\t\t</tr>\n";
        }

        ?>
    </table>

 </body>
</html>

<? require("sys_end.inc"); ?>

