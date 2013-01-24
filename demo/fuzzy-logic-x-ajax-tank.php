<?php
error_reporting(5);
define ("LINFINITY"	,	-1 );
define ("TRIANGLE" 	,	 0 );
define ("RINFINITY"	,	 1 );
define ("TRAPEZOID"	,	 2 );

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


class Member {
	
	protected
		$FName,   // Member Name
		$FMiddle, // Middle Member point
		$FA,      // Start Member point
		$FB,	  // End member point
		$FType;	  // Member type TRIANGLE,LINFINITY,RFINFINITY, TRAPEZOID
				
	public function __construct($Name=NULL,$start=NULL,$medium=NULL,$stop=NULL,$type=NULL){
		if($Name == NULL) return;
		$this->FName 	= $Name;
		$this->FMiddle 	= $medium;
		$this->FA 		= $start;
		$this->FB 		= $stop;
		$this->FType  	= $type;
	}

	public function __toString(){
		return "Member\tname: $this->FName, 
		middle: $this->FMiddle, 
		start : $this->FA,
		fb    : $this->FB,
		type  : $this->FType";
	}
	

/**
* Calculates the ratio of belonging to a set of defined function shape.
*    $ratio = Fuzzification($pontX);
* @param   float 	$poinX
* return   float  
*/
	public function Fuzzification($P=0.0) {
	
    if (($P<$this->FA) OR ($P>$this->FB)) return 0; //P is out this segment...

	if ($P==$this->FMiddle) return 1;

	 if ($this->FType==LINFINITY) {
	    if ($P<=$this->FMiddle) return 1;
		if (($P>$this->FMiddle) AND ($P<$this->FB)) return ($this->FB-$P)/($this->FB-$this->FMiddle);
	 }
	
	 if ($this->FType==RINFINITY) {
	    if ($P>=$this->FMiddle) return 1;
		if (($P<$this->FMiddle) AND ($P>$this->FA)) return ($P-$this->FA)/($this->FMiddle-$this->FA);
	 }

	if ($this->FType==TRIANGLE) {
	   if(($P<$this->FMiddle) AND ($P>$this->FA)) return ($P-$this->FA)/($this->FMiddle-$this->FA);
	   if(($P>$this->FMiddle) AND ($P<$this->FB))  return ($this->FB-$P)/($this->FB-$this->FMiddle);
	 }

	 if ($this->FType==TRAPEZOID) {
	   if(($P<$this->FMiddle[0]) AND ($P>$this->FA)) return ($P-$this->FA)/($this->FMiddle[0]-$this->FA);
	   if(($P>$this->FMiddle[1]) AND ($P<$this->FB))  return ($this->FB-$P)/($this->FB-$this->FMiddle[1]);
	   if (($P>=$this->FMiddle) and ($P<=$this->FMiddle))  return 1;
	 }
	return 0;
}

} // class Member
	
class Fuzzify extends Member {
	
	protected  $FMin	=	array();
	protected  $FMax	=	array();
	protected $members = array();
	
	public function setMinMax($idx,$A=0,$B=0) {
		If ($A<=$B) { 
		$this->FMin[$idx] 	=	$A;
		$this->FMax[$idx] 	= 	$B;
		} else {
			$this->FMin[$idx]   = 	$B;
			$this->FMax[$idx]	=	$A;
		}
	}
	
	public function clearMembers() {
		$this->members = NULL;
	}
	
	public function addMember($idx,$Name='New',$start=0.0,$medium=0.0,$stop=0.0,$type=TRIANGLE) {		
		$member = new Member($Name,$start,$medium,$stop,$type);
		if ($member->FA < (float)$this->FMin[$idx]) $this->setMinMax($idx, $member->FA ,(float)$this->FMax[$idx]);
		if ($member->FB > (float)$this->FMax[$idx]) $this->setMinMax($idx , (float)$this->FMin[$idx] , $member->FB);
		$this->members[$idx][] = $member; 
	}
	
	public function setMembers($idx,$m = array()) {
		$this->members[$idx] = $m;
	}
	
	public function getMembers($idx,$id = NULL) {
		if ($id) return $this->members[$idx][$id];
		else return $this->members[$idx];
	}
	
	public function getMembersIndex($idx,$value = NULL) {
		foreach ($this->members[$idx] as $idx => $member)	
			if ($value==$member->FName) return $idx;
		return FALSE;
	}
	
	public function getMemberByName($idx,$name = NULL) {
		foreach ($this->members[$idx] as $idx => $member)	
			if ($name==$member->FName) return $member;
		return FALSE;
	}
	
	} // end class fuzzify
	
class Rules extends Fuzzify{

	public	$FXValues 		= 	array();
	public	$FYValues 		= 	array();
	public	$FRealInput		=	array();
	public	$FOutputs		=	array();

	private $InputNames = NULL;
	private $OutputNames = NULL;
	private $rules = NULL;
	
/**
* Set private properties $this->InputNames
* example :
*    $fuzzy->setInputNames(array('Name1','Name2'));
* @param   array of string
* return   none  
**/
	
	public function setInputNames($val) {
		$this->InputNames = $val;
	}

/**
* Set private properties $this->OutputNames
* example :
*    $fuzzy->setOutputNames(array('Name1','Name2'));
* @param   array of string
* return   none  
**/
	
	public function setOutputNames($val) {
		$this->OutputNames = $val;
	}

/**
* Return private properties $this->InputNames
* example :
*    $names = $fuzzy->getInputNames();
* @param   none
* return   array of string  
**/
	
	public function getInputNames() {
		return $this->InputNames;
	}
	
/**
* Return private properties $this->OutputNames
* example :
*    $names = $fuzzy->getOututNames();
* @param   none
* return   array of string  
**/
	
	public function getOutputNames() {
		return $this->OutputNames;
	}

/**
* Return private properties $this->InputNames[$idx]
* example :
*    $names = $fuzzy->getInputNames($idx);
* @param   none
* return   string  
**/
	
	public function getInputName($idx) {
		return $this->InputNames[$idx];
	}
	
/**
* Return private properties $this->OutputNames[$idx]
* example :
*    $names = $fuzzy->getOutputNames($idx);
* @param   none
* return   string  
**/
	
	public function getOutputName($idx=0) {
		return $this->OutputNames[$idx];
	}
	
/**
* Clear all rules
* example :
*    $fuzzy->clearRules();
* @param   none
* return   none  
**/
	
	public function clearRules() {
		$this->rules = NULL;
	}
	
/**
* Return array as Rules
* example :
*    $fuzzy->getRules();
* @param   none
* return   array of string  
**/
	
	public function getRules() {
		return $this->rules;
	}
	
/**
* Return Rule as String
* example :
*    $fuzzy->getRule($id);
* @param   integer $id (key)
* return   string  
**/
	
	public function getRule($id) {
		return $this->rules[$id];
	}

/**
* Add Rule as String to private properties Rules Array 
* example :
*    $fuzzy->addRule('IF input1.High AND input2.Slow Then Out1.Run');
* @param   string
* return   none  
**/
	
	public function addRule($val) {
		$this->rules[] = $val;
	}
	
/**
* Find parenthis fragments of Rule as string
* example :
*    $fragment = $this->rSplit('IF input1.High AND (input2.Slow OR input3.Fast) Then Out1.Run');
* @param   string
* return   string  'input2.Slow OR input3.Fast' 
**/
	
	private function rSplit($string) {
		if (preg_match("/\((([^()]*|(?R))*)\)/",$string,$matches))		
		return $matches[1];
	}
/**
* Get last nested parenthis fragments of Rule as string
* example :
*    $fragment = $this->getLastParent('IF input1.High AND (input2.Slow OR (input3.Fast AND input4.Warm)) Then Out1.Run');
* @param   string
* return   string  'input3.Fast AND input4.Warm' 
**/	
	private function getLastParent($a) {
	do {
	$a = $this->rSplit($a);
	if ($a) $ret=$a;
	} while($a);
	return $ret;
	}

/**
* Fuzzy Logic OR operation on array of values
* example :
*    $val = $this->_FuzzyOR(array(1,0.5));
* @param   array float values
* return   float (max value) = 0.5 
**/
	
	private function _FuzzyOR($arr) {
		return (max($arr));
	}
	
/**
* Fuzzy Logic OR operation on array of values
* example :
*    $val = $this->rSplit(array(1,0.5));
* @param   array float values
* return   float (min value) = 1 
**/	
	private function _FuzzyAND($arr) {
		return (min($arr));
	} 
	
/**
* Calculate Rule.Parser And Interpreter for Rule. 
* Rule has text line example:
*    IF input1.High AND input2.Slow Then Out1.Run
* Where
*      Operator  : IF
*      Argument Input 1 : input1.High
*                  where InputName     = input1
* 	    				DotSeparator  = .
* 	    				 MemberName   = Run
*      Operator  : AND   Operation for Arg1 , Arg 2 values
*      Argument Input 2 : input2.Slow
*                  where    InputName  = input2
* 	    			DotSeparator  = .
* 	    			   MemberName = Slow
*      Operator  : Then
*      Argument Output 1  : Out1.Run (InputName,DotSeparator,MemberName)
*
*
* example :
*    $val = $fuzzy->processRule('IF input1.High AND input2.Slow Then Out1.Run');
* @param   string Rule
* return   float (calculated fuzzy value as Rule cryteria)
**/	
	
	public function processRule($rule) {
		while ($in_parent = $this->getLastParent($rule)) {	
			$pos=strpos($rule,$in_parent);
			$len=strlen($in_parent);
			$tmparr=array();
			$items=preg_split("/\s+/",$in_parent);
			$operation='and';
			foreach($items as $item) {
				$inp=strtolower($item);
				if (($inp=='or') or ($inp=='and')) $operation=$inp; 	else   {
					list($inputName,$memberName) = preg_split("/\./",$item);
					// get value from 
					$mem_idx = $this->getMembersIndex($inputName,$memberName);
					$tmparr[] =$this->FOutputs[$inputName][$mem_idx];
					}
			}
		$value1 = ($operation == 'or') ? $this->_FuzzyOR($tmparr) : $this->_FuzzyAND($tmparr);
		$rule=substr($rule,0,$pos-1).$value1.substr($rule,$pos+$len+1); 
		}
		
		$items=preg_split("/\s+/",$rule);
		$operation='and';
		$firstop = array_shift($items);
		$outitem = array_pop($items);
		$tmparr=array();
		foreach($items as $item) {
			$inp=strtolower($item);
			if (($inp=='or') or ($inp=='and')) $operation=$inp; 
				elseif (($inp=='then') or ($inp=='for'))  continue;
					else {
						// split names
						list($inputName,$memberName) = preg_split("/\./",$item);
						// get value from FOutputs 
						$mem_idx = $this->getMembersIndex($inputName,$memberName);
						$tmparr[] =$this->FOutputs[$inputName][$mem_idx];
						}
		}
		
		$value1 = ($operation == 'or') ? $this->_FuzzyOR($tmparr) : $this->_FuzzyAND($tmparr);
		return array($outitem,$value1);
	}
	
} //class Rules	

class Fuzzy_Logic extends Rules {
				
	protected  $FuzzyTable 	=	NULL;
	public 	$StateOutput    =	array();
	protected	$AgregatePoints =   100;
/*
* Clear all solutions arrays in class and next calculations is Ready
* example :
*    $this->ClearSolution();
* @param   none
* return   none 
*/			
	private function ClearSolution() {
	$this->FXValues			=	array();
	$this->FYValues			=	array();
	}
	
/*
* Set protected properties FuzzyTable
* example :
*     $x->setFuzzyTable(array(
*     //      IF input1   AND input2 Then Output
*     //      For OR use pair input1 , NULL
*     //                      NULL   , input 2
*     //       ------       -------       -------         
*     	array('adequate',	   NULL  ,		'low'),
*     	array(NULL ,	     'small'  ,		'low'),
*     	array('marginal',	 'large' ,		'normal'),
*     	array('inadequate' , NULL ,	    'high'),
*     ));
* @param   array
* return   none 
*/	

	
	public function SetFuzzyTable($A = array()) {
	$this->FuzzyTable = $A;
	}
		
/*
* Set Real Input Value =$X for Input named $idx 
* example :
*    $fuzzy->SetRealInput('input1',0.23);
* @param   string $idx 
* @param   float  $X
* return   none 
*/	

	public function SetRealInput($idx,$X = 0.0) {
		$this->FRealInput[$idx]	=	$X;
		$this->FOutputs[$idx] = array();
		For ($i=0;$i<count($this->members[$idx]);$i++) {
			$this->FOutputs[$idx][]	=	$this->members[$idx][$i]->Fuzzification($this->FRealInput[$idx]); 
		}
	}

/*
* Agregate All Rules Result for Defuzification 
* example :
*    $fuzzy->FuzzyAgregate($outname,$Member,$AlphaCut=0.0)
* @param   string $output_name 
* @param   object $member_object
* @param   float $calculate_rule_value
* return   none 
*/	
	
	public function FuzzyAgregate($outname,$Member,$AlphaCut=0.0) {
		foreach($this->FXValues[$outname] as $index=>$pointX) {
			if ($pointX<$Member->FA) continue;
			if ($pointX>$Member->FB) break;
			$ms = $Member->Fuzzification($pointX);
			$mem_val = min($ms,$AlphaCut);
			$this->FYValues[$outname][$index] = max($this->FYValues[$outname][$index],$mem_val);	
		}
	}

/*
* Calculate Defuzification Fuzzy Result for method AVG (required set FuzzyTable)
* example :
*    $fuzzy->calcFuzzy()
* @param   none 
* return   array of outputs values (no associated keys)
*/		
	
	public function calcFuzzyAlt() {	
		$MaxAverage = 0;
		$this->ClearSolution();
		//$count_inputs = count($this->InputNames);
		$sum = 0;
		$tmpx=array();
		$sum = array();
		$cnt = array();
		// fill output agregate table
		foreach($this->getOutputNames() as $outname) {
		$AgregateDeltaX = ($this->FMax[$outname]-$this->FMin[$outname])/$this->AgregatePoints;	 
		$this->FXValues[$outname] = Range($this->FMin[$outname],$this->FMax[$outname],$AgregateDeltaX);
		$this->FYValues[$outname] = array_fill ( 0 , count($this->FXValues[$outname]), 0.0 );
		}
		
		foreach ($this->FuzzyTable as $row_idx => $line_rule) {
				$sum = 0.0;
				$cnt = 0.0;
				$count_inputs = count($line_rule)-1; // last is output
				foreach ($line_rule as $col => $member_name) {
				$out_idx =$col - $count_inputs;
				$outname = $this->getOutputName($out_idx);
					if (!is_null($member_name)) {
					
						if ($col<$count_inputs) { // is input
							$inp_name = $this->getInputName($col);
							$mem_idx = $this->getMembersIndex($inp_name,$member_name);
							$val =$this->FOutputs[$inp_name][$mem_idx]; // get members value
							if ($val>0) { 
								$sum+=$val; // sum members values
								$cnt++;	
								} else {
								//$sum=0;
								//$cnt=0;
								break;
								}
						} else { //is output
	
								$member=$this->getMemberByName($outname,$member_name); // get OUTPUT member
								if ($cnt == 0) $avg_sum = 0; else $avg_sum = $sum/$cnt;
								$this->StateOutput[$member_name] = $avg_sum;
								if ($avg_sum>0)  $this->FuzzyAgregate($outname,$member,$avg_sum);
								$sum = 0.0;
								$cnt = 0.0;
						}
					} // if $member_name
				} // foreach rule	
			} // foreach rule_row
	
		$result = array();
		
		foreach($this->getOutputNames() as $outname) {
		$suma=0.0;
		$sumb=0.0;
		
		foreach($this->FXValues[$outname] as $id=>$x) {
			$y=$this->FYValues[$outname][$id];
			if ($y>0) {
			$suma+=($x*$y);
			$sumb+=$y;
			}
		}	
		if ($sumb == 0) $result[]= 0; else	$result[] = $suma/$sumb;	
		}
	return $result;
}
	
/*
* Calculate Defuzification Fuzzy Result for method MIN,MAX (required set Rules)
* example :
*    $fuzzy->calcFuzzyAlt()
* @param   none 
* return   array of outputs values (associated keys)
*/		
	
	public function calcFuzzy() {	
		$this->ClearSolution();
				
		$sum = 0;
		$tmpx=array();
		$sum = array();
		$cnt = array();
		// fill output agregate table
		foreach($this->getOutputNames() as $outname) {
		$AgregateDeltaX = ($this->FMax[$outname]-$this->FMin[$outname])/$this->AgregatePoints;	 
		$this->FXValues[$outname] = Range($this->FMin[$outname],$this->FMax[$outname],$AgregateDeltaX);
		$this->FYValues[$outname] = array_fill ( 0 , count($this->FXValues[$outname]), 0.0 );
		}
		
		$rules=$this->getRules();
		foreach ($rules as $key=>$rule) {
			 list($outItem,$value) = $this->processRule($rule);
			 list($outputName,$memberName) = preg_split("/\./",$outItem);
			 $this->StateOutput[$memberName] = $value;
			 $member=$this->getMemberByName($outputName,$memberName); // get OUTPUT member
			 if ($value>0)  $this->FuzzyAgregate($outputName,$member,$value);
			}
	
		$result = array();
		
		foreach($this->getOutputNames() as $outname) {
		$suma=0.0;
		$sumb=0.0;
		
		foreach($this->FXValues[$outname] as $id=>$x) {
			$y=$this->FYValues[$outname][$id];
			if ($y>0) {
			$suma+=($x*$y);
			$sumb+=$y;
			}
		}	
		if ($sumb == 0) $result[$outname]= 0; else	$result[$outname] = $suma/$sumb;	
		}
	return $result;
}

} //class



/*--------------------------- start tank demo controll --------*/ 
$x = new Fuzzy_Logic();

$x->clearMembers();
/* ---------- set input members ---------*/
$x->setInputNames(array('ERROR','RATE'));

$x->addMember($x->getInputName(0),'E_NEG',-1     ,-0.9 , 0    ,LINFINITY);
$x->addMember($x->getInputName(0),'E_OK' ,-0.1 , 0   , 0.1,TRIANGLE);
$x->addMember($x->getInputName(0),'E_POS', 0     , 0.9 ,  1  ,RINFINITY);

$x->addMember($x->getInputName(1),'R_NEG',-0.10,-0.07 ,0    ,LINFINITY);
$x->addMember($x->getInputName(1),'R_OK', -0.07, 0    ,0.07 ,TRIANGLE);
$x->addMember($x->getInputName(1),'R_POS', 0   , 0.07 ,0.1  ,RINFINITY);

/* ---------- set output members ---------*/
$x->setOutputNames(array('OUT'));

$x->addMember($x->getOutputName(0),'CF',-1.0, -0.9 ,-0.8 ,TRIANGLE);
$x->addMember($x->getOutputName(0),'CS',-0.6, -0.5 ,-0.4 ,TRIANGLE);
$x->addMember($x->getOutputName(0),'NC',-0.1,  0.0 , 0.1 ,TRIANGLE);
$x->addMember($x->getOutputName(0),'OS', 0.4,  0.5 , 0.6 ,TRIANGLE);
$x->addMember($x->getOutputName(0),'OF', 0.8,  0.9 , 1.0 ,TRIANGLE);


/* ---------- set rules table ------------ */
$x->clearRules();

$x->addRule('IF ERROR.E_NEG THEN OUT.CF');
$x->addRule('IF ERROR.E_OK THEN OUT.NC');
$x->addRule('IF ERROR.E_POS THEN OUT.OF');
$x->addRule('IF ERROR.E_OK AND RATE.R_POS THEN OUT.CS');
$x->addRule('IF ERROR.E_OK AND RATE.R_NEG THEN OUT.OS');

/*------------ Remote parameters----------------*/


$get = $_GET['table'];

$tankLevel 			= $get["currTanksLevel"];
$tankLastLevel		= $get["lastTanksLevel"];
$tankValve 	 		= $get["valveTanksInput"];

$set = $_GET['setmode'];
 
$InputMaxValve 		= $set["mainInput"];
$OutputXMaxValve	= $set["mainOutputX"];
$OutputYMaxValve	= $set["mainOutputY"];
$tankDemandLevel	= $set["demandTanksLevel"];
$tankInpMax  		= $set["remoteTanksInput"];
$tankOutMax	 		= $set["remoteTanksOutput"];
$tankMaxWorkLevel   = $set["tankMaxWorkLevel"];	
$tankMinWorkLevel   = $set["tankMinWorkLevel"];	


/*------------- Valve parameters -------------*/
$ToutX_stream     = 0.02  ;     // output stream by time period  
$ToutY_stream     = 0.03  ;     // output stream by time period  
$Tinp_stream     =  0.07  ;
/*-------------- Initialise ------------------*/

$tankName          = array('A',	'B',	'C',	'D',	'E', 	'X'	, 	'Y'	);
$tankInputRefKey   = array(-1 ,	0  ,	1  ,	2  ,	 3  , 	4	, 	3  	);
$tankOutputRefKey  = array(0  ,	1  ,	2  ,	3  ,	 4  , 	-1	, 	4 	);

$tankMaxLevel	= array(1.0,1.0,1.0,1.0,1.0,1.0,1.0);
$tankInpDT 	 	= array(0.07,0.07,0.07,0.07,0.07,0.07,0.07);
$tankOutDT 	 	= array(0.05,0.05,0.05,0.05,0.05,0.05,0.05);
$tankFuzzy 	 	= array(0.0,0.0,0.0,0.0,0.0,0.0,0.0);

/* ----------- Fuzzy Controll Loop ----------------*/
	for($tank=0;$tank<7;$tank++) {
	    $name=$tankName[$tank];
		list($erate,$elevel) = getLastErrors($tankDemandLevel[$tank],$tankLevel[$tank],$tankLastLevel[$tank]);
		$x->SetRealInput('ERROR',	$elevel	);
		$x->SetRealInput('RATE' ,	$erate	);
		$fuzzy_arr = $x->calcFuzzy();	
		$tankFuzzy[$tank] = $fuzzy_arr['OUT'];
		$tankLastLevel[$tank] = $tankLevel[$tank];
		$key_input_ref = $tankInputRefKey[$tank];
		if($key_input_ref>=0)
				$Vinp_max = $tankInpMax[$tank]*$tankInpDT[$tank]*(int)(($tankLevel[$tank]<=$tankMaxWorkLevel[$tank]) and ($tankLevel[$key_input_ref]>$tankMinWorkLevel[$key_input_ref]));
		else
		        $Vinp_max = $InputMaxValve*$Tinp_stream *(int)($tankLevel[$tank]<$tankMaxWorkLevel[$tank]);
						
		list($tankValve[$tank],$WaterInput) = getValve($tankFuzzy[$tank],$tankValve[$tank],$Vinp_max,0,1);

		$key_output_ref = $tankOutputRefKey[$tank];

		if($key_output_ref>=0)
				$VOut_max = $tankOutMax[$tank]*$tankOutDT[$tank]*(int)($tankLevel[$key_output_ref]>$tankMinWorkLevel[$key_output_ref]);
		else //-1
		        $VOut_max = $OutputXMaxValve*$ToutX_stream*(int)($tankLevel[$tank]>$tankMinWorkLevel[$tank]);        
		
		$tankLevel[$tank] = min($tankMaxLevel[$tank],$tankLevel[$tank] - $VOut_max + $WaterInput);	
	}


$out =array(
"currTanksLevel"	=>  $tankLevel 			,
"lastTanksLevel"	=>  $tankLastLevel		,
"valveTanksInput"	=>  $tankValve 	 		);

echo json_encode($out);

?>