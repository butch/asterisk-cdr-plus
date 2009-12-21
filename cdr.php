<?

session_start();

function cdrpage_getpost_ifset($test_vars)
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


cdrpage_getpost_ifset(array('s', 't'));


$array = array ("", "Статистика", "Сравнение звонков", "Трафик за месяц","Ежедневная нагрузка", "");
$s = $s ? $s : 1;
$section="section$s$t";

$racine=$PHP_SELF;
$update = "03 March 2005";

$paypal="NOK"; //OK || NOK
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>		
		<title>Asterisk CDR</title>
		<meta http-equiv="Content-Type" content="text/html"; charset=utf8">
		<link rel="stylesheet" type="text/css" media="print" href="/css/print.css">
		<SCRIPT LANGUAGE="JavaScript" SRC="./encrypt.js"></SCRIPT>
		<style type="text/css" media="screen">
			@import url("css/layout.css");
			@import url("css/content.css");
			@import url("css/docbook.css");
		</style>
		<meta name="MSSmartTagsPreventParsing" content="TRUE">
		<script src="ec/res/domready.js"></script>
		<script src="ec/res/swfobject.js"></script>
		<script src="ec/res/wavplayer.js"></script>
	</head>
	<body>
	
	

	
	
		<!-- header BEGIN -->
		<div id="fedora-header">
			
			<div id="fedora-header-logo">
				 <table border="0" cellpadding="0" cellspacing="0"><tr><td><img src="images/asterisk.gif"  alt="CDR (Call Detail Records - Детализация звонков)"></td><td>
				 <H1><font color=#990000>&nbsp;&nbsp;&nbsp;CDR (Call Detail Records - Детализация звонков)</font></H1></td></tr></table>
			</div>

		</div>
		<div id="fedora-nav"></div>
		<!-- header END -->
		
		<!-- leftside BEGIN -->
		<div id="fedora-side-left">
		<div id="fedora-side-nav-label">Site Navigation:</div>	<ul id="fedora-side-nav">
		<? 
			$nkey=array_keys($array);
    		$i=0;
    		while($i<sizeof($nkey)){
			
				$op_strong = (($i==$s) && (!is_string($t))) ? '<strong>' : '';
				$cl_strong = (($i==$s) && (!is_string($t))) ? '</strong>' : '';
									
        		if(is_array($array[$nkey[$i]])){
					
					
					
					echo "\n\t<li>$op_strong<a href=\"$racine?s=$i\">".$nkey[$i]."</a>$cl_strong";
									
					$j=0;
					while($j<sizeof($array[$nkey[$i]] )){
						$op_strong = (($i==$s) && (isset($t)) && ($j==intval($t))) ? '<strong>' : '';
						$cl_strong = (($i==$s) && (isset($t))&& ($j==intval($t))) ? '</strong>' : '';						
						echo "<ul>";						
						echo "\n\t<li>$op_strong<a href=\"$racine?s=$i&t=$j\">".$array[$nkey[$i]][$j]."</a>$cl_strong";
						echo "</ul>";
						$j++;						
					}
						
        		}else{					
					echo "\n\t<li>$op_strong<a href=\"$racine?s=$i\">".$array[$nkey[$i]]."</a>$cl_strong";
				}
				echo "</li>\n";
        		
        		$i++;
    		}
			
		?>

			</ul>
			
		<? if ($paypal=="OK"){?>
		<center>
			<br><br>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="info@areski.net">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="tax" value="0">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
</form>
</center>
			<? } ?>
			
		</div>

		<!-- leftside END -->

		<!-- content BEGIN -->
		<div id="fedora-middle-two">
			<div class="fedora-corner-tr">&nbsp;</div>
			<div class="fedora-corner-tl">&nbsp;</div>
			<div id="fedora-content">



<?if ($section=="section0"){?>

<h1>
 <center>ASTERISK : CDR ANALYSER</center>
</h1>
						<h3>Call data collection</h3>
						<p>Regardless of their size, most telephone PBX (public branch exchange) and PMS (property management systems)
						output <b>Call Detail Records (CDR)</b>. Generally, these get created at the end of a call but on some phone systems
						the data is available during the call. This data is output from the phone system by a serial link known as the
						Station Message Detail Recording port (SMDR). <b>Some of the details included in call records are: Time, Date, Call
						Duration, Number dialed, Caller ID information, Extension, Line/trunk location, Cost, Call completion status.</b><br>
						<br>
						Call detail records, both local and long distance, can be used for usage verification, billing reconciliation,
						network management and to monitor telephone usage to determine volume of phone usage, as well as abuse of the system. 
						CDR's aid in the planning for future telecommunications needs. <br>
						<br>
						Control with CDR analysis:
						<ul>

							<li>review all CDR's for accuracy 
							<li>verify usage 
							<li>resolve discrepancies with vendors
							<li>disconnect unused service 
							<li>terminate leases on unused equipment 
							<li>deter or detect fraud
							<li>etc ...
						</ul>

<?}elseif ($section=="section1"){?>

	<?require("call-log.php");?>


<?}elseif ($section=="section2"){?>

	<?require("call-comp.php");?>


<?}elseif ($section=="section3"){?>

	<?require("call-last-month.php");?>

<?}elseif ($section=="section4"){?>

	<?require("call-daily-load.php");?>


<?}elseif ($section=="section5"){?>
		<h1>Contact</h1>        		
        <table width="90%">
          
		  <tr> 
            <td>
				<h3>Arezqui Bela&iuml;d <br> <i>Barcelona - Belgium</i></h3>				
				<br>
				<a href='javascript:bite("3721 945 4728 2762 3565 3554 2008 1380 654 3721 3554 4468 3007 3877 4828 654",5123,2981)'>Click to email me</a>
				<br><br><i>Feel free to send me your suggestions to improve the application ;)</i>
            </td>
          </tr>          
          
        </table>
		<br><br><em><strong>Last update:</strong></em> <?=$update?><br>


<?}else{?>
	<h1>Coming soon ...</h1>
   
<?}?>

		
		<br><br><br><br><br><br>
		</div>

			<div class="fedora-corner-br">&nbsp;</div>
			<div class="fedora-corner-bl">&nbsp;</div>
		</div>
		<!-- content END -->
		
		<!-- footer BEGIN -->
		<div id="fedora-footer">

			<br>			
		</div>
		<!-- footer END -->
	</body>
</html>
