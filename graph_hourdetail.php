<?php
include_once(dirname(__FILE__) . "/lib/defines.php");
include_once(dirname(__FILE__) . "/lib/Class.Table.php");
include_once(dirname(__FILE__) . "/jpgraph_lib/jpgraph.php");
include_once(dirname(__FILE__) . "/jpgraph_lib/jpgraph_line.php");
include_once(dirname(__FILE__) . "/jpgraph_lib/jpgraph_bar.php");


// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;


getpost_ifset(array('typegraph', 'min_call', 'fromstatsday_sday', 'days_compare', 'fromstatsmonth_sday', 'dsttype', 'sourcetype', 'clidtype', 'channel', 'resulttype', 'dst', 'src', 'clid', 'userfieldtype', 'userfield', 'accountcodetype', 'accountcode', 'hourinterval'));

//echo "posted=$posted 'accountcodetype=$accountcodetype', 'accountcode=$accountcode', 'fromstatsday_sday=$fromstatsday_sday', 'fromstatsmonth_sday=$fromstatsmonth_sday' hourinterval=$hourinterval ";
//exit();

//$hourinterval=12;
// hourinterval -- 1 to 23
if (!($hourinterval>=0) && ($hourinterval<=23)) exit();

// http://localhost/Asterisk/asterisk-stat-v1_4/graph_stat.php?min_call=0&fromstatsday_sday=11&days_compare=2&fromstatsmonth_sday=2005-02&dsttype=1&sourcetype=1&clidtype=1&channel=&resulttype=&dst=1649&src=&clid=&userfieldtype=1&userfield=&accountcodetype=1&accountcode=

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME=DB_TABLENAME;

//$link = DbConnect();
$DBHandle  = DbConnect();

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();


$FG_TABLE_DEFAULT_ORDER = "calldate";
$FG_TABLE_DEFAULT_SENS = "DESC";

// This Variable store the argument for the SQL query

$FG_COL_QUERY_GRAPH	= 'calldate, duration';



if ($FG_DEBUG == 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY_GRAPH";

$instance_table_graph = new Table($FG_TABLE_NAME, $FG_COL_QUERY_GRAPH);


if ( is_null ($order) || is_null($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}


	


	
  function do_field($sql,$fld){
  		$fldtype = $fld.'type';
		global $$fld;
		global $$fldtype;
        if (isset($$fld) && ($$fld!='')){
                if (strpos($sql,'WHERE') > 0){
                        $sql = "$sql AND ";
                }else{
                        $sql = "$sql WHERE ";
                }
				$sql = "$sql $fld";
				if (isset ($$fldtype)){                
                        switch ($$fldtype) {
							case 1:	$sql = "$sql='".$$fld."'";  break;
							case 2: $sql = "$sql LIKE '".$$fld."%'";  break;
							case 3: $sql = "$sql LIKE '%".$$fld."%'";  break;
							case 4: $sql = "$sql LIKE '%".$$fld."'";
						}
                }else{ $sql = "$sql LIKE '%".$$fld."%'"; }
		}
        return $sql;
  }  
  $SQLcmd = '';

  if ($_POST['before']) {
    if (strpos($SQLcmd, 'WHERE') > 0) { 	$SQLcmd = "$SQLcmd AND ";
    }else{     								$SQLcmd = "$SQLcmd WHERE "; }
    $SQLcmd = "$SQLcmd calldate<'".$_POST['before']."'";
  }
  if ($_POST['after']) {    if (strpos($SQLcmd, 'WHERE') > 0) {      $SQLcmd = "$SQLcmd AND ";
  } else {      $SQLcmd = "$SQLcmd WHERE ";    }
    $SQLcmd = "$SQLcmd calldate>'".$_POST['after']."'";
  }
  $SQLcmd = do_field($SQLcmd, 'clid');
  $SQLcmd = do_field($SQLcmd, 'src');
  $SQLcmd = do_field($SQLcmd, 'dst');
  $SQLcmd = do_field($SQLcmd, 'channel');  
    
  $SQLcmd = do_field($SQLcmd, 'userfield');
  $SQLcmd = do_field($SQLcmd, 'accountcode');
  


$date_clause='';

$min_call= intval($min_call);
if (($min_call!=0) && ($min_call!=1)) $min_call=0;

if (!isset($fromstatsday_sday)){	
	$fromstatsday_sday = date("d");
	$fromstatsmonth_sday = date("Y-m");	
}


$hourintervalplus = $hourinterval+1;

if (DB_TYPE == "postgres"){	
	if (isset($fromstatsday_sday) && isset($fromstatsmonth_sday)) 
	$date_clause.=" AND calldate < '$fromstatsmonth_sday-$fromstatsday_sday ".$hourintervalplus.":00:00' AND calldate >= '$fromstatsmonth_sday-$fromstatsday_sday ".$hourinterval.":00:00' ";
}else{
	if (isset($fromstatsday_sday) && isset($fromstatsmonth_sday)) 
	$date_clause.=" AND calldate < '$fromstatsmonth_sday-$fromstatsday_sday ".$hourintervalplus.":00:00' AND calldate >= '$fromstatsmonth_sday-$fromstatsday_sday ".$hourinterval.":00:00' ";	
}

//-- $date_clause=" AND calldate < date'$fromstatsmonth_sday-$fromstatsday_sday'+ INTERVAL '1 DAY' AND calldate >= '$fromstatsmonth_sday-$fromstatsday_sday 12:00:00'";
  
if (strpos($SQLcmd, 'WHERE') > 0) { 
	$FG_TABLE_CLAUSE = substr($SQLcmd,6).$date_clause; 
}elseif (strpos($date_clause, 'AND') > 0){
	$FG_TABLE_CLAUSE = substr($date_clause,5); 
}

if ($FG_DEBUG == 3) echo $FG_TABLE_CLAUSE;


//$list = $instance_table -> Get_list ($FG_TABLE_CLAUSE, $order, $sens, null, null, null, null);


$list_total = $instance_table_graph -> Get_list ($FG_TABLE_CLAUSE, 'calldate', 'ASC', null, null, null, null);

//print_r($list_total);
/**************************************/

$nbcall = count($list_total);



$mycall_min[0]=0;
$mycall_dur[0]=0;
/* 
	WE WILL BUILD DIFFERENT TABLES, 
mycall_min FOR THE STARTDATE (MIN) 
mycall_dur FOR THE DURATION OF EACH CALLS (IN SECS)
mycall_minsec_start FOR THE EXACT START OF THE CALL AND END
mycall_minsec_start[i][0] - START DATE (MINSEC)1843		18em Minutes 43 sec
mycall_minsec_start[i][1] - END DATE   (MINSEC)2210		22em Minutes 10 sec

*/
for ($i=1; $i <= $nbcall; $i++){	
	$mycall_min[$i] = substr($list_total[$i-1][0],14,2);
	$mycall_minsec_start[$i][0] = substr($list_total[$i-1][0],14,2).substr($list_total[$i-1][0],17,2);
	$mycall_dur[$i] = $list_total[$i-1][1];	
	
	$nx_sec_report = 0;
	$nx_sec = substr($list_total[$i-1][0],17,2) + ($mycall_dur[$i]%60);
	$nx_sec_report = intval($nx_sec/60);
	$nx_sec = $nx_sec%60;
	
	$nx_min = substr($list_total[$i-1][0],14,2) + intval($mycall_dur[$i]/60) + $nx_sec_report;
	if ($nx_min>59) { $nx_min=59; $nx_sec = 59; }
	
	$mycall_minsec_start[$i][1] = sprintf("%02d",$nx_min).sprintf("%02d",$nx_sec);	
	
	//if ($i==10) break;
}
/*
print_r ($list_total);
print_r ($mycall_minsec_start);
print_r ($mycall_dur);

echo count($mycall_minsec_start)."<br>";
*/


for ($k=0; $k<=count($mycall_minsec_start); $k++){

	if (is_numeric($fluctuation[$mycall_minsec_start[$k][0]])){
		$fluctuation[$mycall_minsec_start[$k][0]]++; 
	}else{
		$fluctuation[$mycall_minsec_start[$k][0]]=1;
	} 
	if (is_numeric($fluctuation[$mycall_minsec_start[$k][1]])){
		$fluctuation[$mycall_minsec_start[$k][1]]--; 
	}else{
		$fluctuation[$mycall_minsec_start[$k][1]]=-1;
	}
}

ksort($fluctuation);
//print_r($fluctuation);

$maxload=1;
$load=0;
while (list ($key, $val) = each ($fluctuation)) {
  //echo "<br>$key => $val\n";  
  $load = $load + $val;
  if (is_numeric($key)) $fluctuation_load[substr($key,0,2).':'.substr($key,2,2)] = $load;
  //echo "<br>:: ".$load;
  if ($load > $maxload){ 
  		$maxload=$load;
		
  }
}
//echo $maxload;

//print_r($fluctuation_load);
//print_r(array_keys($fluctuation_load));


function recursif_count_load ($ind, $table, $load)
{
		$maxload=$load;
		$current_start = $table[$ind][0];
		$current_end = $table[$ind][1];
		
		for ($k=$ind+1; $k<=count($table); $k++){
			if ($table[$k][0]<= $current_end){				
				$load = recursif_count_load ($k, $table, $load+1);
				if ($load > $maxload) $maxload=$load;			
			}else{
				break;			
			}		
		}
		if ($k<count($table)) $load = recursif_count_load ($k, $table, $load);
		if ($load > $maxload) $maxload=$load;
		return $maxload;		
}



// Some data
for ($j=0; $j <= 59; $j++){
 	if ($j==-1) 
		$datax[] = '';
	else 
		$datax[] = sprintf("%02d",$j);

	//$datax = array("","00","01","02","03","04","05","06","07","08","09","10","01","02","03","04","05","06","07","08","09","10","01","02","03","04","05","06","07","08","09","10","01","02","03","04","05","06","07","08","09","10","01","02","03","04","05","06","07","08","09","10");
}	


/*
$nbcall=20;
$mycall_min[0]=0;
$mycall_dur[0]=0;
for ($i=1; $i <= $nbcall; $i++){
	$mycall_min[$i] = rand(0,59); // num min INPUT
	$mycall_dur[$i] = rand(40,1500); // num sec sessiontime INPUT
}*/


sort ($mycall_min);
//print_r($mycall_min);

$lineSetWeight = 500 / $nbcall; 
for ($k=1; $k <= $nbcall; $k++){
	$mycall_dur[$k] = intval($mycall_dur[$k] /60)+1;	
	
	for ($j=0; $j <= 59; $j++){
		if ($j==-1){
			$datay[$k][]='';
		}else{
			if ($j==$mycall_min[$k]){
				$datay[$k][]=$k*1;
			}elseif ($j>$mycall_min[$k]){ // CHECK SESSIONTIME
				
				if ( ($mycall_min[$k]+$mycall_dur[$k]) >= $j ) $datay[$k][]=$k*1;
				else $datay[$k][]='';
			
			}else{ // FILL WITH BLANK
				$datay[$k][]='';
			}	
		}
	}	
}
//print_r($datay);
//exit();

/*
$table_colors[]="yellow@0.3";
$table_colors[]="purple@0.3";
$table_colors[]="green@0.3";
$table_colors[]="blue@0.3";
$table_colors[]="red@0.3";
*/

$myrgb = new RGB();
foreach ($myrgb -> rgb_table as $minimecolor){
	$table_colors[]= $minimecolor;
}




/*****************************************************/
/* 		  2 GRAPH - FLUCTUATION & WATCH TRAFFIC	 	 */
/*****************************************************/

if ($typegraph == 'fluctuation'){
		// Setup the graph
		
		$width_graph=750;
				
		if (count($fluctuation_load)>200){
			$multi_width = intval(count($fluctuation_load)/90);
			$width_graph  =$width_graph * $multi_width;
		}
		$graph = new Graph($width_graph,450);
		$graph->SetMargin(40,40,45,90); //droit,gauche,haut,bas
		$graph->SetMarginColor('white');
		//$graph->SetScale("linlin");
		$graph->SetScale("textlin");
		$graph->yaxis->scale->SetGrace(3);
		
		// Hide the frame around the graph
		$graph->SetFrame(false);
		
		// Setup title
		$graph->title->Set("Graphic");
		//$graph->title->SetFont(FF_VERDANA,FS_BOLD,14);
		
		// Note: requires jpgraph 1.12p or higher
		$graph->SetBackgroundGradient('#FFFFFF','#CDDEFF:0.8',GRAD_HOR,BGRAD_PLOT);
		$graph->tabtitle->Set("$fromstatsmonth_sday-$fromstatsday_sday Hourly Graph - FROM $hourinterval to $hourintervalplus - NBCALLS $nbcall - "."MAX LOAD = $maxload");
		$graph->tabtitle->SetWidth(TABTITLE_WIDTHFULL);
		
		// Enable X and Y Grid
		$graph->xgrid->Show();
		$graph->xgrid->SetColor('gray@0.5');
		$graph->ygrid->SetColor('gray@0.5');
		
		$graph->yaxis->HideZeroLabel();
		$graph->xaxis->HideZeroLabel();
		$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#CDDEFF@0.5');
		
		
		//$graph->xaxis->SetTickLabels($tableau_hours[0]);
		
		// initialisaton fixe de AXE X
		
		$graph->xaxis->SetTickLabels(array_keys($fluctuation_load)); 
		
		
		
		// Setup X-scale
		//$graph->xaxis->SetTickLabels($tableau_hours[0]);
		$graph->xaxis->SetLabelAngle(90);
		
		
		// Format the legend box
		$graph->legend->SetColor('firebrick1');
		
		$graph->legend->SetFillColor('gray@0.8');
		$graph->legend->SetLineWeight(2);
		$graph->legend->SetShadow('gray@0.4',3);
		$graph->legend->SetPos(0.1,0.15,'left','left');
		$graph->legend->SetMarkAbsSize(1);
		$graph->legend->SetFont(FF_FONT1,FS_BOLD); 
		
		
		
		$indgraph=0;
		
		
		
			$bplot[$indgraph] = new BarPlot(array_values($fluctuation_load));
			
			//$bplot[$indgraph]->SetColor($table_colors[$indgraph]);
			$bplot[$indgraph]->SetWeight(1);
			$bplot[$indgraph]->SetFillColor('orange');
			$bplot[$indgraph]->SetShadow('black',1,1);
			
			$bplot[$indgraph]->value->Show();	
			$bplot[$indgraph]->SetLegend("MAX LOAD = $maxload");
			
			$graph->Add($bplot[$indgraph]);
			$indgraph++;	
		
		
		// Output the graph
		$graph->Stroke();



}else{


		
		
		
		
		$graph = new Graph(750,800);
		$graph->SetMargin(60,40,45,90); //droit,gauche,haut,bas
		$graph->SetMarginColor('white');
		//$graph->SetScale("linlin");
		$graph->SetScale("textlin");
		$graph->yaxis->scale->SetGrace(1,1);
		
		
		
		
		// Hide the frame around the graph
		$graph->SetFrame(false);
		
		// Setup title
		$graph->title->Set("Graphic");
		//$graph->title->SetFont(FF_VERDANA,FS_BOLD,14);
		
		// Note: requires jpgraph 1.12p or higher
		$graph->SetBackgroundGradient('#FFFFFF','#CDDEFF:0.8',GRAD_HOR,BGRAD_PLOT);
		$graph->tabtitle->Set("$fromstatsmonth_sday-$fromstatsday_sday Hourly Graph - FROM $hourinterval to $hourintervalplus - NBCALLS $nbcall - "."MAX LOAD = $maxload");
		$graph->tabtitle->SetWidth(TABTITLE_WIDTHFULL);
		
		//$graph->yaxis->Hide();
		// Enable X and Y Grid
		//$graph->xgrid->Show();
		$graph->xgrid->SetColor('gray@0.5');
		$graph->ygrid->SetColor('gray@0.5');
		
		//$graph->yaxis->HideZeroLabel();
		$graph->xaxis->HideZeroLabel();
		$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#CDDEFF@0.5');
		
		$graph->xaxis->SetTickLabels($datax);
		$graph->xaxis->SetLabelAngle(90);
		
		
		$graph->yaxis->HideFirstLastLabel();
		//$graph->yaxis->HideLine();
		$graph->yaxis->HideTicks(); 
		$graph->yaxis->SetLabelFormatString('%1d call'); 
		$graph->yaxis->SetTextLabelInterval(2); 
		
		
		// Format the legend box
		$graph->legend->SetColor('firebrick1');
		
		$graph->legend->SetFillColor('gray@0.8');
		$graph->legend->SetLineWeight(2);
		$graph->legend->SetShadow('gray@0.4',3);
		$graph->legend->SetPos(0.2,0.2,'left','center');
		$graph->legend->SetMarkAbsSize(1);
		$graph->legend->SetFont(FF_FONT1,FS_BOLD); 
		
		for ($i=1;$i<=count($datay);$i++){
		
			// Create the first line
			$p1[$i] = new LinePlot($datay[$i]);	
			$p1[$i]->SetColor($table_colors[($i+20) % 436]  );
			$p1[$i]->SetCenter();
			$p1[$i]->SetWeight($lineSetWeight);
			if ($i==1) $p1[$i]->SetLegend("MAX LOAD = $maxload");
			$graph->Add($p1[$i]);
			
		}
		
		// Output line
		$graph->Stroke();
		
		
		
}//END IF (typegraph)


?>
