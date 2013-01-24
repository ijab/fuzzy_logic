<?php
/**
 * Implemen fuzzy logic for PAM without using MatLab
 * Example data:
 * [System]
 *	Name='Method1'
 *	Type='mamdani'
 *	Version=2.0
 *	NumInputs=7
 *	NumOutputs=1
 *	NumRules=165
 *	AndMethod='min'
 *	OrMethod='max'
 *	ImpMethod='min'
 *	AggMethod='max'
 *	DefuzzMethod='centroid'		
 *
 */

require_once ('../fuzzy_logic.php');
$impendance = 0;
$pam_fuzzy = new PAM_Fuzzy_Logic();


// iJab 2013/01/23: Input real data to calculate
$input_params = isset($_GET['input_params']) ? $_GET['input_params'] : "1.191000000,2,0,3,0.122563,0.134468,0.224211";
$inputs_v = explode(",", $input_params);

$impendance = $pam_fuzzy->cal_fuzzy($inputs_v);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Calculate PAM Impendance</title>
<link href="css/screen.css" rel="stylesheet" type="text/css"/>
</head>

<body>

	<div class="container">
		<h2 align="center">Calculate PAM Impendance</h2>
		<div class="span-16 prepend-4 append-4 last">
			<form>
				<label for="project_funding">Input params</>
				<input type="text" id="input_params" name="input_params" value="<?php echo $input_params;?>" /><br />  
				<input type="submit" name="calculate" value="calculate" />
				<div style="height:18px;margin:1px;background-color:red;border: 1px solid #000"><?php echo $impendance;?></div>
			</form>
		</div>
	</div>
</body>
</html>