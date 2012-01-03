<? require("sys_begin.inc"); ?>

<?
  

?>

<?
  //get actual time
  $time=time();

  //save the data if it is required
  if($_GET['save'])
  {
    if (!$_GET['extra'])
    {
        $ok=MySQL_QUERY("UPDATE objednavky SET 
            name='$_GET[name]',
            surname='$_GET[surname]',
            title='$_GET[title]',
            phone='$_GET[phone]',
            birth='$_GET[birth]',
            action='$_GET[action]',
            note='$_GET[note]',
            block='0'
            WHERE doc_id=$_GET[doc_id] AND termin=$_GET[date];");
    }
    else
    {
        $ok=MySQL_QUERY("INSERT INTO objednavky (termin, doc_id, doc_avail, name, surname, title, phone, birth, action, note, block)
                VALUES ('$_GET[date]',
                        '$_GET[doc_id]',
                        '1',
                        '$_GET[name]',
                        '$_GET[surname]',
                        '$_GET[title]',
                        '$_GET[phone]',
                        '$_GET[birth]',
                        '$_GET[action]',
                        '$_GET[note]',
                        '0');");
    }
    
    if ($ok)
    {
        //echo "<h3>Upraveno</h3>";
        //databaseDisconnect();
        replacePage("docday.php?date=".getDayTimestamp($_GET['date'])."&doc_id=".$_GET['doc_id']."&auth=".$auth);
    }
  }

  //delete the data if requested
  if($_GET['delete'])
  {
    if (!isTerminExtra($_GET[date]))
    {
        $ok=MySQL_QUERY("UPDATE objednavky SET 
            name='',
            surname='',
            title='',
            phone='',
            birth='',
            action='0',
            note='',
            block='0'
            WHERE doc_id=$_GET[doc_id] AND termin=$_GET[date];");
    }
    else
    {
        $ok=MySQL_QUERY("DELETE from objednavky WHERE doc_id=$_GET[doc_id] AND termin=$_GET[date];");
    }
    if ($ok)
    {
        //echo "<h3>Upraveno</h3>";
        //databaseDisconnect();
        replacePage("docday.php?date=".getDayTimestamp($_GET['date'])."&doc_id=".$_GET['doc_id']."&auth=".$auth);
    }
  }

  //paste the termin from clipbeard, if requested
  if ($_GET['paste'])
  {
       $clipboard = getClipboard($auth_name);      
       $clipboard_array = explode("#",$clipboard);

       $old_doc_id = $clipboard_array[0];
       $old_termin = $clipboard_array[1];
       $new_termin = $_GET['paste'];
       if ($old_termin<0)
       {
         writeMessagePage("Data ve schrance už nejsou platná<br>Pokračujte prosím<a href=\"docday.php?date=".getDayTimestamp($new_termin)."&doc_id=".$_GET['doc_id']."&auth=".$auth."\">zde</a>");
       }

       //cancel the value in the clipboard
       deleteClipboard($auth_name);
        
       //is the old termin overloaded?
       $result=MySQL_QUERY("SELECT doc_avail FROM objednavky WHERE doc_id=$old_doc_id AND termin=$old_termin");
       $record=MySQL_fetch_array($result);
       $old_doc_avail=$record['doc_avail'];

       //chande the "termin" value to the new one && unblock the item
       //shift old to new
       $ok1=MySQL_QUERY("UPDATE objednavky SET 
            block='0',
            termin='$new_termin',
            doc_id='$_GET[doc_id]',
            doc_avail='1'
            WHERE doc_id=$old_doc_id AND termin=$old_termin");

       if ($old_doc_avail==1)
       {
           // shift new back to old
           $ok2=MySQL_QUERY("UPDATE objednavky SET 
                block='0',
                termin='$old_termin',
                doc_id='$old_doc_id'
                WHERE doc_id=$_GET[doc_id] AND termin=$new_termin AND action=0");
       }
       else
       {
           //delete overloaded if exists
           $ok2=MySQL_QUERY("DELETE FROM objednavky  
                WHERE doc_id=$_GET[doc_id] AND termin=$new_termin AND action=0");
       }

       if ($ok1 && $ok2)
       {
            //redirect to docday
            replacePage("docday.php?date=".getDayTimestamp($new_termin)."&doc_id=".$_GET['doc_id']."&auth=".$auth);
       }
       else
       {
            writeMessagePage("Problémové hlášení","Nelze provést vložení!","");
       }       
  }

  //unblock termin, if requested
  if ($_GET['unblock'])
  {
    $ok=MySQL_QUERY("UPDATE objednavky SET 
            block='0'
            WHERE doc_id=$_GET[doc_id] AND termin=$_GET[unblock];");
    if ($ok)
    {
        if ($_GET['form_again'])
        {
            replacePage("order.php?date=".$_GET['date']."&doc_id=".$_GET['doc_id']."&auth=".$auth);
        }
        else
        {
            replacePage("docday.php?date=".getDayTimestamp($_GET['date'])."&doc_id=".$_GET['doc_id']."&auth=".$auth);
        }
    }
    else
    {
        die("Error: nelze provest ublock");
    }
  }

  
  //get the values from database
  $result=MySQL_QUERY("SELECT * FROM objednavky WHERE doc_id=$_GET[doc_id] AND termin=$_GET[date];");
  if (($result) && ($record=MySQL_Fetch_Array($result))) 
  {
    $form_name=$record['name'];
    $form_surname=$record['surname'];
    $form_title=$record['title'];
    $form_phone=$record['phone'];
    $form_birth=($record['birth']=="0000")?"":$record['birth'];
    $form_action=$record['action'];
    $form_note=$record['note'];
    $block=$record['block'];
  }
  
  
  //get the termin to the clipboard, if requsted
  if ($_GET['cut'])
  {
      //save the termin to the clipboard, expiry date set to block timeout
      setClipboard($auth_name, $_GET['doc_id']."#".$_GET['cut'], ($block+5*60));

      //redirect to overview
      replacePage("overview.php?date=".getDayTimestamp($_GET['date'])."&auth=".$auth);
      
      //let the termin blocked, till replaning is finished (termin pasted)
  }


  //disable the form, if still blocked by another user
  $blocked = false;
  if (($time-$block) < 5*60) 
  {
    $blocked = true;
  }


  //block this termin for some time - set the timestamp
  if (!$blocked)
  {
      $ok=MySQL_QUERY("UPDATE objednavky SET 
            block='$time'
            WHERE doc_id=$_GET[doc_id] AND termin=$_GET[date];");
      if (!$ok)
      {
          die ("chyba databaze");
      }
  }

  //insert VIP client ?
  $vip=0;
  if ($_GET['vip'])
  {
      $vip = 1;
  }

?>

<? html_doc_begin("Objednani pacietna",0); ?>
<? require("jsfuncs.inc"); ?>
<? page_menu("Objednani pacienta","Order"); ?>
<? page_begin(); ?>
<? page_content_begin(); ?>




		<div class="post">
			
            <?

            echo "<h2 class=\"title\">".getDocName($_GET['doc_id'])."</h2>\n";
			echo "<p class=\"byline\"><small>Požadovaný čas: ";
            if ($vip)
            {
                echo "Extra objednavka";
            }
            else
            {
                echo date("H:i", $_GET['date']);  
            }
            echo "</small></p>\n";
			
            writeDate($_GET['date']); 

            echo "<div class=\"entry\">";

            if ($blocked)
            {
                global $actions;

                //write read only informations
                echo "<table>\n";
                echo "\t<tr><TD width=\"100\">Přijmení:</TD><TD width=\"200\">".$form_surname."</TD></tr>\n";
				echo "\t<Tr><TD width=\"100\">Jméno:</TD><TD width=\"200\">".$form_name."</TD></tr>\n";
				echo "\t<Tr><TD width=\"100\">Titul:</TD><TD width=\"200\">".$form_title."</TD></tr>\n";
                echo "\t<Tr><TD width=\"100\">Telefon:</TD><TD width=\"200\">".$form_phone."</TD></tr>\n";
				echo "\t<Tr><TD width=\"100\">Rok narozeni:</TD><TD width=\"200\">".$form_birth."</TD></tr>\n";
                echo "\t<Tr><TD width=\"100\">Akce:</TD><TD width=\"200\">".$actions[$form_action]."</TD></tr>\n";
				echo "\t<Tr><TD width=\"100\" valign=\"top\">Poznamka</TD><TD width=\"200\">".$form_note."</TD></tr>\n";
				echo "</table></div><br><br>\n";
    
                //write info
                echo "Termín je právě blokovaný, expirace za ".(($block+5*60)-$time)." sekund<br><br><br><br><br>";
                echo "<p class=\"links\">\n";
                    echo "<a href=\"order.php?unblock=".$_GET['date']."&form_again=1&date=".$_GET['date']."&doc_id=".$_GET['doc_id']."&auth=".$auth;
                    echo "\" class=\"more\">Nasilně odblokovat</a> (Použijte jen ve vyjmečných případech !)";
                    echo "</p>\n";
                echo "<p class=\"links\">\n";
                    echo "<a href=\"docday.php?date=";
                    echo getDayTimestamp($_GET['date'])."&doc_id=".$_GET['doc_id']."&auth=".$auth;
                    echo "\" class=\"more\">Zpět na denní přehled</a> (Provedené úpravy budou zrušeny)";
			        echo "</p>\n";
            }
            else {

            ?>

			<form action="order.php" method=get>
            <input type="hidden" name="doc_id" value="<? echo $_GET['doc_id']; ?>">
            <input type="hidden" name="auth" value="<? echo $auth; ?>">

            <?

                if ($vip)
                {
	            echo "<input type=\"hidden\" name=\"extra\" value=\"1\">";	
                    echo "<table><tr><td width=\"100\">Čas</td><td width=200>";
                    echo "<SELECT NAME=\"date\">\n";


                    $result=MySQL_QUERY("SELECT termin 
                                         FROM objednavky 
                                         WHERE doc_id=$_GET[doc_id]  AND termin>$_GET[date] AND termin<($_GET[date]+60*60*24)
                                         ORDER BY termin;");
                    if (!$result)
                    {
                        die("chyba databáze");
                    }

                    $tmp=0;
                    while ($record=MySQL_Fetch_Array($result))
                    {                     
                        if (isTerminExtra($record['termin']))
                        {
                            $tmp=0;
                            continue;
                        }
                        if ($tmp)
                        {
                            echo "<OPTION VALUE=\"".$tmp."\">".date("H:i", $tmp)."&nbsp; \n";
                        }
                        $tmp = $record['termin']+60*10;
                    }

                    echo "</SELECT></td></tr></table>\n";                   
                }
                else
                {
                    echo "<input type=\"hidden\" name=\"date\" value=\"".$_GET['date']."\">\n";
                }
				
                ?>
                
                <table>
					<Tr><TD width="100">Přijmení</TD><TD width="200"><Input type="text" name="surname" onkeyup="capitalise(this);" value="<? echo $form_surname; ?>"></TD></tr>
					<Tr><TD width="100">Jméno</TD><TD width="200"><Input type="text" name="name" onkeyup="capitalise(this);" value="<? echo $form_name; ?>"></TD></tr>
					<Tr><TD width="100">Titul</TD><TD width="200"><Input type="text" name="title" onkeyup="capitalise(this);" value="<? echo $form_title; ?>"></TD></tr>
                    <Tr><TD width="100">Telefon</TD><TD width="200"><Input type="text" name="phone" value="<? echo $form_phone; ?>"></TD></tr>
					<Tr><TD width="100">Rok narozeni</TD><TD width="200"><Input type="text" name="birth" value="<? echo $form_birth; ?>"></TD></tr>
					<Tr><TD width="100">Akce</TD><TD width="200">
						<SELECT NAME="action">
						<OPTION VALUE="1" <? if ($form_action==1) echo "SELECTED"; ?> >Vstupní vyšetření
						<OPTION VALUE="2" <? if ($form_action==2) echo "SELECTED"; ?> >Znovuvstupní vyšetření
						<OPTION VALUE="3" <? if ($form_action==3) echo "SELECTED"; ?> >Kontrolní vyšetření
						</SELECT></TD></tr>
					<Tr><TD width="100" valign="top">Poznamka</TD><TD width="200"><textarea name="note" style="text-transform: capitalize;" rows="4"><? echo $form_note; ?></textarea></TD></tr>
					<tr><td></td><td><input type="submit" name="save" value="uložit"></td></tr>
				</table>
				</form>
			</div>
            <br>
            <?
            
            //link "vlozit objednavku ze schranky"
            if ($form_action==0) //the termin is emptu
            {
                if (getClipboard($auth_name)>-1) //there is anything to paste
                {
                    echo "<p class=\"links\">\n";
                    echo "<a href=\"order.php?paste=";
                    echo $_GET['date']."&doc_id=".$_GET['doc_id']."&auth=".$auth;
                    echo "\" class=\"more\">Vložit objednávku ze schránky</a>";
                    echo "</p>\n";
                }
            }


            //link "tisknout"
            if ($form_action>0) //the termin has been ever saved
            {
                echo "<p class=\"links\">\n";
                echo "<a href=\"print_order.php?date=";
                echo $_GET['date']."&doc_id=".$_GET['doc_id']."&auth=".$auth;
                echo "\" class=\"more\" target=\"_new\">Vytisknout tuto objednávku</a>";
                echo "</p>\n";
            }

            //link smazat
            if ($form_action>0) //the termin has been ever saved
            {
                echo "<p class=\"links\">\n";
                echo "<a href=\"order.php?date=";
                echo $_GET['date']."&doc_id=".$_GET['doc_id']."&delete=1&auth=".$auth;
                echo "\" class=\"more\">Smazat tuto objednávku</a>";
                echo "</p>\n";
            }


            //link "preplanovat"
            if ($form_action>0) //the termin has been ever saved
            {
                echo "<p class=\"links\">\n";   
                echo "<a href=\"order.php?date=";
                echo getDayTimestamp($_GET['date'])."&doc_id=".$_GET['doc_id']."&cut=".$_GET['date']."&auth=".$auth;
                echo "\" class=\"more\">Přeplánovat pacienta</a> (Záznam se vloží do schránky)";
                echo "</p>\n";
            }

            //link "zpet"
            echo "<p class=\"links\">\n";   
            echo "<a href=\"order.php?date=";
            echo getDayTimestamp($_GET['date'])."&doc_id=".$_GET['doc_id']."&unblock=".$_GET['date']."&auth=".$auth;
            echo "\" class=\"more\">Zpět na denní přehled</a> (Provedené úpravy budou zrušeny)";
            echo "</p>\n";
            
            ?>

            <? } // else end (blocked) ?>


		</div>			



	</div>
	<div id="sidebar">
        
        <ul>
			<!-- <li id="calendar"><? //writeCalendar(time(),""); ?></li> -->
            <!--
            <li>
                <h2>Operace</h2>
                <ul>Přeplánovat pacienta<input type="submit" name="reorder" value="proveď" ></ul>
                <ul>Smazat objednávku<input type="submit" name="delete" value="proveď" ></ul>
            </li>
            -->
		</ul>
        

	</div>
     </div>
  </body>

</html>

<?

require("sys_end.inc");

?>
