<? require('sys_begin.inc'); ?>


<? 
  //prepare timestamp
  if (isset($_GET["date"])):
    $date=$_GET["date"];
  else:
    $date= getDayTimestamp(time());
  endif;

  $date = cropDate($date);
?>



<? html_doc_begin("Hledat",0); ?>
<? page_menu("Evidence objednávek - hledat","Hledat"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>

<div class='post'>
<? if (isset($_GET["search"]) && isset($_GET["sname"]) && isset($_GET["ssurname"])) {



	$sname = $_GET["sname"];
	$ssurname = $_GET["ssurname"];
	if (strlen($sname . $ssurname)>0) {
		echo "<h2>Vyhledávaní objednávek od ". date("Y-m-d",$date) ." podle: ";	

		$q="1";

		if (strlen($ssurname)>0) {
			echo "přijmení='".$ssurname."' ";
			$q.=" and `surname` LIKE '".$ssurname."'";
		} 
		if (strlen($sname)>0) {
			echo "jméno='".$sname."' ";
			$q.=" and `name` LIKE '".$sname."'";
		} 
		echo "</h2>\n";

		
    		$result = MySQL_Query("SELECT action,termin, doc_id, name, surname FROM objednavky WHERE (termin>=$date) and action!=0 and ($q) ORDER BY termin ASC LIMIT 0,30");
		$af=0;
	        while($rec = MySQL_Fetch_Array($result)) {
			$af=1;
			$action = $rec['action'];
	                $termin = $rec['termin'];
	                $doc_id = $rec['doc_id'];
			$name = $rec['name'];
			$surname = $rec['surname'];
			echo "<div>".date("Y-m-d H:i",$termin).", ".getDocName($doc_id).", ". $actions[$action] ." - ".$surname." ".$name."</div>\n";
		#	echo "<div>Přijmení = $surname, jméno = $name, doktor = ".getDocName($doc_id).", termín = ".date("Y-m-d H:i",$termin)."</div>\n";
	        }
		if (!$af) {
			echo "Žádné nalezené záznamy.";
		}



	} else {
		echo "<h2>Jméno nebo přijmení musí být vyplněno.</h2>";
	}
} else {
	echo "<h2>Zadej přijmení nebo/i jméno ve vyhledávacím folmuláři.</h2>";
}
?>
</div>

<? page_content_end(); ?>


<? page_sidebar_begin(); ?>
	<li id="calendar"><? writeCalendar($date,""); ?></li>
	<li id="actions">
	
<? require("jsfuncs.inc"); ?>
		<form action="search.php" method=get>
		<INPUT TYPE="hidden" NAME="date" VALUE="<? echo $date;?>">
	    <INPUT TYPE="hidden" NAME="auth" VALUE="<? echo $auth;?>">

		<h2>Vyhledávaní od <? echo date("Y-m-d", $date) ?></h2>
		<ul>
		Přijmení:<INPUT TYPE="text" NAME="ssurname"  onkeyup="capitalise(this);" ><br>
		Jméno:<INPUT TYPE="text" NAME="sname" onkeyup="capitalise(this);" ><br>
		<input type="submit" name="search" value="hledej"></ul>
		</form>
	</li>
<? page_sidebar_end(); ?>
<? page_end(); ?>
<? html_doc_end(); ?>


<?
require('sys_end.inc');
?>
