<? require("sys_begin.inc"); ?>


<?
//prepare variables

$pass = 0;

if (isset($_POST['pass'])) {
	$p1 = $_POST['password1'];	
	$p2 = $_POST['password2'];	
	$p = $_POST['password'];
	// check aktualniho
	$result = MySQL_Query("SELECT count(*) as ok FROM uzivatele WHERE login='$auth_name' and password='$p'");
	if (! $result) {
		$pass=2;
		die("chyba databáze".mysql_error());
	}

	$record = MySQL_Fetch_Array($result);
	if ($record['ok']==1) {
		// je-li nove ok, pak zmenit
		if ($p1 == $p2) {
			MySQL_Query("UPDATE uzivatele SET password='$p1' WHERE login='$auth_name'");
			$pass=1;
		}
	} else {
		$pass=2;
	}
}


?>



<? html_doc_begin("Nastavení",0); ?>
<? page_menu("Nastavení","User"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>


<? 
if ($pass == 1) {
    echo "Heslo změněno<br><br>";
} else {
    if ($pass == 2) { echo "Aktualní heslo nesouhlasí nebo nová nejsou stejná<br>"; }
?>
<form method=post>
Změna hesla uživatele "<? echo $auth_name; ?>"<br>
aktuální:<input type="password" name="password" value=""><br>
nové:<input type="password" name="password1" value=""><br>
nové:<input type="password" name="password2" value=""><br>
<input type="submit" name="pass" value="změnit">
</form>

<? } ?>


<? page_content_end(); ?>


<? page_sidebar_begin(); ?>
<? page_sidebar_end(); ?>
<? page_end(); ?>
<? html_doc_end(); ?>

<?
require("sys_end.inc");
?>

