<? require('sys_begin.inc'); ?>

<?
//prepare variables
$date=$_GET["date"];
$doc_id=$_GET["doc_id"];

if (!isset($doc_id)) { $doc_id=1; }
if (!isset($date)) { $date = time(); }

?>



<? html_doc_begin("Planovani",0); ?>
<? page_menu("Planovani","Plan"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>

<?

function planWeek($date) {
	echo "Planovani pomoci sablony pro tyden: ";

	$date = cropDate($date);

	$date_begin = getMondayDate($date);
	$date_end = getFridayDate($date)+20*3600;
	echo date("Y-m-d (c)",$date_begin);
	echo " az ";
	echo date("Y-m-d (c)",$date_end);
	echo "<br>";

	// odstranit nevyuzite casovky
	$result = MySQL_Query("DELETE FROM objednavky WHERE (action=0) AND (termin>=$date_begin) AND (termin<=$date_end)");	
	if ($result) {
		echo "nevyuzite casovky odstraneny<br>";
	}

	// natahnout sablonu, prepocitat na aktualni cas
	$result = MySQL_Query("SELECT * FROM sablona");
	$sablona = array();
	while($s_record = MySQL_Fetch_Array($result)) {

        	$t = split(':',$s_record['casovka']);
		$off = ($s_record['wday']-1)*24*3600;
		$d = getdate($date_begin+$off);
		$casovka = mktime($t[0],$t[1],0,$d['mon'],$d['mday'],$d['year']);
		$sablona[] = array("casovka"=>$casovka,"doc_id"=>$s_record['doc_id'],"no"=>0);
	}
	echo "sablona nactena<br>";

	// nedostupne dame na dostupne (a nasledne se to da zpet)
	MySQL_Query("UPDATE objednavky SET doc_avail=1 WHERE (termin>=$date_begin) AND (termin<=$date_end)");

	// vytahnout neprazdne objednavky a promaznout sablonu
	$result = MySQL_Query("SELECT termin, doc_id FROM objednavky WHERE (termin>=$date_begin) AND (termin<=$date_end)");
	while($rec = MySQL_Fetch_Array($result)) {
		$termin = $rec['termin'];
		$doc_id = $rec['doc_id'];
		if (in_array(array("casovka"=>$termin,"doc_id"=>$doc_id, "no"=>0),$sablona)) {
			$key = array_search(array("casovka"=>$termin,"doc_id"=>$doc_id, "no"=>0),$sablona);
			$sablona[$key]["no"]=1;
		} else {
			//doktor nema ordinovat, ale je ma uz neco objednano!
			MySQL_Query("UPDATE objednavky SET doc_avail=0 WHERE (termin=$termin) and (doc_id=$doc_id)");
		}
	}
	echo "nevyuzite casovky zruseny, jiz naplanovane oznaceny za problematicke<br>";
	
	foreach($sablona as $s) {
		if ($s["no"]==0) {
			$termin = $s["casovka"];
			$doc_id = $s["doc_id"];
			if (isholyday($termin)) {
			} else {
				MySQL_Query("INSERT INTO objednavky (termin,doc_id,doc_avail,action) VALUES ('$termin','$doc_id','1','0');");				
			}
		}
	}
	echo "casovky podle sablony zalozeny<br>";
	echo "-- konec planovani tydne --<br>";

}

if (isset($_GET["plan"])) {
	planWeek($date);
} 

if (isset($_GET["multi_plan"])) {
	planWeek($date);
	planWeek($date+7*24*3600);
	planWeek($date+14*24*3600);
	planWeek($date+21*24*3600);
}

?>



<table id="overview" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
  <td width="80" valign=top> <? writeWeekDates(getMondayDate($date),5,1); ?> </td>

<?
	$d_result = MySQL_Query("SELECT id FROM doktori");
	while($d_record = MySQL_Fetch_Array($d_result)) {
                echo "<td width=20></td>\n";
                echo "\t<td valign=top>\n";
// sloupecek doktora
		$doc_id = $d_record['id'];
		$doc_name = getDocName($doc_id);
		$timestamp = getMondayDate($date);
    		echo "<!-- sloupecek doktora $doc_id [$doc_name] na tyden $timestamp -->\n";
    		echo "<table width=\"100%\">\n";
    			echo "\t<tr><td height=\"60\"><h2>".$doc_name."</h2></td></tr>\n";
			echo "\t<tr><td height=\"20\"><h3></h3></td></tr>\n";


			for ($i=0; $i<5; $i++) {
				$day = $timestamp+(86400*$i);
				echo "\t<tr><td height=\"80\">\n";
				$date_array = getdate($day);	
				$day_begin = mktime ( 1 , 0 , 0 , $date_array[mon], $date_array[mday], $date_array[year] );
				$day_end   = mktime ( 23 , 0 , 0 , $date_array[mon], $date_array[mday], $date_array[year] );
				// je alespon jedna objednavka pro dany den a doktora?
				$result = MySQL_Query("SELECT count(*) as pocet FROM objednavky WHERE (doc_id=$doc_id) AND (termin>=$day_begin) AND (termin<=$day_end) AND (doc_avail=1)");
				$record = MySQL_Fetch_Array($result);	
				if ($record['pocet']) {
					echo "Naplanovano<br>";
					echo "<a href=\"detail_plan.php?auth=$auth&date=$day&doc_id=$doc_id\">Editovat</a>";
				} else {
					if (isholyday($day)) {
						echo "Svatek<br>";
					} else {
						echo "Volno<br>";
						echo "<a href=\"detail_plan.php?auth=$auth&date=$day&doc_id=$doc_id\">Editovat</a>";
					}
				}

								
		                //writePlanThumb($doc_id,$day,$week_saved);

			        echo "\t\t</td></tr>\n";
			}

			
    		echo "</table>\n";
// konec sloupecku doktora
                echo "\t</td>\n";
	}
?>
</tr>
</table>



<? page_content_end(); ?>


<? page_sidebar_begin(); ?>
	<li id="calendar"><? writeCalendar($date,""); ?></li>
	<li id="actions">
		<form method=get>
		<INPUT TYPE="hidden" NAME="date" VALUE="<? echo $date;?>">
	      	<INPUT TYPE="hidden" NAME="auth" VALUE="<? echo $auth;?>">

		<h2>Operace</h2>

		<ul>Naplanovat (preplanovat) zvoleny tyden pomoci sablony - <input type="submit" name="plan" value="provest"></ul>
		<ul>Hromadne planovani (4 tydny) od zvoleneho tydne pomoci sablony - <input type="submit" name="multi_plan" value="provest"></ul>


		</form>
	</li>
<? page_sidebar_end(); ?>
<? page_end(); ?>
<? html_doc_end(); ?>

<?

 require('sys_end.inc'); 
?>

