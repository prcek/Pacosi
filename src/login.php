<?	header('Content-type: text/html; charset=utf-8'); 
	require("functions.php"); ?>

<?

if ($_POST['enter'])
{
    databaseConnect();
    
    $login    = $_POST['login'];
    $password = $_POST['password'];
    if (($login=="") || ($password==""))
    {
        $unauthorised = 1;
    }
 
    $result=MySQL_Query("SELECT role,selector FROM uzivatele WHERE login='$login' AND password='$password'");
    if (!$result)
    {
        die("chyba databáze".mysql_error());
    }
    $record = mysqli_fetch_array($result);
    if (!$record)
    {
        $unauthorised = 1;
    }
    else
    {
        $role = $record['role'];
        $selector = $record['selector'];

        $auth_string=md5(uniqid(rand(), true));

	MySQL_Query("INSERT INTO `auth` ( `login` , `last` , `auth_key` ) VALUES ( '$login', NOW( ) , '$auth_string')");
        
        replacePage("index.php?auth=".$auth_string);
        die("Přesměrováno");
    }

    databaseDisconnect();
}

?>

<html>
  <head>
    <title>Evidence - prihlaseni</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="default.css">
  </head>
  <body>
    <div id="header">
	<div id="logo">
		<span class="system_label">Evidence objednávek</span>
		<span class="action_label">Přihlášení do systému</span>
	</div>
    </div>
    <div id="page" align="center">
				<br><br><br><br><br><br><br><br><br><br>
                <?
                if ($unauthorised)
                {
                    echo "<h3>Špatné jméno nebo heslo</h3><br>\n";
                }
                ?>
				<form method="post">
				<table>
					<Tr><TD width="100">Jméno</TD><TD width="200"><Input type="text" name="login"></TD></tr>
					<Tr><TD width="100">Heslo</TD><TD width="200"><Input type="password" name="password"></TD></tr>
					
					<tr><td></td><td><input type="submit" name="enter" value="vstoupit"></td></tr>
				</table>
				</form>
			



     </div>
  </body>
</html>
