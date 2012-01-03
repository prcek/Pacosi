<? require('sys_begin.inc'); ?>

<?
//prepare variables
$date=$_GET["date"];
$doc_id=$_GET["doc_id"];

if (!isset($doc_id)) { $doc_id=1; }
if (!isset($date)) { $date = time(); }



?>



<? html_doc_begin("Sablona",0); ?>
<? page_menu("Editace sablony","Template"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>



<div class="post">
<h2 class="title"><? echo getDocName($doc_id) ?></h2><br>
<? writeWDay($date); ?>

<br>
<br>
<br>
<br>
<br>

<form method=get>
<INPUT TYPE="hidden" NAME="date" VALUE="<? echo $date;?>">
<INPUT TYPE="hidden" NAME="auth" VALUE="<? echo $auth;?>">
<INPUT TYPE="hidden" NAME="doc_id" VALUE="<? echo $doc_id;?>">

<select name="l[]" size=33 multiple>
<?
$list = array();

if (isset($_GET["save"])) {
	$list = $_GET["l"];
	$wday = date("w",$date);
	$result = MySQL_Query("DELETE FROM sablona WHERE (doc_id=$doc_id) AND (wday=$wday)");
	foreach($list as $c) {
		$t = sprintf("%02d:%02d:00",($c / 3600),($c % 3600)/60);
		MySQL_Query("INSERT INTO `sablona` ( `doc_id` , `wday` , `casovka` ) VALUES('$doc_id','$wday','$t')");
	}
} else {
	// db read
	$wday = date("w",$date);
	$result = MySQL_Query("SELECT casovka FROM sablona WHERE (`doc_id`=$doc_id) AND (`wday`=$wday)");
	while($rec = MySQL_Fetch_Array($result)) {
		$t = split(':',$rec['casovka']);
		$list[]=$t[0]*3600+$t[1]*60;
	}
}

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

<input type="submit" name="save" value="ulozit sablonu" >
<?
if (isset($_GET["save"])) {
	echo "ulozeno!";
}
?>
</form>




</div>


<? page_content_end(); ?>


<? page_sidebar_begin(); ?>
	<li id="week_calendar"><? writeWeekCalendar($date,("&doc_id=".$doc_id)); ?></li>
	<li id="doctors"><?  writeDoctors($date,0); ?></li>
<? page_sidebar_end(); ?>
<? page_end(); ?>
<? html_doc_end(); ?>

<? require('sys_end.inc'); ?>


