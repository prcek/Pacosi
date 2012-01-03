<? require("sys_begin.inc"); ?>


<?
//prepare variables
$date=$_GET["date"];
$doc_id=$_GET["doc_id"];

if (!isset($doc_id)) { $doc_id=1; }
if (!isset($date)) { $date = time(); }



?>



<? html_doc_begin("Nastaveni",0); ?>
<? page_menu("Nastaveni","Settings"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>





<? page_content_end(); ?>


<? page_sidebar_begin(); ?>
<? page_sidebar_end(); ?>
<? page_end(); ?>
<? html_doc_end(); ?>

<?
require("sys_end.inc");
?>

