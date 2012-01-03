<? require('sys_begin.inc'); ?>


<? 
  //prepare timestamp
  if (isset($_GET["date"])):
    $date=$_GET["date"];
  else:
    $date= getDayTimestamp(time());
  endif;
?>

<?

function writeDocWeek($doc_id, $timestamp, $days) 
{
    global $auth;

    //get doctor name
    $name = getDocName($doc_id);
    
    // write the column
    echo "<!-- tabulka doktora $doc_id [$name] na tyden $timestamp -->\n";
    echo "<table width=\"100%\">\n";
    echo "\t<tr><td height=\"50\" width=\"100\"><h2>".$name."</h2></td></tr>\n";
    $i = 0;
    while($days) {
        $day = $timestamp+(86400*$i);
        $date = getdate($day);
        if (($date["wday"] != 0) && ($date["wday"] != 6)) 
        {
        	echo "\t<tr><td height=\"50\" align=\"center\" onClick=\"self.location.href='docday.php?date=".$day."&doc_id=".$doc_id."&auth=".$auth."';\">\n";
        	writeDayThumb($doc_id,$day); 
        	echo "\t\t</td></tr>\n";
            $days--;
        }
        $i++;	
    }
 

    echo "</table>\n";
}
?>


<? html_doc_begin("Prehled",0); ?>
<? page_menu("Evidence objednávek","Prehled"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>

	<table id="overview" width="100%" cellpadding="0" cellspacing="0" border="0">
		  <tr>
		    <td width="80" valign=top> <? writeWeekDates(getMondayDate($date),10,0); ?> </td>
            
            <?
            $result = MySQL_Query("SELECT id FROM doktori");
            if (!$result) 
            {
	            echo "Chyba databáze";
	            die;  
            }
            while($record = MySQL_Fetch_Array($result))
            {
                echo "<td width=20></td>\n";
                echo "\t<td valign=top>\n";
                writeDocWeek($record["id"], getMondayDate($date),10);
                echo "\t</td>\n";
            }
            ?>

    	  </tr>
		</table>

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
		Přijmení:<INPUT TYPE="text" NAME="ssurname" onkeyup="capitalise(this);"><br>
		Jméno:<INPUT TYPE="text" NAME="sname" onkeyup="capitalise(this);"><br>
		<input type="submit" name="search" value="hledej"></ul>
		</form>
	</li>
<? page_sidebar_end(); ?>
<? page_end(); ?>
<? html_doc_end(); ?>


<?
require('sys_end.inc');
?>
