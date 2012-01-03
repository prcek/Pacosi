<? require('sys_begin.inc'); ?>


<?

  if (isset($_GET['logout'])) {

	MySQL_Query("DELETE FROM auth WHERE auth_key='$auth'");
  
        replacePage("login.php");
	die("Přesměrováno");
  }

  if (isset($_GET['back'])) {
        replacePage("index.php?auth=".$auth);
	die("Přesměrováno");
  }

?>

<? html_doc_begin("Odhlášení",0); ?>
<? page_menu("Odhlášení","Logout"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>


<div align=center>

<form method=get>
<input type="hidden" name="auth" value="<? echo $auth; ?>">
opravdu odhlásit? <input type="submit" name="logout" value="Ano"> <input type="submit" name="back" value="Ne">
</form>
</div>


<? page_content_end(); ?>


<? page_sidebar_begin(); ?>
<? page_sidebar_end(); ?>
<? page_end(); ?>
<? html_doc_end(); ?>

<?
  require('sys_end.inc');
?>

