<?php include'currentSystem.php'?>
<?php
	/** void: echo a link if the current web page is not the one in the link's href */
	function nlink($href,$name)
	{
		if(strtoupper($href)==strtoupper($_SERVER['REQUEST_URI'])) 
			echo "<div class=active-tab><a href='$href' style=color:black>$name</a></div>";
		else 
			echo "<div class=inactive-tab><a href='$href'>$name</a></div>";
	}	
?>

<style>
	#navbar .active-tab, #navbar .inactive-tab{
		margin:0 0 0;
		display:inline-block;
	}
	#navbar .active-tab{
		background:white;
	}
	#navbar a {
		color:white;	
	}
</style>

<div id=navbar style="text-align:center;background:#00aff1;color:white;padding:0.2em 0em 0em 0em;font-size:18px">
	<?php
		echo "&#9776; "; //menu symbol (3 horizontal bars)
		nlink("/ecam/index.php","&#8962; HOME");
		if(isset($_COOKIE['GLOBAL']))
		{
			nlink("/ecam/getStarted.php","Get started");
			nlink("/ecam/configuration.php","Configuration");
			nlink("/ecam/stages.php","Input data");
			nlink("/ecam/resources.php","Resources");
			nlink("/ecam/about.php","About");
		}
	?>
</div>
