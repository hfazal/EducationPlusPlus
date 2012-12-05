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
require 'eppClasses/PointEarningScenario.php';
require 'eppClasses/Requirement.php';
require 'eppClasses/Activity.php';


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$idOfPesToEdit  = optional_param('pes', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'createAPES', "createAPES.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/createAPES.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('educationplusplus-'.$somevar);

// Retrieve All Assignments to Display as Options for Requirements
// Retrieve from DB all Activities
global $DB;

// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Education++');

if(!empty($idOfPesToEdit)){
	$PEStoEdit = $DB->get_record('epp_pointearningscenario',array('id'=>$idOfPesToEdit));
	$allRequirements = $DB->get_records('epp_requirement',array('pointearningscenario'=>$PEStoEdit->id));
	$arrayOfRequirements = array();

	foreach ($allRequirements as $rowRequirements){
		$activ = $DB->get_record('assign',array('id'=>$rowRequirements->activity));	
		array_push($arrayOfRequirements, new Requirement(new Activity($activ->name,null), intval($rowRequirements->cond), intval($rowRequirements->percenttoachieve)));
		// ( $activity, $condition, $percentToAchieve )
	}
	$PES = new PointEarningScenario($PEStoEdit->name, intval($PEStoEdit->pointvalue), $PEStoEdit->description, $arrayOfRequirements, new DateTime($PEStoEdit->expirydate));
	//( $name, $pointValue, $description, $requirementSet, $expiryDate, $deletedByProf )
	
	//CREATE ADDABLE DROPDOWN
	$result = $DB->get_records('assign',array('course'=>$course->id));
	$constructedSelectOptions = "";
	if ($result){
		foreach ($result as $row){
			$constructedSelectOptions = $constructedSelectOptions . '<option value="' . $row->id . '">' . $row->name . '</option>';
		}
	}
	
	//CREATE PRESET DROPDOWNS
	$prefilledAct = array();
	$prefilledCond = array();
	$prefilledGTA = array();
	
	foreach ($PES->requirementSet as $rowRequirement){
		//ACTIVITY
		$stringForAct = "";
		if ($result){
			foreach ($result as $row){	
				$stringForAct = $stringForAct . '<option ' . ((strcmp($rowRequirement->activity->name,$row->name)==0) ? 'selected="selected"' : 'lolol ') . ' value="' . $row->id . '">' . $row->name . '</option>';
			}
		}
		array_push($prefilledAct, $stringForAct);

		//CONDITION
		$stringForCond = '<option ' . (($rowRequirement->condition == 0)? 'selected="selected"' : '') . ' value="0">Completed</option>
						  <option ' . (($rowRequirement->condition == 1)? 'selected="selected"' : '') . ' value="1">&gt;</option>
						  <option ' . (($rowRequirement->condition == 2)? 'selected="selected"' : '') . ' value="2">&gt;=</option>
						  <option ' . (($rowRequirement->condition == 3)? 'selected="selected"' : '') . ' value="3">=</option>';
		array_push($prefilledCond, $stringForCond);
		
		//PERCENTTOACHIEVE
		array_push($prefilledGTA, $rowRequirement->percentToAchieve);
	}

	
	$requirementsPreFilledDropdown = "";
	for ($i=0; $i < count($prefilledGTA); $i++){
		$requirementsPreFilledDropdown = $requirementsPreFilledDropdown . '<select id="reqAct[]" name="reqAct[]" style="margin-left:50px;">' . $prefilledAct[$i] . '</select><select id="reqCond[]" name="reqCond[]">' . $prefilledCond[$i] . '</select><input type="text" id="reqGradeToAchieve[]" name="reqGradeToAchieve[]" style="width:50px;text-align:right;" value=' . $prefilledGTA[$i] . ' placeholder="%">';
	}
	
	echo $OUTPUT->box('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<script>
	function cancelEdit(){
		window.open("viewAllPES.php?id='. $cm->id .'", "_self");
	}
	function addRequirements(){
		var newdiv = document.createElement(\'div\');

		newdiv.innerHTML = \'<select id="reqAct[]" name="reqAct[]" style="margin-left:50px;">' . $constructedSelectOptions . '</select><select id="reqCond[]" name="reqCond[]"><option value="0">Completed</option><option value="1">&gt;</option><option value="2">&gt;=</option><option value="3">=</option></select><input type="text" id="reqGradeToAchieve[]" name="reqGradeToAchieve[]" style="width:50px;text-align:right;" placeholder="%">\';

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
		<form id="pesform" name="pesform" method="post" onsubmit="return validate()" action="persistUpdatedPES.php?id='. $cm->id .'&pes=' . $idOfPesToEdit . '" name="pes-creator" id="pes-creator" style="padding-left:10px;padding-right:10px;">
			<h3>Edit an Existing Point Earning Scenario</h3>
			<table>
				<tr>
					<td style="width:100px">Name</td>
					<td><input type="text" value="' . $PES->name . '" style="margin-right:10px;width:200px;" id="pesName" name="pesName"><br/><span id="nameReq" style="color:red;display:none;">You Must Specify the Name of the Scenario</span></td>
				</tr>
				<tr>
					<td>Point Value</td>
					<td><input type="text" value="' . $PES->pointValue . '" style="margin-right:10px;width:200px;" id="pesPointValue" name="pesPointValue"><br/><span id="pvReq" style="color:red;display:none;">You Must Specify a Point Value to Award</span><span id="pvReqInt" style="color:red;display:none;">You Must Specify a positive number for a Point Value</span></td>
				</tr>
				<tr>
					<td>Expiry Date</td>
					<td><input type="date" value="' . $PES->expiryDate->format('Y-m-d') . '" style="margin-right:10px;width:200px;" id="pesExpiryDate" name="pesExpiryDate"><br/><span id="expReq" style="color:red;display:none;">You Must Specify an Expiry Date</span></td>
				</tr>
				<tr>
					<td style="vertical-align:top;">Description</td>
					<td><textarea name="pesDescription" id="pesDescription" style="margin-right:10px;width:200px;">' . $PES->description . '</textarea><br/><span id="desReq" style="color:red;display:none;">You Must Specify a Description</span></td>
				</tr>
			</table>
			<hr/>
			Requirement(s)<br/>
			<div id="requirementsDIV" name="requirementsDIV">' . 
				$requirementsPreFilledDropdown
			. '</div>
			<br/><br/>
			<input name="AddAnother" type="button" style="margin: 0 auto; display:block; border:1px solid #000000; height:20px; padding-left:2px; padding-right:2px; padding-top:0px; padding-bottom:2px; line-height:14px; background-color:#EFEFEF;" onclick="addRequirements()" value="Add Another Requirement"/>
			<br/>
			<input name="Cancel" type="button" style="margin: 0 auto; display:block; border:1px solid #000000; height:20px; padding-left:2px; padding-right:2px; padding-top:0px; padding-bottom:2px; line-height:14px; background-color:#EFEFEF;" onclick="cancelEdit()" value="Cancel Edit"/>
			<br/>
			<input name="Submit" type="submit" style="margin: 0 auto; display:block; border:1px solid #000000; height:20px; padding-left:2px; padding-right:2px; padding-top:0px; padding-bottom:2px; line-height:14px; background-color:#EFEFEF;" value="Save Changes to Existing Point Earning Scenario"/>
		</form>
	</div>');
}
else {
	echo $OUTPUT->box('This page cannot be accessed directly');
}
echo "<br/>";
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Click to return to the Education++ homepage</a></div>');

// Finish the page
echo $OUTPUT->footer();

