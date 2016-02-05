<?php
	/**summary.php: summary for inputs or outputs */

	if(!isset($_GET['type']))
		die('Error. type not specified<br>Try: <a href=summary.php?type=input>Inputs</a> or <a href=summary.php?type=output>Outputs</a>');
	
	//tipus de variable: inputs o outputs
	$type=$_GET['type'];

	//check correct $type value
	if($type!="input" && $type!="output" )
		die('Error. type must be "inputs" or "outputs"');
?>
<!doctype html><html><head>
	<?php include'imports.php'?>
	<script>
		function init()
		{
			updateLevel1()
			updateLevel2()
			updateCounts()
			Exceptions.apply();
			updateResult()
		}

		function updateLevel1()
		{
			var t=document.querySelector("[level='1']")
			while(t.rows.length>1)t.deleteRow(-1)
			t.innerHTML+=tableRows(Global.UWS,  "UWS", 	        "uws",   "edit.php?level=UWS")
			t.innerHTML+=tableRows(Global.Water,"Water supply", "water", "edit.php?level=Water")
			t.innerHTML+=tableRows(Global.Waste,"Wastewater",   "waste", "edit.php?level=Waste")
		}

		function updateLevel2()
		{
			var t=document.querySelector("[level='2']")
			while(t.rows.length>1)t.deleteRow(-1)

			t.innerHTML+=tableRows(Global.Water.General,      "Water Energy use and production",     "waterGen", "edit.php?level=Water&sublevel=General")
			t.innerHTML+=tableRows(Global.Water.Abstraction,  "Water Abstraction",	                 "waterAbs", "edit.php?level=Water&sublevel=Abstraction")
			t.innerHTML+=tableRows(Global.Water.Treatment,	  "Water Treatment",	                 "waterTre", "edit.php?level=Water&sublevel=Treatment")
			t.innerHTML+=tableRows(Global.Water.Distribution, "Water Distribution",	                 "waterDis", "edit.php?level=Water&sublevel=Distribution")
			t.innerHTML+=tableRows(Global.Waste.General,      "Wastewater Energy use and production","wasteGen", "edit.php?level=Waste&sublevel=General")
			t.innerHTML+=tableRows(Global.Waste.Collection,	  "Wastewater Collection",               "wasteCol", "edit.php?level=Waste&sublevel=Collection")
			t.innerHTML+=tableRows(Global.Waste.Treatment,	  "Wastewater Treatment",                "wasteTre", "edit.php?level=Waste&sublevel=Treatment")
			t.innerHTML+=tableRows(Global.Waste.Discharge,	  "Wastewater Discharge",                "wasteDis", "edit.php?level=Waste&sublevel=Discharge")
		}

		function updateCounts()
		{
			for(family in Global.Configuration["Active Stages"])
			{
				var count=document.querySelectorAll("[family='"+family+"']").length;
				if(count!=0)document.querySelector("[count='"+family+"']").innerHTML = count;
			}
		}

		//** Create rows and columns for a table with specified object
		function tableRows(object,name,family,href)
		{
			//make link or not depending on active 
			if(Global.Configuration["Active Stages"][family]==0){return "";}

			//color blue or red
			var color = family.search('waste')==-1 ? "#00aff1" : "#bf5050";

			//return string
			var ret="<tr><td colspan=5 style='background:#ccc;font-weight:bold'>";
			ret+="<a href="+href+" style='color:"+color+"'>"+name+":</a> "

			//create a input/output count for the stage
			ret+="<span count="+family+">0</span> <?php echo $type?>s"

			//fill rows
			for(var variable in object)
			{
				//only go over type specified
				<?php
					switch($type)
					{
						case "input":$typeof="number";break;
						case "output":$typeof="function";break;
					}
				?>
				if(typeof(object[variable])!="<?php echo $typeof?>")continue

				var description=Info[variable] ? Info[variable].description : "<span style=color:#ccc>no description</span>";
				var explanation=Info[variable] ? Info[variable].explanation : "<span style=color:red>no explanation</span>";
				var unit = Info[variable] ? (Global.Configuration.Units[variable] || Info[variable].unit) : "<span style=color:#ccc>no unit</a>"
				var multiplier = Units.multiplier(variable);
				ret+="<tr field='"+variable+"' family='"+family+"'>"+
					"<td style='font-weight:bold'><a style='color:"+color+"' href=variable.php?id="+variable+">"+variable+"</a>"+
					"<td>"+description+
					"<td>"+explanation+
					"<td style=text-align:right>"+Math.floor(1e2*object[variable]<?php if($type=="output"){echo "()";}?>/multiplier)/1e2+
					"<td>"+unit
			}
			return ret;
		}
	</script>
</head><body onload=init()><center>
<!--sidebar--><?php include'sidebar.php'?>
<!--NAVBAR--><?php include"navbar.php"?>
<!--TITLE--><h1>Summary — All <?php echo $type?>s</h1>
<!--STAGES--><?php include"activeStages.php"?>

<!--AVAILABLE INPUTS-->
<div class=inline style="text-align:left">
	<h4>All active <?php echo $type?>s (sorted by stage)</h4>
	<!--level 1-->
	<div class=inline style="font-size:11px;width:45%;padding:0">
		<table style="width:100%" level=1><tr><th colspan=5>Level 1</table>
	</div>
	<!--level 2-->
	<div class=inline style="font-size:11px;width:45%;padding:0">
		<table style="width:100%" level=2><tr><th colspan=5>Level 2</table>
	</div>
</div>

<!--FOOTER--><?php include'footer.php'?>
<!--CURRENT JSON--><?php include'currentJSON.php'?>
