<?php
require_once ('../fuzzy_logic.php');
/*--------------------------- start tank demo controll --------*/ 
$x = new Fuzzy_Logic();

$x->clearMembers();
/* ---------- set input members ---------*/
$x->setInputNames(array('ERROR','RATE'));

$x->addMember($x->getInputName(0),'E_NEG',-1     ,-0.9 , 0    ,LINFINITY);
$x->addMember($x->getInputName(0),'E_OK' ,-0.1 , 0   , 0.1,TRIANGLE);
$x->addMember($x->getInputName(0),'E_POS', 0     , 0.9 ,  1  ,RINFINITY);
                      
$x->addMember($x->GetInputName(1),'R_NEG',-0.10,-0.07 ,0    ,LINFINITY);
$x->addMember($x->GetInputName(1),'R_OK', -0.07, 0    ,0.07 ,TRIANGLE);
$x->addMember($x->GetInputName(1),'R_POS', 0   , 0.07 ,0.1  ,RINFINITY);

/* ---------- set output members ---------*/
$x->setOutputNames(array('OUT'));

$x->addMember($x->getOutputName(0),'CF',-1.0, -0.9 ,-0.8 ,TRIANGLE);
$x->addMember($x->getOutputName(0),'CS',-0.6, -0.5 ,-0.4 ,TRIANGLE);
$x->addMember($x->getOutputName(0),'NC',-0.1,  0.0 , 0.1 ,TRIANGLE);
$x->addMember($x->getOutputName(0),'OS', 0.4,  0.5 , 0.6 ,TRIANGLE);
$x->addMember($x->getOutputName(0),'OF', 0.8,  0.9 , 1.0 ,TRIANGLE);
/* ---------- set rule table ------------ */

/* ---------- set rule table ------------ */
/* ---------- set rule table ------------ */
$x->clearRules();

$x->addRule('IF ERROR.E_NEG THEN OUT.CF');
$x->addRule('IF ERROR.E_OK THEN OUT.NC');
$x->addRule('IF ERROR.E_POS THEN OUT.OF');
$x->addRule('IF ERROR.E_OK AND RATE.R_POS THEN OUT.CS');
$x->addRule('IF ERROR.E_OK AND RATE.R_NEG THEN OUT.OS');

$x->setFuzzyTable(array(
//       IN(0,0)	IN(0,1)	    OUT(0)
//       ------   -------       -------         
	array('E_NEG',	NULL ,		'CF'),
	array('E_OK' ,	NULL ,		'NC'),
	array('E_POS',	NULL ,		'OF'),
	array('E_OK' ,	'R_POS',	'CS'),
	array('E_OK' ,	'R_NEG',	'OS') 	
));

   
/*------------ Tank parameters----------------*/
$Theight         = 1.0  ;  // tank max level
$TEmergencyRange = array(0.1,0.1,0.1,0.1);
$Tinit           = 0.1  ;  // tank init
$IitA            = 0.1  ;
$IitB            = 0.1  ;
$IitC            = 0.1  ;
$IitD            = 0.1  ;
$InputValve      = 1.0 ;
$Tdemand         = 0.7  ;  // tank demand level
$Toverflow       = 0.9  ;  // tank overflow signal


/*------------- Valve parameters -------------*/
$Tout_stream     = 0.03  ;  // output stream by time period     
$Vinp_max        = 0.05  ;  // max input stream for power valve
/*-------------- Initialise ------------------*/
$Level          = $Tinit;
$LastLevel 		= $Level;
$elevel 		= $Tinit;
$erate  		= 0;
$Vinput         = 0;
$Valve 			= 0;
$Fuzzy          = 0;
$WaterInput     = 0;
echo "<pre>\n";
echo "This is a simulation of fuzzy-logic controller. The controller controls the desired level of water in the tank. 
 The controller closes the water inlet valve so as to offset the amount of water flowing from the water flowing. 
 Parameters
 ------------
 Valve adjustment range 0 to 1 
 The amount of water input flowing per unit time: $Vinp_max
 The amount of water output flowing per unit time: $Tout_stream
 Startup water level: $Tinit  
 Desired level of water: $Tdemand 
 ------------
 Plot Values: 
 ------------
 L - current level 
 V - the level of the valve opening 
 F - error output controller 
 -------------- 
";
/* ----------- Controll Loop ----------------*/
for ($i=1;$i<=80;$i++) {
//if ($i>40) $Tout_stream = 0.0;


//if ($i>40) $WaterInput = 0;
list($erate,$elevel) = getLastErrors($Tdemand,$Level,$LastLevel);

$x->SetRealInput('ERROR',	$elevel	);
$x->SetRealInput('RATE' ,	$erate	);

//$fuzzy_arr = $x->calcFuzzyAlt();
//
//$Fuzzy = $fuzzy_arr[0];
$fuzzy_arr = $x->calcFuzzy();

$Fuzzy = $fuzzy_arr['OUT'];

//print_r($fuzzy_arr);
$LastLevel = $Level;
$Level = $Level - $Tout_stream + $WaterInput;
list($Valve,$WaterInput) = getValve($Fuzzy,$Valve,$Vinp_max,0,1);

$dotline = str_repeat('.',100);
$dotline[round($Level*99)]='#';
echo $dotline,sprintf("L:%1.3f F:%1.3f V:%1.3f",$Level,$Fuzzy,$Valve),"\n";
}
print_r($x->StateOutput);
echo "</pre>\n";

/*---- required additional function ------------*/
function getValve($Fuzzy,$Valve,$inpMax,$lb,$ub) {    
    $xdot = $Valve+$Fuzzy; 
	if ($xdot<=$lb) $Valve = $lb;
	elseif ($xdot>=$ub) $Valve = $ub;
	else $Valve=min(max($xdot,$lb),$ub);
return array($Valve,$Valve * $inpMax);
}

function getLastErrors($demandLevel,$currentLevel,$lastLevel) {
    $erate =  $currentLevel-$lastLevel;
    if ($erate < -0.1) $erate =-0.1;
    if ($erate > 0.1) $erate = 0.1;
    $elevel = $demandLevel-$currentLevel ;
return array($erate,$elevel);
}
?>