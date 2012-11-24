<!DOCTYPE HTML>
<?php require 'epp_classes.php'; ?>
<html>
<head>
	<title>Point Earning Scenario Class Tester</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<script>
	counter = 0;
	function addRequirements(){
		var newdiv = document.createElement('div');

		newdiv.innerHTML = '<select id="reqAct[]" name="reqAct[]" style="margin-left:50px;">\n<option value="0">Test 1</option><option value="1">Test 2</option><option value="2">Quiz 1</option></select><select id="reqCond[]" name="reqCond[]"><option value="0">Completed</option><option value="1">&gt;</option><option value="2">&gt;=</option><option value="3">=</option></select><input type="text" id="reqGradeToAchieve[]" name="reqGradeToAchieve[]" style="width:50px;text-align:right;" placeholder="%">';
		counter++;

		document.getElementById("requirementsDIV").appendChild(newdiv);
	}
	</script>
</head>
<body onload="addRequirements()">
	<div id="form" style="width:400px;height:400px;border:thin solid black;overflow:auto;">
		<form method="post" action="persistPES.php" name="pes-creator" id="pes-creator" style="padding-left:10px;padding-right:10px;">
			<h3>Point Earning Scenario</h3>
			<table>
				<tr>
					<td style="width:100px">Name</td>
					<td><input type="text" style="margin-right:10px;width:200px;" id="pesName" name="pesName"></td>
				</tr>
				<tr>
					<td>Point Value</td>
					<td><input type="text" style="margin-right:10px;width:200px;" id="pesPointValue" name="pesPointValue"></td>
				</tr>
				<tr>
					<td>Expiry Date</td>
					<td><input type="date" style="margin-right:10px;width:200px;" id="pesExpiryDate" name="pesExpiryDate"></td>
				</tr>
				<tr>
					<td style="vertical-align:top;">Description</td>
					<td><textarea name="pesDescription" id="pesDescription" style="margin-right:10px;width:200px;" style></textarea></td>
				</tr>
			</table>
			<hr/>
			Requirement(s)<br/>
			<div id="requirementsDIV" name="requirementsDIV">			
			</div>
			<br/><br/>
			<input name="Submit" type="button" style="margin: 0 auto; display:block; border:1px solid #000000; height:20px; padding-left:2px; padding-right:2px; padding-top:0px; padding-bottom:2px; line-height:14px; background-color:#EFEFEF;" onclick="addRequirements()" value="Add Another Requirement"/>
			<br/>
			<input name="Submit" type="submit" style="float:right; display:block; border:1px solid #000000; height:20px; padding-left:2px; padding-right:2px; padding-top:0px; padding-bottom:2px; line-height:14px; background-color:#EFEFEF;" value="Create New Point Earning Scenario"/>
		</form>
	</div>

	<script>
   		$("#req-cond1").change(function () {
          var str = "";
          str = $("#req-cond1 option:selected").val();
          if (str == "0"){
	          $("#req-percentToAchieve").hide();
		  }
	      else{ 
	          $("#req-percentToAchieve").show();
	      }
        })
        .change();
    </script>
</body>
</html>

