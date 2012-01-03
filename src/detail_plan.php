<? require('sys_begin.inc'); ?>

<?
//prepare variables
$date=$_GET["date"];
$doc_id=$_GET["doc_id"];

if (!isset($doc_id)) { $doc_id=1; }
if (!isset($date)) { $date = time(); }



?>



<? html_doc_begin("Detailni planovani",0); ?>
<? page_menu("Detailni planovani","DetailPlan"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>



<div class="post">
<h2 class="title"><? echo getDocName($doc_id) ?></h2><br>
<? writeDate($date); ?>
<? 
	if (isholyday($date)) {
		echo "<span style=\"font-size:20px; color:#FF0000;\"> Svátek! </span>";
	}
?>


<br>
<br>
<br>
<br>
<br>

<form method=get>
<INPUT TYPE="hidden" NAME="date" VALUE="<? echo $date;?>">
<INPUT TYPE="hidden" NAME="auth" VALUE="<? echo $auth;?>">
<INPUT TYPE="hidden" NAME="doc_id" VALUE="<? echo $doc_id;?>">

<?
$list = array();

if (isset($_GET["save"])) {
// opravit ordinacni dobu 
	$list = $_GET["l"];
	$date = cropDate($date);
	$date_begin = $date;
	$date_end = $date+20*3600;

	echo date("Y-m-d (c)",$date_begin);
	echo " az ";
	echo date("Y-m-d (c)",$date_end);
	echo "<br>";


	// odstranit nevyuzite casovky
	$result = MySQL_Query("DELETE FROM objednavky WHERE (action=0) AND (termin>=$date_begin) AND (termin<=$date_end) AND (doc_id=$doc_id)");	
	if ($result) {
		echo "nevyuzite casovky odstraneny<br>";
	}
	// z "list" udelame absolutni sablonu
	$sablona = array();
	foreach($list as $l) {
		$t[0]=intval($l/3600);
		$t[1]=intval(($l % 3600) / 60);
		$d = getdate($date_begin);
		$casovka = mktime($t[0],$t[1],0,$d['mon'],$d['mday'],$d['year']);
		$sablona[] = array("casovka"=>$casovka,"doc_id"=>$doc_id,"no"=>0);
	}
	echo "casovky pro den vyrobeny<br>";

	// nedostupne dame na dostupne (a nasledne se to da zpet)
	MySQL_Query("UPDATE objednavky SET doc_avail=1 WHERE (termin>=$date_begin) AND (termin<=$date_end) AND (doc_id=$doc_id)");

	// vytahnout neprazdne objednavky a promaznout sablonu
	$result = MySQL_Query("SELECT termin, doc_id FROM objednavky WHERE (termin>=$date_begin) AND (termin<=$date_end) AND (doc_id=$doc_id)");
	while($rec = MySQL_Fetch_Array($result)) {
		$termin = $rec['termin'];
		$doc_id = $rec['doc_id'];
		if (in_array(array("casovka"=>$termin,"doc_id"=>$doc_id, "no"=>0),$sablona)) {
			$key = array_search(array("casovka"=>$termin,"doc_id"=>$doc_id, "no"=>0),$sablona);
			$sablona[$key]["no"]=1;
		} else {
			//neni to extra casovka???
			if (isTerminExtra($termin)) {
				$termin_pred = $termin - 10*60;
				$termin_po = $termin + 10*60;
				//existuje radna casovka pred a po ?
				if (
				  (      in_array(array("casovka"=>$termin_pred,"doc_id"=>$doc_id, "no"=>0),$sablona) 
				 	|| 
					in_array(array("casovka"=>$termin_pred,"doc_id"=>$doc_id, "no"=>1),$sablona) 
				  ) 
				  &&  
				  (
				  	in_array(array("casovka"=>$termin_po,"doc_id"=>$doc_id, "no"=>0),$sablona) 
					|| 
					in_array(array("casovka"=>$termin_po,"doc_id"=>$doc_id, "no"=>1),$sablona) 
				  ) 
				  ) {

				  } else {
				  	echo "Extra je mimo ordinacni dobu!<br>";

					MySQL_Query("UPDATE objednavky SET doc_avail=0 WHERE (termin=$termin) and (doc_id=$doc_id)");
				  }
				
			} else {
				//doktor nema ordinovat, ale je ma uz neco objednano!
				echo "Radna casovka je mimo ordinacni dobu!<br>";
				MySQL_Query("UPDATE objednavky SET doc_avail=0 WHERE (termin=$termin) and (doc_id=$doc_id)");
			}
		}
	}
	echo "nevyuzite casovky zruseny, jiz naplanovane oznaceny za problematicke<br>";
	
	foreach($sablona as $s) {
		if ($s["no"]==0) {
			$termin = $s["casovka"];
			$doc_id = $s["doc_id"];
			MySQL_Query("INSERT INTO objednavky (termin,doc_id,doc_avail,action) VALUES ('$termin','$doc_id','1','0');");				
		}
	}
	echo "casovky podle sablony zalozeny<br>";
	echo "-- konec planovani tydne --<br>";



} else if (isset($_GET["load"])) {
// nacteni z sablony
	$wday = date("w",$date);
	$result = MySQL_Query("SELECT casovka FROM sablona WHERE (`doc_id`=$doc_id) AND (`wday`=$wday)");
	while($rec = MySQL_Fetch_Array($result)) {
		$t = split(':',$rec['casovka']);
		$list[]=$t[0]*3600+$t[1]*60;
	}
} else { // cokoli nebo show
// nacteni z objednavek
	$date = cropDate($date);
	$date_begin = $date;
	$date_end = $date+20*3600;

	$result = MySQL_Query("SELECT termin FROM objednavky WHERE (termin>=$date_begin) AND (termin<=$date_end) AND (doc_id=$doc_id)");
        while($rec = MySQL_Fetch_Array($result)) {
		$t = getdate($rec['termin']);		
		$list[] = $t['hours']*3600+$t['minutes']*60;
	}


}
?>
<select name="l[]" size=33 multiple>
<?
//7:00 az 18:00
for($t = 7*60*60; $t<18*60*60; $t+=60*20)  {
	$l = sprintf("%02d:%02d - %02d:%02d",($t / 3600),($t % 3600)/60,($t+(60*20))/3600,(($t+(60*20)) % 3600)/60);
	if (in_array($t,$list)) {
		echo "<option value=$t selected>$l</option>";
	} else {
		echo "<option value=$t>$l</option>";
	}
}
?>
</select>

<input type="submit" name="save" value="ulozit ordinacni dobu" >
<input type="submit" name="load" value="nacist sablonu" >
<input type="submit" name="show" value="obnovit" >

<?
if (isset($_GET["save"])) {
	echo "ulozeno!";
}
?>
</form>




</div>


<? page_content_end(); ?>


<? page_sidebar_begin(); ?>
	<li id="calendar"><? writeCalendar($date,("&doc_id=".$doc_id)); ?></li>
	<li id="doctors"><?  writeDoctors($date,0); ?></li>
<? page_sidebar_end(); ?>
<? page_end(); ?>
<? html_doc_end(); ?>

<?
require("sys_end.inc");
?>


