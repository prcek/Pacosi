<?

$sys_actions = array(
	"Prehled" => array("label"=>"Přehled", "url"=>"overview.php", "access"=>array(1,3) ),
	"Hledat" => array("label"=>"Hledat", "url"=>"search.php", "access"=>array(1,3), "add_date"=>1 ),
	"Doktor" => array("label"=>"Přehled", "url"=>"docday_doktor.php", "access"=>array(2)),
	"Plan" => array("label"=>"Plánování", "url"=>"plan2.php", "access"=>array(1), "add_date"=>1),
	"Template" => array("label"=>"Šablona", "url"=>"template.php", "access"=>array(1) ),
	"Settings" => array("label"=>"Nastavení", "url"=>"settings.php", "access"=>array() ),
	"Svatky" => array("label"=>"Svatky", "url"=>"svatky.php", "access"=>array(1) ),
	"User" => array("label"=>"Uživatel", "url"=>"user.php", "access"=>array(1,2,3) ),
	"Logout"   => array("label"=>"Odhlášení", "url"=>"logout.php", "access"=>array(1,2,3) )
	
	);

?>
