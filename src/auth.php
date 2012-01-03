<?

$auth = $_GET["auth"];
$auth_role = 0;
$auth_name = "";
$auth_selector = 0;


function auth_check_access_key($key) {
	global $auth_name;
	global $auth_role;
	global $auth_selector;
	// smazat ty co maji "last" starsi nez "X"
//	MySQL_Query("DELETE FROM auth WHERE `last` < ......"

	// vytahnout z auth tabulky login, a z uzivatelu updatnout auth_role, auth_selector
	$result = MySQL_Query("SELECT login FROM auth WHERE auth_key='$key'");
	
	if (!$result)
	{
		die("chyba databáze".mysql_error());
	}
	$record = MySQL_Fetch_Array($result);
	if (!$record)
	{
		return 0;
	}
	$auth_name = $record['login'];
	
	$result = MySQL_Query("SELECT role,selector FROM uzivatele WHERE login='$auth_name'");
	if (!$result)
	{
		die("chyba databáze".mysql_error());
	}
	$record = MySQL_Fetch_Array($result);
	if (!$result)
	{
		return 0;
	}
	

	$auth_role = $record['role'];
	$auth_selector = $record['selector'];

	// a update 'last' na now()
	MySQL_Query("UPDATE auth SET `last`=NOW() WHERE auth_key='$key'");

	return 1;
}

function auth_destroy_access_key($key) {
	// smazat key z auth tabulky
}


if (! isset($auth) ) {
	$auth = "no";
	if (isset($redir_on_noauth)) {
		replacePage("login.php");
	}
}

if (!auth_check_access_key($auth))
{
     writeMessagePage("Problémové hlášení","Neautorizovaný přístup!<br><br>Pokračujte prosím <a href=login.php>zde</a>","");
}


if ((!$auth_role) || (!$auth_name))
{
    writeMessagePage("Problémové hlášení","Neautorizovaný přístup!<br><br>Pokračujte prosím <a href=login.php>zde</a>","");
}

?>
