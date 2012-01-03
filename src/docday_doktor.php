<? require("sys_begin.inc"); ?>

<? 
  //prepare variables
  $date=$_GET["date"];
  if (!isset($date)) {
  	 $date= getDayTimestamp(time());
  }
  $doc_id=$auth_selector;

?>

<? html_doc_begin("Den doktora",300); ?>
<? page_menu("Den doktora","Doktor"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>


		<div class="post">
			<h2 class="title"><? echo getDocName($doc_id) ?></h2><br>
			<? writeDate($date); ?>
			<div class="entry">
				<table width="280">
					<?

                    $stateColor = array (
                        -2=> "red",
                        -1=> "Gray",
                        0=> "Green",
                        1=> "Purple",
                        2=> "Purple",
                        3=> "Blue"
                    );
                    
                    $result = MySQL_Query("SELECT * FROM objednavky WHERE doc_id=$doc_id AND $date<=termin AND termin<$date+60*60*24 ORDER BY termin;");
                    if (!$result)
                    {
                        die("chyba databaze");
                    }

                    if ($rows=mysql_num_rows($result))
                    {
                      while ($record = MySQL_Fetch_Array($result))
                      {
                        $termin = $record['termin'];                       
                        $action = $record['action'];
                        $block  = $record['block'];
                        $state  = $action;
                        if ((time()-$block)<5*60) 
                        {
                            $state=-1;
                        }
                        if ($record['doc_avail']==0)
                        {
                            $state=-2;
                        }
                        $time_str=date("H:i", $termin);
                        switch ($state)
                        {
                            case -1: $name = "blokováno: ".$record['name']." ".$record['surname'];break;
                            case  0: $name = "volno";break;
                            default: $name =  $record['name']." ".$record['surname'];break;

                        }
                        //$name = ($action>0) ? $record['name']." ".$record['surname'] : "volno";
 
                        echo "\t\t<tr>\n";
                        echo "\t\t\t<td width=\"80\">".$time_str."</td>\n";
						echo "\t\t\t<td bgcolor=\"".$stateColor[$state]."\" align=center >".$name."</td>\n";
                        echo "\t\t</td>\n";
                      }
                    }
                    else 
                    { 
                        echo "Lékař v tento den neordinuje<br><br><br><br>";
                    }


                    ?>
                    
				</table>
				
			</div>			
			<br>

            <?

            //link "tisknout"
            echo "<p class=\"links\">\n";
            echo "<a href=\"print_docday.php?date=".$date."&doc_id=".$doc_id."&auth=".$auth;
            echo "\" class=\"more\" target=\"_new\">Vytisknout tento seznam</a>";
            echo "</p>\n";


            if (($rows>0) && ($auth_role==1))
            {
                echo "<p class=\"links\">\n";				
                echo "\t<a href=\"order.php?date=".$date."&vip=1&doc_id=".$doc_id."&auth=".$auth."\" class=\"more\">Extra objednavka</a>\n";
                echo "</p>\n";
            }

            if  ($auth_role==1)
            {
                echo "<p class=\"links\">\n";
				echo "<a href=\"overview.php?date=".$date."&auth=".$auth."\" class=\"more\">Zpět na přehled</a>\n";
                echo "</p>\n";

            }
            ?>
				


		</div>



	</div>
	<div id="sidebar">
		<ul>
            <li id="calendar"><? writeCalendar($date,("&doc_id=".$doc_id) ); ?></li>
            <?
            if ($auth_role==1)
            {
                echo "<li id=\"doctors\">\n";
                writeDoctors($date,1);
                echo "</li>\n";
            }
            ?>

		</ul>
	</div>
     </div>
  </body>
</html>


<?
require("sys_end.inc");
?>
