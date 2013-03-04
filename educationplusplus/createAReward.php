<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of educationplusplus
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage educationplusplus
 * @copyright  2012 Husain Fazal, Preshoth Paramalingam, Robert Stancia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace educationplusplus with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
// Education++ Classes



$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('educationplusplus', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $educationplusplus  = $DB->get_record('educationplusplus', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $educationplusplus  = $DB->get_record('educationplusplus', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $educationplusplus->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('educationplusplus', $educationplusplus->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'educationplusplus', 'createAnIncentive', "createAnIncentive.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/createAnIncentive.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('educationplusplus-'.$somevar);

// Retrieve All Assignments to Display as Options for Requirements
// Retrieve from DB all Activities

// Output starts here
echo $OUTPUT->header();
$constructedSelectOptions = "";

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Education++');

echo $OUTPUT->box('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
<script>
counter = 0;
function addRequirements(){
	var newdiv = document.createElement(\'div\');

	newdiv.innerHTML = \'<select id="reqAct[]" name="reqAct[]" style="margin-left:50px;">' . $constructedSelectOptions . '</select><select id="reqCond[]" name="reqCond[]"><option value="0">Completed</option><option value="1">&gt;</option><option value="2">&gt;=</option><option value="3">=</option></select><input type="text" id="reqGradeToAchieve[]" name="reqGradeToAchieve[]" style="width:50px;text-align:right;" placeholder="%">\';
	counter++;

	document.getElementById("requirementsDIV").appendChild(newdiv);
}
function validate(){
	var name        = document.forms["pesform"]["pesName"].value;
	var pv          = document.forms["pesform"]["pesPointValue"].value;
	var expirydate  = document.forms["pesform"]["pesExpiryDate"].value;
	var description = document.forms["pesform"]["pesDescription"].value;
	var pass 		= true;
	
	$("#nameReq").css("display", "none");
	$("#pvReq").css("display", "none");
	$("#pvReqInt").css("display", "none");
	$("#expReq").css("display", "none");
	$("#desReq").css("display", "none");
	
	if (name==null || name==""){
		$("#nameReq").css("display", "inline");
		pass = false;
	}
	if (pv==null || pv==""){
		$("#pvReq").css("display", "inline");
		pass = false;
	}
	else if (!parseInt(pv)){
		$("#pvReqInt").css("display", "inline");
		pass = false;
	}
	else if (parseInt(pv) < 1){
		$("#pvReqInt").css("display", "inline");
		pass = false;
	}
	if (expirydate==null || expirydate==""){
		$("#expReq").css("display", "inline");
		pass = false;
	}
	if (description==null || description==""){
		$("#desReq").css("display", "inline");
		pass = false;
	}
	
	return pass;
}
addRequirements();
</script>

<div id="form" style="width:400px;height:600px;overflow:auto;">
	<form id="pesform" name="pesform" method="post"  enctype="multipart/form-data" onsubmit="return validate()" action="persistIncentive.php?id='. $cm->id .'" name="pes-creator" id="pes-creator" style="padding-left:10px;padding-right:10px;">
		<h3>Incentive</h3>
		<table>
			<tr>
				<td style="width:100px">Name</td>
				<td><input type="text" class="required" style="margin-right:10px;width:200px;" id="incentiveName" name="incentiveName"><br/><span id="nameReq" style="color:red;display:none;">You Must Specify the Name of the Incentive</span></td>
			</tr>
			<tr>
				<td>Quantity Per Student</td>
				<td><input type="text" class="required" style="margin-right:10px;width:200px;" id="incentiveQty" name="incentiveQty"><br/><span id="pvReq" style="color:red;display:none;">You Must Specify a quantity per student</span><span id="pvReqInt" style="color:red;display:none;">You Must Specify a positive number for a Quantity</span></td>
			</tr>
			<tr>
				<td>Price in Points</td>
				<td><input type="text" class="required" style="margin-right:10px;width:200px;" id="incentivePrice" name="incentivePrice"><br/><span id="pvReq" style="color:red;display:none;">You Must Specify a price for the incentive</span><span id="pvReqInt" style="color:red;display:none;">You Must Specify a positive number for a Price</span></td>
			</tr>
	
			<tr>
				<td style="vertical-align:top;">Store Visibility</td>
				<td><input type="checkbox" class="required" name="storevis" id="storevis" style="margin-right:10px;width:200px;" value="1" checked/>
					
				</td>
			</tr>
			<tr>
				<td>Image Selection</td>
				<td><input type="file"  style="margin-right:10px;width:200px;" id="incentiveImg" name="incentiveImg" ><br/>
				</td>
			</tr>
			<tr>
				<td>Expiry Date</td>
				<td><input type="date" class="required" style="margin-right:10px;width:200px;" id="rewardExpiryDate" name="rewardExpiryDate"><br/><span id="expReq" style="color:red;display:none;">You Must Specify an Expiry Date</span></td>
			</tr>
			<tr>
				<td style="vertical-align:top;">Description</td>
				<td><textarea class="required" name="rewardDescription" id="rewardDescription" style="margin-right:10px;width:200px;" style></textarea><br/><span id="desReq" style="color:red;display:none;">You Must Specify a Description</span></td>
			</tr>
		</table>
		<hr/>
		<input name="Submit" type="submit" style="float:right; display:block; border:1px solid #000000; height:20px; padding-left:2px; padding-right:2px; padding-top:0px; padding-bottom:2px; line-height:14px; background-color:#EFEFEF;" value="Create New Incentive"/>
	</form>
</div>');

echo "<br/>";
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="viewAllPES.php?id='. $cm->id .'">Return to the Education++: Manage Scenarios Page (Cancel Creation of this Scenario)</a></div>');

// Finish the page
echo $OUTPUT->footer();

