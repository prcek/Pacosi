<? require('sys_begin.inc'); ?>


<?
//prepare variables
//$date=$_GET["date"];
//$doc_id=$_GET["doc_id"];

//if (!isset($doc_id)) { $doc_id=1; }
//if (!isset($date)) { $date = time(); }



?>



<? html_doc_begin("Nastaveni svatku",0); ?>
<? page_menu("Nastaveni svatku","Svatky"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>
<div class="post">


<br>
<?
if (isset($_GET["add"])) {
	$d = $_GET["r"]."-".$_GET["m"]."-".$_GET["d"];
	echo "pridat $d<BR>";
	$q = "INSERT INTO `svatky` (`datum`,`popis`) VALUES ('$d','')";
	MySQL_Query($q);
}

if (isset($_GET["del"])) {
	$l = $_GET["s"];	
	foreach($l as $d) {
		echo "smazat $d<br>";
		MySQL_Query("DELETE FROM svatky WHERE `datum`='$d'");
	}
}

?>
<br>
<form method=get>
<INPUT TYPE="hidden" NAME="auth" VALUE="<? echo $auth;?>">
<table border=0>
<tr>
<td>
svatky:<br>
<select name="s[]", size=10 multiple>
<?
$list = array();
$result = MySQL_Query("SELECT datum FROM svatky ORDER BY datum");
while($rec = MySQL_Fetch_Array($result)) {
	$d = $rec['datum'];
	echo "<option value=$d>$d</option>";
}
?>
</select>
<br>
<input type="submit" name="del" value="smazat oznacene">
</td>
<td>
<select name=r> <?  for($i=2010; $i<2020; $i++) { echo "<option value=$i>$i&nbsp&nbsp</option>"; } ?> </select>
<select name=m> <?  for($i=1; $i<=12; $i++) { echo "<option value=$i>$i&nbsp&nbsp</option>"; } ?> </select>
<select name=d> <?  for($i=1; $i<=31; $i++) { echo "<option value=$i>$i&nbsp&nbsp</option>"; } ?> </select>

<input type="submit" name="add" value="pridat">
</td>
</tr>
</table>
</form>
</div>
<? page_content_end(); ?>


<? page_sidebar_begin(); ?>
<? page_sidebar_end(); ?>
<? page_end(); ?>
<? html_doc_end(); ?>

<?
require("sys_end.inc");
?>

