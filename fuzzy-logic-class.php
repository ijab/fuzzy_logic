<?php
/***************************************************************
*  
*      (c) 2011 Wojtek Jarzęcki (lottocad(nospam)@gmail.com)
*      All rights reserved
*
*	   BSD Licence
* 
* Date: 2013-01-22
* Modified by: iJab(zhancaibaoATgmail.com)
*  Parse MatLab Rules of Fuzzy Logic
*
***************************************************************/
error_reporting(5);
define ("LINFINITY"	,	-1 );
define ("TRIANGLE" 	,	 0 );
define ("RINFINITY"	,	 1 );
define ("TRAPEZOID"	,	 2 );

class Member {
	
	protected
		$FName,   // Member Name
		$FMiddle, // Middle Member point
		$FA,      // Start Member point
		$FB,	  // End member point
		$FType;	  // Member type TRIANGLE,LINFINITY,RFINFINITY, TRAPEZOID
				
	public function __construct($Name=NULL,$start=NULL,$medium=NULL,$stop=NULL,$type=NULL){
		if(is_null($Name)) return;
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
* Get Name of Membership funcitons.
*    $mf_name = getMemberName();
* @param   void
* return   string  
*/
	public function getMemberName() {
		return $this->FName;
	}
	
/**
* Get Type of Membership funcitons.
*    $mf_type = getMemberType();
* @param   void
* return   string  
*/
	public function getMemberType() {
		return $this->FType;
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
	   if (($P>=$this->FMiddle[0]) AND ($P<=$this->FMiddle[1]))  return 1;
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
		if(!is_null($id))
		{
			return $this->members[$idx][$id];
		}
		else if(!is_null($idx))
		{
			return $this->members[$idx]; 
		}
		else
		{
			return $this->members;
		}
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

	public	$FXValues 		= array();
	public	$FYValues 		= array();
	public	$FRealInput		=	array();
	public  $FInputRange	= array();
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
* @param   $idx Integer
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
* Add Rules based on MatLab's Rule File 
* example :
*    $fuzzy->addRules(array("0 0 0 1 0 0 1, 1 (1) : 1",
												 "0 0 0 1 0 0 2, 1 (1) : 1",));
* @param   array
* return   none  
**/
	
	public function addRules($vals) {
		$ct_inputs = count($this->getInputNames());
		$ct_outputs = count($this->getOutputNames());
		
		foreach($vals as $val)
		{
			// Parse each rule with MatLab format
			$a_rule = explode(',', $val);
			$input_rule = explode(' ', $a_rule[0]);
			$r_rule = explode(':', $a_rule[1]);
			$output_rule = explode('(', $r_rule[0]);
			$tmp = explode(')', $output_rule[1]);
			$oput_put_rule[1] = $tmp[0];
			$op_rule = $r_rule[1];
			
			// Construct String Rule
			$rule_string = 'IF ';
			$op = intval($op_rule) == 1 ? "AND" : "OR";
			
			// Parse input params
			for($ix = 0; $ix < count($input_rule); $ix++)
			{
				$mf_ix = intval($input_rule[$ix])-1;
				if($mf_ix < 0 ) continue;				
				if($ix >= $ct_inputs ) break;
				
				$input_name = $this->getInputName($ix);				
				$input_mf = $this->getMembers($input_name, intval($input_rule[$ix])-1);				
				
				if($ix > 0)
				{
					$rule_string .= ' ' . $op . ' ';
				}				
				$rule_string .= $input_name . "." . $input_mf->getMemberName();
			}
			
			// Parse output params
			$rule_string .= ' Then ';
			$output_name = $this->getOutputName(intval($output_rule[1])-1);
			$output_mf = $this->getMembers($output_name, intval($output_rule[0])-1);
			$rule_string .= $output_name . "." . $output_mf->getMemberName();
			
			$this->rules[] = $rule_string;
		}
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
* Fuzzy Logic NOT operation on array of values
* example :
*    $val = $this->rSplit(array(1,0.5));
* @param   array float values
* return   float (min value) = 1 
**/	
	private function _FuzzyNOT($arr) {
		return 1 - $arr[0];
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
* return   array(float, output) (calculated fuzzy value as Rule cryteria)
**/	
	
  public function processRule($rule) {
        // strip IF
        $ifLess = explode('IF', $rule)[1];
        //get value and outputSTring
        list($value, $outputString) = explode('THEN', $ifLess);
        $toReturn = (array(trim($outputString), $this->evalRule($value)));
        return $toReturn;
    }

    private function evalRule ($fuLo) {
        // if nil return nothing
        if (!isset( $fuLo)) return '';
        $fuLo = trim($fuLo); //get rid of whitespace
        if ($fuLo === '') return '';
        //replace first found inner parentheses with evalRule content of parentheses
        if (strpos('$fuLo', '(') !== FALSE) {
            list($leftOfParentheses, $rightOfOpeningParenthesis) = explode('(', $fulo);
            list($middle, $rightOfParentheses) = explode(')', $fulo);
            return $this->evalRule($leftOfParentheses . ' ' . $this->evalRule($middle) . ' ' . ($rightOfParentheses));
        }
        //else find op and return evalRule(lf) op evalRule(rs) for binary
        //or evalRule( beforeop . (op afterop) )
        //TODO there must be a nicer way to do this, 
        list($leftOfOperator, $rightOfOperator) = explode('AND', $fuLo);
        if ($leftOfOperator !== NULL && $rightOfOperator !== NULL) {
            return $this->_FuzzyAND(array($this->evalRule($leftOfOperator), $this->evalRule($rightOfOperator)));
        }
        list($leftOfOperator, $rightOfOperator) = explode('OR', $fuLo);
        if ($leftOfOperator !== NULL && $rightOfOperator !== NULL) {
            return $this->_FuzzyOR(array($this->evalRule($leftOfOperator), $this->evalRule($rightOfOperator)));
        }
        list($leftOfOperator, $rightOfOperator) = explode('NOT',$fuLo);
        if ($rightOfOperator !== NULL) {
        return $this->evalRule($leftoOfOperator . ' ' . $this->_FuzzyNOT($this->evalRule($rightOfOperator)));
        }
        //ATOM
        //get value from FOutputs
        list($inputName, $memberName) = preg_split("/\./", $fuLo);
        $memIndex = $this->getMembersIndex($inputName, $memberName);
        $toReturn = $this->FOutputs[$inputName][$memIndex];
        return $toReturn;
    }

	
} //class Rules	

class Fuzzy_Logic extends Rules {
				
	protected  $FuzzyTable 	=	NULL;
	public 	$StateOutput    =	array();
	protected	$AgregatePoints =   100;
/*
* Clear all solutions arrays in class and next calculations is Ready
* example :
*    $this->clearSolution();
* @param   none
* return   none 
*/			
	private function clearSolution() {
	$this->FXValues			=	array();
	$this->FYValues			=	array();
	}
	
/**
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
**/	

	
	public function setFuzzyTable($A = array()) {
	$this->FuzzyTable = $A;
	}
		
/**
* Set Real Input Value @param2 for Input named @param1 
* example :
*    $fuzzy->setRealInput('input1',0.23);
* @param1   string $idx 
* @param2   float  $X
* return   none 
**/	

	public function setRealInput($idx,$X = 0.0) {
		$this->FRealInput[$idx]	=	$X;
		$this->FOutputs[$idx] = array();
		For ($i=0;$i<count($this->members[$idx]);$i++) {
			$this->FOutputs[$idx][]	=	$this->members[$idx][$i]->Fuzzification($this->FRealInput[$idx]); 
		}
	}
	
/**
* Set Input Range @param2 for Input named @param1 
* example :
*    $fuzzy->setInputRange('input1',array(0,1));
* @param1   string $idx 
* @param2   array  $range
* return   none 
**/	

	public function setInputRange($idx,$range = array(0, 1)) {
		$this->FInputRange[$idx]	=	$range;
	}

/*
* Agregate All Rules Result for Defuzification 
* example :
*    $fuzzy->fuzzyAgregate($outname,$Member,$AlphaCut=0.0)
* @param   string $output_name 
* @param   object $member_object
* @param   float $calculate_rule_value
* return   none 
*/	
	
	public function fuzzyAgregate($outname,$Member,$AlphaCut=0.0) {
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
		$this->clearSolution();
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
								if ($avg_sum>0)  $this->fuzzyAgregate($outname,$member,$avg_sum);
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
		$this->clearSolution();
				
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
			 if ($value>0)  $this->fuzzyAgregate($outputName,$member,$value);
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

?>
