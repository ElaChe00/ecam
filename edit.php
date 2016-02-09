<?php
	/* edit.php this page lets the user modify inputs and see automatically the outputs */

	//check specified input: level and sublevel
	if(!isset($_GET['level'])){die("ERROR: stage not specified");}

	//level: 	 mandatory {"Water","Waste","UWS"}
	//sublevel:  optional. If set, enables level 3 {"Abstraction","Treatment","Distribution",[...]}
	$level=$_GET['level'];
	$sublevel=isset($_GET['sublevel']) ? $_GET['sublevel'] : false;
?>
<!doctype html><html><head>
	<?php include'imports.php'?>
	<style>
		td.input input { width:95%;font-size:18px}
		td.input       { width:80px;text-align:right;color:#666;background-color:#eee;cursor:cell}
		table#outputs tr:hover { background:orange; }
		th{text-align:center}
		<?php
			if($level=="Waste")
			{?>
				th{background:#bf5050}
				a,a:visited{color:#bf5050}
			<?php }
		?>
	</style>
	<script>
		/** 
		 * GUI utilities
		 * Note: Comments follow JSdoc structure (http://usejsdoc.org/about-getting-started.html) 
		 */

		<?php
			//establish the stage we are going to be focused
			if($sublevel)
				echo "var CurrentLevel = Global['$level']['$sublevel']";
			else
				echo "var CurrentLevel = Global['$level'];";
		?>

		/** 
		 * Transform a <td> cell to a <input> to make modifications in the Global object
		 * @param {element} element - the <td> cell
		 */
		function transformField(element)
		{
			element.removeAttribute('onclick')
			var field=element.parentNode.getAttribute('field')
			element.innerHTML=""
			var input=document.createElement('input')
			input.id=field
			input.classList.add('input')
			input.autocomplete='off'
			input.onblur=function(){updateField(field,input.value)}
			input.onkeypress=function(event){if(event.which==13){input.onblur()}}
			//value converted
			var multiplier = Units.multiplier(field);
			var currentValue = CurrentLevel[field]/multiplier;
			input.value=currentValue
			element.appendChild(input)
			input.select()
		}

		/** Redisplay table id=inputs */
		function updateInputs()
		{
			var t=document.getElementById('inputs')
			while(t.rows.length>2){t.deleteRow(-1)}
			for(field in CurrentLevel)
			{
				/*first check if function*/
				if(typeof(CurrentLevel[field])!="number")
				{
					/*then, check if is calculated variable "c_xxxxxx" */
					if(field.search('c_')==-1){continue};
				}

				/*check if field is level3 specific*/if(Level3.isInList(field)){continue;}

				//bool for if current field is a calculated variable (CV)
				var isCV = field.search('c_')!=-1

				/*new row*/var newRow=t.insertRow(-1);

				/*background*/if(isCV){newRow.classList.add('isCV');}

				/*hlFields for formula and show formula, only if CV*/
				if(isCV)
				{
					var formula = CurrentLevel[field].toString();
					var prettyFormula = Formulas.prettify(formula);
					newRow.setAttribute('onmouseover','Formulas.hlFields("'+prettyFormula+'",1)');
					newRow.setAttribute('onmouseout', 'Formulas.hlFields("'+prettyFormula+'",0)');
					newRow.setAttribute('title',prettyFormula);
				}
				
				/*attribute field==field>*/newRow.setAttribute('field',field);
				/*description*/ 
				var newCell=newRow.insertCell(-1);
				newCell.setAttribute('title',Info[field].explanation);
				newCell.style.cursor='help';
				newCell.innerHTML=(function()
				{
					var description = Info[field]?Info[field].description:"<span style=color:#ccc>no description</span>";
					var code = "<a href=variable.php?id="+field+">"+field+"</a>";
					return description+" ("+code+")";
				})();
				//editable cell if not CV
				var newCell=newRow.insertCell(-1);
				if(!isCV)
				{
					newCell.className="input";
					newCell.setAttribute('onclick','transformField(this)');
				}
				else newCell.style.textAlign='center'

				/*value*/
				newCell.innerHTML=(function()
				{
					if(isCV)
						return Math.floor(1e2*CurrentLevel[field]()/Units.multiplier(field))/1e2;
					else
						return CurrentLevel[field]/Units.multiplier(field);
				})();
				//unit
				newRow.insertCell(-1).innerHTML=(function()
				{
					if(Info[field].magnitude=="Currency")
					{
						return Global.General.Currency;
					}

					if(isCV) 
					{
						return Info[field].unit;
					}
					else
					{
						var str="<select onchange=Units.selectUnit('"+field+"',this.value)>";
						if(Info[field]===undefined)
						{
							return "<span style=color:#ccc>no unit</span>";
						}
						if(Units[Info[field].magnitude]===undefined)
						{
							return Info[field].unit
						}
						var currentUnit = Global.Configuration.Units[field] || Info[field].unit
						for(unit in Units[Info[field].magnitude])
						{
							if(unit==currentUnit)
								str+="<option selected>"+unit+"</option>";
							else
								str+="<option>"+unit+"</option>";
						}
						str+="</select>"
						return str
					}
				})();
				//data quality
				newRow.insertCell(-1).innerHTML=(function(){
					if(isCV)
					{
						return "Calculated";
					}
					else
					{
						/*
						return "Input";
						*/
						var select = document.createElement('select');
						['Actual','Estimated'].forEach(function(opt)
						{
							var option=document.createElement('option');
							option.innerHTML=opt;
							select.appendChild(option);
						});
						return select.outerHTML;
					}
				})();
			}
		}

		/** Redisplay table id=outputs */
		function updateOutputs()
		{
			var t=document.getElementById('outputs');
			while(t.rows.length>2){t.deleteRow(-1);}
			for(var field in CurrentLevel)
			{
				if(typeof(CurrentLevel[field])!="function"){continue;}
				if(field.search('c_')!=-1){continue;}

				/*check if field is level3 specific*/
				if(Level3.isInList(field)){continue;}
				var newCell,newRow=t.insertRow(-1);
				newRow.setAttribute('field',field);
				var formula=CurrentLevel[field].toString();
				var prettyFormula=Formulas.prettify(formula);
				newRow.setAttribute('title',prettyFormula);
				newRow.setAttribute('onmouseover','Formulas.hlFields("'+prettyFormula+'",1)');
				newRow.setAttribute('onmouseout', 'Formulas.hlFields("'+prettyFormula+'",0)');

				//compute now the value for creating the indicator
				var value = Math.floor(1e2*CurrentLevel[field]()/Units.multiplier(field))/1e2;

				/*circle indicator*/ 
				newCell=newRow.insertCell(-1);
				newCell.style.textAlign='center';
				newCell.style.cursor='help';
				newCell.innerHTML=(function()
				{
					var hasIndicator=RefValues.isInside(field);
					if(hasIndicator)
					{
						var indicator=RefValues[field](value);
						newCell.title=indicator;
						var color;
						switch(indicator)
						{
							case "Good":           color="#af0";break;
							case "Acceptable":     color="orange";break;
							case "Unsatisfactory": color="red";break;
							default:               color="#ccc";break;
						}
						return "<span style='font-size:20px;color:"+color+"'>&#128308;</span>";
					}
					else return "<span style=color:#ccc>-</span>";
				})();
				/*description*/ 
				newCell=newRow.insertCell(-1);
				newCell.setAttribute('title',Info[field].explanation);
				newCell.style.cursor='help';
				newCell.innerHTML=(function()
				{
					var description = Info[field]?Info[field].description:"<span style=color:#ccc>no description</span>";
					var code = "<a href=variable.php?id="+field+">"+field+"</a>";
					return description+" ("+code+")";
				})();
				/*value*/ 
				newCell=newRow.insertCell(-1)
				newCell.innerHTML=(function()
				{
					if(Level2Warnings.isIn(field))
					{
						return value+" <span style=color:#999>("+Level2Warnings[field]+")</span>";
					}
					return value;
				})();
				/*unit*/ newRow.insertCell(-1).innerHTML=Info[field]?Info[field].unit:"<span style=color:#ccc>no unit</span>";
			}
		}

		/**
		 * Update a field from the Global object
		 * @param {string} field - The field of the CurrentLevel object
		 */
		function updateField(field,newValue)
		{
			if(typeof(CurrentLevel[field])=="number")newValue=parseFloat(newValue) //if CurrentLevel[field] is a number, parse float
			//if a unit change is set, get it:
			var multiplier = Units.multiplier(field);
			CurrentLevel[field]=multiplier*newValue; //update the field
			init(); //update tables and write cookies
		}

		/** Update all tables */
		function init()
		{
			updateInputs();
			updateOutputs();
			Exceptions.apply();
			updateResult();
		}
	</script>
</head><body onload=init()><center>

<!--sidebar--><?php include'sidebar.php'?>

<div id=fixedTopBar>
	<style>
		div#fixedTopBar {
			position:fixed;
			top:0;
			width:100%;
			margin:0;padding:0;
			border-bottom:1px solid #ccc;
			background:white;
		}
	</style>

	<!--NAVBAR--><?php include"navbar.php"?>

	<!--TITLE-->
	<?php 
		//Set a navigable title for page
		switch($level)
		{
			case "Water":  $titleLevel="Water Supply";break;
			case "Waste":  $titleLevel="Wastewater";break;
			default:	   $titleLevel=$level;break;
		}
		if($sublevel)
		{
			switch($sublevel)
			{
				case "General":$titleSublevel="Energy use and production";break;
				default:	   $titleSublevel=$sublevel;break;
			}
		}
		/*separator*/ $sep="<span style=color:black>&rsaquo;</span>";
		$title=$sublevel ? "<a href=edit.php?level=$level>$titleLevel</a> $sep <span style=color:black>$titleSublevel (Level 2)</span>" : "<span style=color:black>$titleLevel (Level 1)</span>";
	?>
	<style> h1 {text-align:left;padding-left:20em} </style>
	<h1><a href=stages.php>Input data</a> <?php echo "$sep $title"?></h1>
</div>

<!--separator--><div style=margin-top:120px></div>
<!--linear diagram--><?php include'linear.php'?>
<!--go to level 3 button-->
<?php
	if($sublevel)
	{
		if($sublevel!="General")
		{
			$color = ($level=="Waste")?"lightcoral":"lightblue";
			echo "<button 
					class=button
					style='background:$color;'
					onclick=window.location='level3.php?level=$level&sublevel=$sublevel'>
						Go to $sublevel Level 3 &map;
					</button> 
				";
		}
	}
?>
<!--HELP--><h4>Input data for this stage. The Indicators (in yellow) will be updated automatically.</h4>

<!--IO-->
<div style=text-align:left>
	<!--INPUTS-->
	<table id=inputs class=inline style="max-width:46%">
		<tr><th colspan=5>INPUTS <?php include'inputType.php'?>
		<tr>
			<th>Description
			<th>Current value
			<th>Unit
			<th>Data quality
	</table>

	<!--PI-->
	<table id=outputs class=inline style=max-width:46%;background:#f5ecce;>
		<tr><th colspan=5>RESULTS - Key performance indicators
		<tr>
			<th title=Performance style=cursor:help>P
			<th>Description
			<th>Current value
			<th>Unit
	</table>
</div>

<!--FOOTER--><?php include'footer.php'?>
<!--CURRENT JSON--><?php include'currentJSON.php'?>
