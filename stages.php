<?php 
	/** THIS PAGES LETS THE USER NAVIGATE THROUGH ACTIVE STAGES */

	//parse cookie "GLOBAL" to see if stage $stages[name] is 0 or 1
	$stages=json_decode($_COOKIE['GLOBAL'],true)["General"]["Active Stages"];

	/** Prints a Level 1 stage for the navigation table. All parameters are strings */
	function printL1stage($alias,$level)
	{
		global $stages;
		$active=$stages[$alias];
		switch($level)
		{
			case "Water":$levelAlias="Water Supply";break;
			case "Waste":$levelAlias="Wastewater";break;
			default:$levelAlias=$level;break;
		}
		if($active)
			echo "
				<td rowspan=3>
					<img src=img/$alias.png>
					<a href='edit.php?level=$level'>$levelAlias</a>
			";
		else echo "<td rowspan=3 class=inactive title='Inactive'>$levelAlias";
	}

	/** Prints a Level 2 stage for the navigation table. All parameters are strings */
	function printL2stage($alias,$level,$sublevel)
	{
		global $stages;
		$active=$stages[$alias];
		if($active)
			echo "
				<td>
					<img src=img/$alias.png>
					<a title='Active Stage' href='edit.php?level=$level&sublevel=$sublevel'>$sublevel</a>
					<td> <a href=level3.php?level=$level&sublevel=$sublevel>Substages</a> 
					(<script>document.write(Global.Level3.$level.$sublevel.length)</script>)";
		else
			echo "
				<td class=inactive title='Inactive'>$sublevel
				<td class=inactive title='Inactive'>Substages";
	}
?>
<!doctype html><html><head>
	<meta charset=utf-8>
	<title>ECAM Web Tool</title>
	<link rel=stylesheet href="css.css"><style>
		td{vertical-align:middle;padding:1.5em;font-size:15px}
		td.inactive
		{
			color:#aaa;
			background-color:#efefef;
			font-size:12px;
		}
	</style>
	<script src="dataModel/global.js"></script>
	<script src="dataModel/info.js"></script>
	<script src="js/cookies.js"></script>
	<script src="js/updateGlobalFromCookies.js"></script>
	<script>
		function init()
		{
			updateResult()
		}
	</script>
</head><body onload=init()><center>
<!--NAVBAR--><?php include"navbar.php"?>
<!--TITLE--><h1>Stages Overview</h1>
<!--HELP--><h4>Click the stage where do you want to input data. To activate stages go to <a href=configuration.php>Configuration</a>.</h4>

<!--Navigation Table (for active stages)-->
<table id=navigationTable class=inline style="text-align:center;margin:1em">
	<!--this table style--><style>
		#navigationTable img{width:40px;vertical-align:middle}
	</style>
	<tr>
		<th style="font-size:13px">Level 1
		<th style="font-size:13px">Level 2
		<th style="font-size:13px">Level 3
	<tr>
		<?php printL1stage('water','Water')?>
			<?php printL2stage('waterAbs','Water','Abstraction')?>
			<tr><?php printL2stage('waterTre','Water','Treatment')?>
			<tr><?php printL2stage('waterDis','Water','Distribution')?>
	<tr>
		<?php printL1stage('waste','Waste')?>
			<?php printL2stage('wasteCol','Waste','Collection')?>
			<tr><?php printL2stage('wasteTre','Waste','Treatment')?>
			<tr><?php printL2stage('wasteDis','Waste','Discharge')?>
</table>

<!--DIAGRAM--><?php include'diagram.php'?>

<!--PREV & NEXT BUTTONS-->
<div style=margin:1em> 
	<button class="button prev" onclick=window.location='configuration.php'>Previous</button> 
	<button class="button next" onclick=window.location='allInputs.php'>Next</button>
</div>

<!--CURRENT JSON--><?php include'currentJSON.php'?>
