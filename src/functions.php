<?
require('sys_config.php');

$czech_months = array (
            1=> "Leden",
            	"Únor",
		        "Březen",
		        "Duben",
		        "Květen",
		        "Červen",
		        "Červenec",
		        "Srpen",
		        "Září",
		        "Říjen",
		        "Listopad",
		        "Prosinec"
    		);
$czech_days = array ( 0=>"Ne", 1=>"Po", 2=>"Út", 3=>"St", 4=>"Čt", 5=>"Pá", 6=>"So" );

$actions = array (
            0=> "<nezadáno>",
            1=> "Vstupní vyšetření",
                "Znovuvstupní vyšetření",
                "Kontrolní vyšetření"
            );

function replacePage($href)
{
    //echo "<HTML>\n\t<HEAD>\n\t</HEAD>\n\t<BODY onload=\"parent.location.replace('".$href."')\">\n\t</BODY>\n</HTML>";
    //echo "<HTML>\n\t<HEAD>\n\t</HEAD>\n\t<BODY onload=\"self.location.href='".$href."';\">\n\t</BODY>\n</HTML>";

     writeMessagePage("","Přesměrováno", "onload=\"parent.location.replace('".$href."')\"");
}


function isTerminExtra($termin)
{
    $minutes=intval(date("i", $termin));
    return (($minutes%20)!=0);
}

function cropDate($date) {
        $a = getdate($date);
	        return mktime(2,0,0,$a['mon'],$a['mday'],$a['year']);
}

function getDayTimestamp($timestamp)
{
    $date = getdate($timestamp);
    $ts   = mktime (1,1,1, $date["mon"], $date["mday"] , $date["year"] );
    return $ts;
}


function isholyday($t) {
    $date = getdate($t);
    $d = $date["year"]."-".$date["mon"]."-".$date["mday"];
    $result = MySQL_Query("SELECT datum FROM svatky WHERE datum='$d'");
    return mysql_num_rows ($result);
}

function writeMessagePage($title,$message,$body)
{
    echo "<html>\n";
    echo "\t<head>\n";
    echo "\t\t<title>Evidence</title>\n";
    echo "\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n";
    echo "\t\t<link rel=\"stylesheet\" href=\"default.css\">\n";
    echo "\t</head>\n";
    echo "\t<body ".$body.">\n";
    echo "\t\t<div id=\"header\">\n";
    echo "\t\t\t<div id=\"logo\">\n";
    echo "\t\t\t\t<span class=\"system_label\">Evidence objednávek</span>\n";
    echo "\t\t\t\t<span class=\"action_label\">".$title."</span>\n";
    echo "\t\t\t</div>\n";
    echo "\t\t</div>\n";
    echo "\t\t<div id=\"page\" align=\"center\">\n";
    echo "\t\t\t<br><br><br><br><br><br><br><br><br><br>\n";
    echo "\t\t\t<h3>".$message."</h3>\n";
    echo "\t\t</div>\n";
    echo "\t</body>\n";
    echo "</html>\n";
    die;
}


function writeMenu($highlight,$date)
{
    global $auth;
    global $auth_role;
    global $auth_selector;

    echo "\t<div id=\"menu\">\n";
    echo "\t\t<ul>\n";
    if ($auth_role==1) //root
    {
        echo "\t\t\t<li ".(($highlight=="Prehled")?"class=\"current_page_item\"":"")."><a href=\"overview.php?date=".getDayTimestamp($date)."&auth=".$auth."\">Přehled</a></li>\n";
        echo "\t\t\t<li ".(($highlight=="Planovani")?"class=\"current_page_item\"":"")."><a href=\"plan.php?date=".getDayTimestamp($date)."&auth=".$auth."\">Plánování</a></li>\n";
        echo "\t\t\t<li ".(($highlight=="Hledat")?"class=\"current_page_item\"":"")."><a href=\"search.php?date=".getDayTimestamp($date)."&auth=".$auth."\">Hledat</a></li>\n";
    }
    else if ($auth_role==3)  //evidence
    {
        echo "\t\t\t<li ".(($highlight=="Prehled")?"class=\"current_page_item\"":"")."><a href=\"overview.php?date=".getDayTimestamp($date)."&auth=".$auth."\">Přehled</a></li>\n";
        echo "\t\t\t<li ".(($highlight=="Hledat")?"class=\"current_page_item\"":"")."><a href=\"search.php?date=".getDayTimestamp($date)."&auth=".$auth."\">Hledat</a></li>\n";
    }
    else //doktor
    {
        echo "\t\t\t<li ".(($highlight=="Prehled")?"class=\"current_page_item\"":"")."><a href=\"docday.php?date=".getDayTimestamp($date)."&doc_id=".$auth_selector."&auth=".$auth."\">Přehled</a></li>\n";
    }
    echo "\t\t</ul>\n";
    echo "\t</div>\n";
}

function writeDate($timestamp)
{
    global $czech_months;
    global $czech_days;
    $date = getdate($timestamp);

    echo "\t\t<p class=\"date\">\n";
    echo "\t\t\t<span class=\"month\">".$czech_months[$date["mon"]]."</span>\n";
    echo "\t\t\t<span class=\"wday\">".$czech_days[$date["wday"]]."</span>\n";
    echo "\t\t\t<span class=\"day\">".$date["mday"]."</span>\n";
    echo "\t\t\t<span class=\"year\">, ".$date["year"]."</span>\n";
    echo "\t\t</p>\n";
}

function writeWDay($timestamp) 
{
    global $czech_days;
    $date = getdate($timestamp);
    echo "\t\t<p class=\"date\">\n";
    echo "\t\t\t<span class=\"month\"></span>\n";
    echo "\t\t\t<span class=\"wday\"></span>\n";
    echo "\t\t\t<span class=\"day\">".$czech_days[$date["wday"]]."</span>\n";
    echo "\t\t\t<span class=\"year\"></span>\n";
    echo "\t\t</p>\n";

}

function writeWeekDates($timestamp,$days,$plan)
{
    if ($plan) { $h = 80; } else {$h = 50;}
    echo "<!-- tabulka datumu -->\n";
    echo "<table width=80>\n";
    echo "\t<tr><td width=\"80\" height=\"$h\"></td></tr>\n";
    $i = 0;
    while($days) {
    	$date = getdate($timestamp+(86400*$i));
	if (($date["wday"] != 0) && ($date["wday"] != 6)) {
	        echo "\t<tr><td width=\"80\" height=\"$h\">\n";
	        writeDate($timestamp+(86400*$i));
	        echo "\t\t</td></tr>\n";
		$days--;
	} 
	$i++;	
    }
 
    echo "</table>\n";

}



function writeDoctors($date,$thumb) {

    global $auth;

	echo "<h2>Doktori</h2>";

	$result = MySQL_Query("SELECT * FROM doktori");
	if (!$result)
	{
		echo "Chyba databáze";
		die;
	}

    echo "<br>\n";

    while($record = MySQL_Fetch_Array($result))
    {
		$id = $record["id"];
		$name = $record["name"]." ".$record["surname"];
        
        echo "<table width=200 height=60 border=0>\n";
        echo "<tr><td width=20 height=20></td><td>";
		    echo "<a href=\"?doc_id=".$id."&date=".$date."&auth=".$auth."\">".$name."</a>";
            echo "</td><td width=20></td></tr>\n";

	if ($thumb) {
        	echo "<tr><td widht=20 height=40></td><td onClick=\"self.location.href='?date=".$date."&doc_id=".$id."&auth=".$auth."';\">";
		writeDayThumb($id,$date); 
        	echo "</td><td width=20></td></tr>\n";
	}

	echo "<tr><td height=10></td></tr>\n";
    }
    
    echo "</table>\n<br><br>\n";


}

function writeCalendar($timestamp,$href)
{
    global $czech_months;
    global $auth;

    $href = $href."&auth=".$auth;

    $date = getdate($timestamp);
    //echo $date["wday"]."-".$date["mday"].".".$date["mon"]."<br>";
    
    // search for the first day in the month, which day in week it is
    $first = mktime ( 1,1,1, $date["mon"], 1, $date["year"] );
    $first_wday=(date('w', $first)+6)%7;
    //echo "1. je v $first_wday (1 jako pondeli)<br>";

    // fill the calendar table from this day
    $days_in_month=date('t', $timestamp);
    $days_in_calendar_table = 0;
    for ($i=0; $i<$first_wday; $i++)
    {
        $calendar[$i] = "";
        $days_in_calendar_table++;
    }
    for ($i=$first_wday, $j = 1; $j<=$days_in_month; $i++, $j++)
    {
        $calendar[$i] = $j;
        $days_in_calendar_table++;
    }
    for ($i=$days_in_calendar_table%7;$i<7;$i++)
    {
        $calendar[$i + $days_in_calendar_table] = "";
        $days_in_calendar_table++;
    }
    //echo $days_in_calendar_table;  
    
    // prepare next and previous links
    $timestamp_prev = mktime (1,1,1, $date["mon"]-1, 1 , $date["year"] );
    $timestamp_next = mktime (1,1,1, $date["mon"]+1, 1 , $date["year"] );
    $month_prev_ = getdate($timestamp_prev);
    $month_next_ = getdate($timestamp_next);
    $month_prev = $month_prev_["mon"];
    $month_next = $month_next_["mon"];


    echo "<h2>Kalendář</h2>\n";
    echo "<div id=\"calendar_wrap\">\n";
    echo "<table id=\"wp-calendar\" summary=\"Calendar\">\n";
    echo "\t<caption>".$czech_months[$date["mon"]]." ".$date["year"]."</caption>\n";
    echo "\t<thead>\n";
    echo "\t\t<tr>\n";
    echo "\t\t\t<th abbr=\"Monday\" scope=\"col\" title=\"Monday\">Po</th>\n";
    echo "\t\t\t<th abbr=\"Tuesday\" scope=\"col\" title=\"Tuesday\">Út</th>\n";
    echo "\t\t\t<th abbr=\"Wednesday\" scope=\"col\" title=\"Wednesday\">St</th>\n";
    echo "\t\t\t<th abbr=\"Thursday\" scope=\"col\" title=\"Thursday\">Čt</th>\n";
    echo "\t\t\t<th abbr=\"Friday\" scope=\"col\" title=\"Friday\">Pá</th>\n";
    echo "\t\t\t<th abbr=\"Saturday\" scope=\"col\" title=\"Saturday\">So</th>\n";
    echo "\t\t\t<th abbr=\"Sunday\" scope=\"col\" title=\"Sunday\">Ne</th>\n";
    echo "\t\t</tr>\n";
    echo "\t</thead>\n";
    echo "\t<tfoot>\n";
    echo "\t\t<tr>\n";
    echo "\t\t\t<td abbr=\"July\" colspan=\"7\" id=\"prev\">\n";
    echo "\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><TR>
                  <TD align=\"left\"><a href=\"?date=".$timestamp_prev.$href."\">&laquo; ".$czech_months[$month_prev]."</a></TD>
                  <TD align=\"right\"><a href=\"?date=".$timestamp_next.$href."\">".$czech_months[$month_next]." &raquo;</a></TD>
                </TR></table>\n";
    echo "\t\t\t</td>\n";
    echo "\t\t</tr>\n";
    echo "\t\t<tr><td colspan=\"7\" align=center><br><a href=\"?date=".getDayTimestamp(time()).$href."\">[ dnes ]</a></td></tr>\n";
    echo "\t</tfoot>\n";
    echo "\t<tbody>\n";

    $counter=0;
    for ($i=0; $i<$days_in_calendar_table/7; $i++)
    {
        if ( ($calendar[$counter] <= $date["mday"]) && ( ($date["mday"] <= $calendar[$counter+6]) || ($calendar[$counter+6]==0) )):
          $today=" id=\"today\"";
        else:
          $today="";
        endif;
                
        echo "\t\t<tr".$today.">\n";
        for ($j=0; $j<7; $j++)
        {
            $time = mktime(1,1,1,$date["mon"],intval($calendar[$counter]),$date["year"]);
            echo "\t\t\t<td><a href=?date=".$time.$href.">".$calendar[$counter]."</a></td>\n";
            $counter++;
        }
        echo "\t\t</tr>\n";
    }
    echo "\t</tbody>\n";
    echo "</table>\n";
    echo "<br><br><br>\n";
    echo "</div>\n";
}

function writeWeekCalendar($date,$href) 
{
	global $czech_days;
	global $auth;

	$href = $href."&auth=".$auth;

	echo "<h2>Dny</h2>";		
	echo "<br>";
	$ds =getdate($date);
	$cwday = $ds["wday"];

	echo "&nbsp&nbsp&nbsp";	
	for($i=1; $i<6; $i++) {
		$d = $date+($i-$cwday)*24*60*60;
		echo "<a href=?date=$d$href>";
		if ($cwday == $i) { echo "<b>"; }
		echo $czech_days[$i];
		if ($cwday == $i) { echo "</b>"; }
		echo "</a>&nbsp&nbsp";
	}
	echo "<br><br>";
}

function getMondayDate($time)
{
    $day_in_week = ((intval(date("w", $time)))+6)%7;
    return $time-($day_in_week*86400);
}

function getFridayDate($time) 
{
    return getMondayDate($time)+4*3600*24;
}


function writeDayThumb($doc_id,$time)
{

    $doc_avail = 0;
    $actions = 0; 
    //$time = getDayTimestamp($time);

    // get the availability 
    $result = MySQL_Query("SELECT action,doc_avail 
                           FROM objednavky 
                           WHERE (doc_id=$doc_id) AND ($time<termin) AND (termin<$time+60*60*24) ");
    if (!$result)
    {
	    echo "Chyba databáze: ".mysql_error();
	    die;  
    }
    $rows = mysql_num_rows ($result);

    while ( $record = MySQL_Fetch_Array($result))
    {
        if ($record['doc_avail']==0) $doc_avail++;
        if ($record['action']>0) $actions++;
    }

    //draw
    if ($rows==0)
    {
    	if (isholyday($time)>0) {
		echo "svatek";
	} else {
        	echo "nezadano";
	}
        return;
    }

    $available=($rows==$actions)?"0":"1";  
    $overload=($doc_avail>0)?"bgcolor=red":"";

    echo "<table width=\"100%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n";
    echo "\t<tr><td height=\"20%\" ".$overload."></td></tr>\n";
    echo "\t<tr>\n";
        echo "\t\t<td align=center bgcolor=\"".(($available)?"green":"red")."\" height=\"60%\" style=\"cursor:pointer\">".(($available)?"volno":"obsazeno")."</td>\n";
    echo "\t</tr>\n";
    echo "\t<tr><td height=\"20%\" ".$overload."></td></tr>\n";
    echo "</table>\n\n";

}

function getDocName($doc_id)
{
    $result = MySQL_Query("SELECT * FROM doktori WHERE doktori.id=$doc_id");
    if (!$result) 
    {
	    echo "Chyba databáze";
	    die;  
    }
    $record = MySQL_Fetch_Array($result);
    if (!$record)
    {
        echo "<!-- ERROR: doktor $doc_id v databazi neexistuje -->";
        return "<none>";
    }
    
    return $record["name"]." ".$record["surname"];
}


///////////////////////  clipboard functions { ////////////////////////////////////////////////////////////////////////

function setClipboard($name, $value, $expire)
{
    deleteClipboard($name);

    $ok=MySQL_Query("Insert into uschovna (name, value, timestamp) values ('$name','$value','$expire')");
    if (!$ok)
    {
        die("nelze vlozit do schranky");
    }

}

function getClipboard($name)
{
    $result=MySql_Query("Select * from uschovna where name=\"$name\"");
    if (($result) && ($record=MySQL_fetch_array($result)) && ($record['timestamp']<time()))
    {
        return $record['value'];
    }
    return -1;
}

function deleteClipboard($name)
{
    $ok=MySQL_Query("DELETE from uschovna where name=\"$name\"");
    if (!$ok)
    {
        echo "DELETE from uschovna where name=$name<br>";
        die("nelze smazat zaznam ve schrance:".mysql_error());
    }

}


///////////////////////  clipboard functions } ////////////////////////////////////////////////////////////////////////

///////////////////////  Database functions { /////////////////////////////////////////////////////////////////////////

$connection = 0;



function databaseConnect()
{
  global $connection;
  $a = split("/",$_SERVER["REQUEST_URI"]);
  if ($a[1] == "pacosi") {
  	$login = "pacosi";
	$password = "*** CHANGE ME ***";
	$database = "pacosi";
  } else if (($a[1] == "clr") && ($a[2]=="pacosi_dob")) {
  	$login = "clr";
	$password = "*** CHANGE ME ***";
	$database = "clr_pacosi_dob";
  } else if (($a[1] == "clr") && ($a[2]=="pacosi_vin")) {
  	$login = "clr";
	$password = "*** CHANGE ME ***";
	$database = "clr_pacosi_vin";
  } else {
  	$login = "pacosi_test";
	$password = "pacosi_test";
	$database = "pacosi_test";
  }


  $connection = MySQL_Connect("localhost",$login,$password);
  if (!$connection) 
  {
	echo "Databáze není pøipojena";
	return -1;
  }
  MySQL_Select_DB($database);

  mysql_query("SET CHARACTER SET utf8");
  mysql_query("SET NAMES utf8");

  return 0;
}


function databaseDisconnect()
{
  global $connection;
  MySQL_Close($connection);
}

///////////////////////  Database functions } /////////////////////////////////////////////////////////////////////////

///////////////////////  Page functions { ///////////////////////////////////////////

function html_doc_begin($title,$refresh)
{

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
	echo "<html>\n";
	echo "\t<head>\n";
   	echo "\t<title>$title</title>\n";

        echo "\t<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"/>\n";
	echo "\t<link rel=\"stylesheet\" href=\"default.css\">\n";

	echo "\t</head>\n";
	echo "\t<body>\n";

}

function page_menu($action_name,$action) {

	global $auth;
	global $auth_role;
       	global $auth_selector;
	global $sys_actions;
	global $date;
	$highlight=$action; //TODO

	echo "\t\t<!-- PAGE MENU BEGIN -->\n";
	echo "\t\t<div id=\"header\">\n";
	echo "\t\t\t<div id=\"logo\">\n";
	echo "\t\t\t\t<span class=\"system_label\">Evidence objednávek</span>\n";
	echo "\t\t\t\t<span class=\"action_label\">$action_name</span>\n";
	echo "\t\t\t</div> <!-- end of logo -->\n";
// writeMenu("Planovani",$date); 

	echo "\t<div id=\"menu\">\n";
        echo "\t\t<ul>\n";
	while (list($key, $val) = each($sys_actions)) {
		$url = $val["url"];
		$label = $val["label"];
		$access = $val["access"];
		$extra_p = "?auth=$auth";
		if ((isset($val["add_date"])) && ($val["add_date"])  && (isset($date))) {
			$extra_p .= "&date=$date"; 
		}
		if (in_array($auth_role,$access)) {
	   		echo "\t\t\t<li ".(($highlight==$key)?"class=\"current_page_item\"":"")."><a href=\"$url".$extra_p."\">$label</a></li>\n";
		}

	}
	echo "\t\t</ul>\n";
	echo "\t</div>\n";


	echo "\t\t</div> <!-- end of header -->\n";
	echo "\t\t<!-- PAGE MENU END -->\n";
}

function html_doc_end() {
	echo  "\t</body>\n</html>\n";
}
function page_begin() {
	echo "\t\t<!-- PAGE BEGIN -->\n";
	echo "\t\t<div id=\"page\">\n";
}
function page_end() {
	echo "\t\t</div> <!-- end of page -->\n";
	echo "\t\t<!-- PAGE END -->\n";
}

function page_content_begin() {
	echo "\t\t\t<!-- PAGE CONTENT BEGIN -->\n";
	echo "\t\t\t<div id=\"content\">\n";
}

function page_content_end() {
	echo "\t\t\t</div> <!-- end of page content -->\n";
	echo "\t\t\t<!-- PAGE CONTENT END -->\n";
}

function page_sidebar_begin() {
	echo "\t\t\t<!-- PAGE SIDEBAR BEGIN -->\n";
	echo "\t\t\t<div id=\"sidebar\">\n";	
	echo "\t\t\t\t<ul>\n";
}

function page_sidebar_end() {
	echo "\t\t\t\t</ul>\n";
	echo "\t\t\t</div> <!-- end of sidebar -->\n";
	echo "\t\t\t<!-- PAGE SIDEBAR END -->\n";
}




///////////////////////  Page functions } ///////////////////////////////////////////

?>
