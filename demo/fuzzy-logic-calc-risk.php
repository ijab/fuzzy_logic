<?php
require_once ('../fuzzy-logic-class.php');
$x = new Fuzzy_Logic();
$x->clearMembers(); 
$x->SetInputNames(array('project_funding','project_staffing'));
$x->addMember($x->getInputName(0),'inadequate',  0, 20, 40 ,LINFINITY);
$x->addMember($x->getInputName(0),'marginal'  , 20, 50, 80 ,TRIANGLE);
$x->addMember($x->getInputName(0),'adequate'  , 60, 80, 100,RINFINITY);
$x->addMember($x->getInputName(1),'small', 0, 30, 70,LINFINITY);
$x->addMember($x->getInputName(1),'large',30, 70,100,RINFINITY);
$x->SetOutputNames(array('risk'));
$x->addMember($x->getOutputName(0),'low',0, 20 ,40 ,LINFINITY);
$x->addMember($x->getOutputName(0),'normal',20, 50 ,80 ,TRIANGLE);
$x->addMember($x->getOutputName(0),'high',60,  80 , 100 ,RINFINITY);
$x->clearRules();
$x->addRule('IF project_funding.adequate OR project_staffing.small THEN risk.low');
$x->addRule('IF project_funding.marginal AND project_staffing.large THEN risk.normal');
$x->addRule('IF project_funding.inadequate THEN risk.high');

$project_funding = (isset($_GET['project_funding'])) ? $_GET['project_funding'] : 35;   
$project_staffing = (isset($_GET['project_staffing'])) ? $_GET['project_staffing'] : 65; 

$x->setRealInput('project_funding',	  $project_funding	);
$x->setRealInput('project_staffing' , $project_staffing	);
$fuzzy_arr = $x->calcFuzzy();
$risk = $fuzzy_arr['risk'];
$bar_width = 320;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Calculate Risk Project</title>
<link href="css/screen.css" rel="stylesheet" type="text/css"/>
</head>

<body>

<div class="container">
<h2 align="center">Calculate Risk Project</h2>
<div class="span-16 prepend-4 append-4 last">
<form>
<label for="project_funding">Project funding [0..100%]</>
<input type="text" id="project_funding" name="project_funding" value="<?php echo $project_funding;?>" /><br />  
<label for="project_staffing">Project staffing [0..100%]</>
<input type="text" id="project_staffing" name="project_staffing" value="<?php echo $project_staffing;?>" /><br /> 
<input type="submit" name="calculate" value="calculate" />
<div style="width: <?php echo $bar_width;?>px;height:22px;border: 1px solid #000;;text-align:center;">
<div style="width: <?php echo round($risk*$bar_width/100); ?>px;height:18px;margin:1px;background-color:red;border: 1px solid #000"><?php echo sprintf("%3.1f %%",$risk);?></div>
</div>
</form>
<br /> 
<hr />
<br />
<h2>Help for use class Fuzzy_Logic</h2>
<h3>Create new instance class</h3>
<div class="success"><pre> $fuzzy = new Fuzzy_Logic();</pre></div>
<h3>Clear all membership</h3>
<div class="success"><pre> $fuzzy->clearMembers(); </pre></div>
<h3>Build memberships</h3>
<div class="box">
<p>The linguistic variable "project_funding" in this system can be divided into a range of "states", such as: "inadequate", "marginal", "adequate". Defining the bounds of these states is a bit tricky. An arbitrary threshold might be set to divide "inadequate" from "marginal", but this would result in a discontinuous change when the input value passed over that threshold.(is fuzzy).
The way around this is to make the states "fuzzy", that is, allow them to change gradually from one state to the next. You could define the input funding condition states using "membership functions" such as the following:</p>
<p>The Fuzzy class are four types of membership functions of states:</p>
<h3>memberships shapes defined in Fuzzy_Logic class :</h3> 
<pre>define ("LINFINITY"    ,   -1 );
define ("TRIANGLE"     ,    0 );
define ("RINFINITY"    ,    1 );
define ("TRAPEZOID"    ,    2 );</pre>
<p>shapes this function is defined as points of shapes (in order and named) as: begin,middle,end</p>
<ul>
<li>LFINITY  = Left Ended Trapezoid in example defined as points: 0,20,50</li>
<li>TRIANGLE = defined in example as points: 20,50,80</li>
<li>RFINITY  = Right Ended Trapesoid in example defined as points : 50,80,100</li>
<li>TRAPEZOID = may for example be defined as points: 20,array(40,60),80</li>
</ul>
 <div align="center"> <img src="images/trapezoid.jpg" alt="available membership shapes" title="available membership shapes" /> </div>
 </div>
<br /> 
<hr />
<br /> 
<h3>add to class all inputs names :</h3> 
<div class="success"><pre>$fuzzy->SetInputNames(array('project_funding','project_staffing'));</pre></div>
<br /> 
<hr />
<br />
<h3>add members to input named  "project_funding":</h3> 
<div class="success"><pre>$fuzzy->addMember('project_funding','inadequate',  0, 20, 40 ,LINFINITY);
$fuzzy->addMember('project_funding','marginal'  , 20, 50, 80 ,TRIANGLE);
$fuzzy->addMember('project_funding','adequate'  , 60, 80, 100,RINFINITY);</pre></div>
 <div align="center"> <img src="images/project_funding.jpg" alt="project funding membership" title="project_funding" /> </div>
<br /> 
<hr />
<br /> 
<h3>add members to input named  "project_staffing":</h3>  
<div class="success"><pre>$fuzzy->addMember('project_staffing','small', 0, 30, 70,LINFINITY);
$fuzzy->addMember('project_staffing','large',30, 70,100,RINFINITY);</pre></div>
 <div align="center"> <img src="images/project_staffing.jpg" alt="project staffing membership" title="project_staffing" /> </div>
<br /> 
<hr />
<br />
<h3>add to class all output names :</h3> 
<div class="success"><pre>$fuzzy->addMember('risk','low',0, 20 ,40 ,LINFINITY);
$fuzzy->addMember('risk','normal',20, 50 ,80 ,TRIANGLE);
$fuzzy->addMember('risk','high',60,  80 , 100 ,RINFINITY);</pre></div>
 <div align="center"> <img src="images/risk.jpg" alt="risk" title="risk" /> </div>
 <br /> 
<hr />
<br />
<h3>clear all rules :</h3> 
<div class="success"><pre>$fuzzy->clearRules();</pre></div>
 <br /> 
<hr />
<br />
<h3>add all rules :</h3> 
<div class="success"><pre>$fuzzy->addRule('IF project_funding.adequate OR project_staffing.small THEN risk.low');
$fuzzy->addRule('IF project_funding.marginal AND project_staffing.large THEN risk.normal');
$fuzzy->addRule('IF project_funding.inadequate THEN risk.high');</pre></div>
<div class="box">
<h2>Help for rules syntax :</h2>
 Rules is defined as text command : eg
<pre>   IF i1.m1 AND i2.n1 AND i3.k1 THEN o1.xx</pre>
<p>where IF, AND, OR, NOT, THEN is available instruction words<br />
</p>
Command syntax (instruction words) :

<pre>IF, THEN - condition operators
IF - always the first word in the command
THEN - always precedes the output value pair 
OR, AND, NOT - logical operations.
</pre>
Input and output fuzzy fuzzy sets are supplied as a text concatenation:
<pre>input_name.meber_name</pre>  
Separator is a dot character.<br />
example: 
<pre>temperature.cold , dish.good</pre> 

<ul>
<li>The default operator is a logical AND operator.</li> 
<li>If the line there are two different logical operators they must be surrounded by parentheses.</li> 
<li>The NOT operator must always be surrounded by parenthesis.</li> 
<li>Not operator ALVAIS HAVE ONE ARGUMENT</li>
<li>If the operations in brackets are nested, always calculated from the most nested operations.</li> 
<li>Operations in the parentheses are performed first.</li>
</ul>
<h3>Examples of valid commands:</h3>
<pre>IF a.b AND c.d THEN x.y
IF a.b AND (c.d OR g.h) THEN x.y
IF a.b AND (c.d OR (g.h AND i.j)) THEN x.y
IF a.b AND c.d  AND g.h THEN x.y
IF a.b AND (NOT c.d) THEN x.y
</pre>
<h3>Examples of bad commands:</h3>
<pre>IF a.b AND c.d OR g.h THEN x.y
IF a.b NOT c.d THEN x.y
IF a.b AND c.d OR g.h AND i.j THEN x.y</pre>
<h3>Logical calculate order example </h3>
<pre>
IF a.b AND (c.d OR (e.f AND i.j)) THEN g.h

  order logic calculations :
  
       1. (e.f AND i.j) = xx 
       2. (c.d OR (xx)) = yy
       3. a.b AND yy	
	   
</pre>
</div>
<br /> 
<hr />
<br />
<h3>set crisp input values to Fuzzy_Class inputs :</h3> 
<div class="success"><pre>$project_funding = 65;
$project_staffing = 35;
$fuzzy->setRealInput('project_funding',	  $project_funding	);
$fuzzy->setRealInput('project_staffing' , $project_staffing	);</pre></div>

<br /> 
<hr />
<br />
<h3>calculate Fuzzy Result :</h3> 
<div class="success"><pre>$fuzzy_output_array = $fuzzy->calcFuzzy();</pre></div>
<br />
<hr />
<br />
<div class="box"><p>Result is returned as associated as output names keys array. (eg $fuzzy_output_array)</p>
<pre>
array (
	[risk] = 35.6
	)
</pre>
</div>
<br />
<hr />
<br />
<br />

</div>
</div>
</body>
</html>