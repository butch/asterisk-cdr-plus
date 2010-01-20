<?php

define ("WEBROOT", "http://cdr.florist.my/");
define ("FSROOT", "/var/www/asterisk-stat/");



define ("LIBDIR", FSROOT."lib/");


define ("HOST", "localhost");
define ("PORT", "3306");
define ("USER", "fsdbuser");
define ("PASS", "fsdbpass");
define ("DBNAME", "freeswitch");
define ("DB_TYPE", "mysql"); // mysql or postgres


define ("DB_TABLENAME", "cdr");
 
// Regarding to the dst you can setup an application name
// Make more sense to have a text that just a number
// especially if you have a lot of extension in your dialplan
$appli_list['601']=array("operators");
$appli_list['00049967']=array("telfin");
$appli_list['1001']=array("panas_menu");
$appli_list['21100038943']=array("terrasip");
$appli_list['0022912035']=array("8-800-200-3356");
$appli_list['0019242460']=array("8-800-200-4070");
$appli_list['0022912012']=array("8-800-200-6769");


include (FSROOT."lib/DB-modules/phplib_".DB_TYPE.".php");


function DbConnect()
  {
	
	$DBHandle = new DB_Sql();
	$DBHandle -> Database = DBNAME;
	$DBHandle -> Host = HOST;
	$DBHandle -> User = USER;
	$DBHandle -> Password = PASS;

	$DBHandle -> connect ();


	return $DBHandle;
}


function getpost_ifset($test_vars)
{
	if (!is_array($test_vars)) {
		$test_vars = array($test_vars);
	}
	foreach($test_vars as $test_var) { 
		if (isset($_POST[$test_var])) { 
			global $$test_var;
			$$test_var = $_POST[$test_var]; 
		} elseif (isset($_GET[$test_var])) {
			global $$test_var; 
			$$test_var = $_GET[$test_var];
		}
	}
}



function display_minute($sessiontime){
		global $resulttype;
		if ((!isset($resulttype)) || ($resulttype=="min")){  
				$minutes = sprintf("%02d",intval($sessiontime/60)).":".sprintf("%02d",intval($sessiontime%60));
		}else{
				$minutes = $sessiontime;
		}
		echo $minutes;
}

function display_2dec($var){		
		echo number_format($var,2);
}

function display_2bill($var){	
		$var=$var/100;
		echo '$ '.number_format($var,2);
}

function remove_prefix($phonenumber){
		// our trunks prefix from panasonic
		if (substr($phonenumber,0,3) == "801"){
					echo substr($phonenumber,3);
					return 1;
		}
		if (substr($phonenumber,0,3) == "804"){
					echo substr($phonenumber,3);
					return 1;
		}
		echo $phonenumber;
	
}


function display_acronym($field){		
		echo '<acronym title="'.$field.'">'.substr($field,0,7).'...</acronym>';		
}

function playlink($filename1){
	$shit = 'rec/'.$filename1.'.wav';
	if (is_readable($shit)) {
    	 	// echo "The file $shit  is readable";
        	return "<a href='$shit'>play</a>";
        }else{
        	// echo "no file - $shit";
        }
}

function dstchannel($channel){
	if (strstr($channel, "sofia/internal/")) {
		return "<b>".(str_replace("@192.168.2.35","",(str_replace("sofia/internal/", "", $channel))))."</b>";
		// return preg_replace('80[14](\d+)', '${1}', (str_replace("@192.168.2.35","",(str_replace("sofia/internal/", "", $channel)))));
	}else{
		return "";
	}
	 
}


?>
