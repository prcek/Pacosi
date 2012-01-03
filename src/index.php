<?$redir_on_noauth=1;?>
<? require("sys_begin.inc"); ?>

<?

        switch($auth_role)
        {
            case 1: //root id
                    replacePage("overview.php?auth=".$auth);
                    break;
            case 2: //doktor role
                    replacePage("docday_doktor.php?date=".getDayTimestamp(time())."&auth=".$auth);
                    break;
	    case 3: //evidence id
                    replacePage("overview.php?auth=".$auth);
                    break;
            default:
                    replacePage("overview.php?auth=".$auth);
                    break;
        }

?>

<? require("sys_end.inc"); ?>

