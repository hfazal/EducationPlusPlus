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
 * @copyright  2013 Husain Fazal, Preshoth Paramalingam, Robert Stancia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
// Education++ Classes
require 'eppClasses/Badge.php';


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$idOfIncentivetoEdit  = optional_param('badge', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'editBadge', "editBadge.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/editBadge.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Retrieve All Assignments to Display as Options for Requirements
// Retrieve from DB all Activities
global $DB;

// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}
// Determine if Professor Level Access
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}

if($isProfessor){
	// Display Intro
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Edit a Badge</h1>
			<p>To reward students, you can create incentives for them to purchase such as a Badge</p>
			<p>A Badge is a trophy that students can purchase and display on the school leaderboard for bragging rights</p>
		  </div>';


	if(!empty($idOfIncentivetoEdit)){
		$IncentivetoEdit = $DB->get_record('epp_incentive',array('id'=>$idOfIncentivetoEdit));
		//$BadgetoEdit = $DB->get_record('epp_badge',array('incentive_id'=>$idOfIncentivetoEdit));


		$Incentive = new Badge($IncentivetoEdit->name, intval($IncentivetoEdit->qtyperstudent), intval($IncentivetoEdit->storevisibility), intval($IncentivetoEdit->priceinpoints), $IncentivetoEdit->icon, intval($IncentivetoEdit->deletebyprof), new DateTime($IncentivetoEdit->datecreated));

		if (intval($IncentivetoEdit->storevisibility) == 1)
			$storeVis = "checked";
		else
			$storeVis = "";
		//'if ($storeVis == 1) {$value="value="1" checked "}'
		
		
		echo $OUTPUT->box('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<script>
		function validate(){
			var name        = document.forms["pesform"]["incentiveName"].value;
			var pv          = document.forms["pesform"]["incentivePrice"].value;

			var pass 		= true;
			
			$("#nameReq").css("display", "none");
			$("#pvReq").css("display", "none");
			$("#pvReqInt").css("display", "none");
			
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
			
			return pass;
		}
	</script>

	<div id="form" style="width:650px;height:600px;overflow:auto;">
		<div style="width:300px;float:left;">
			<form id="pesform" name="pesform" method="post"  enctype="multipart/form-data" onsubmit="return validate()" action="persistUpdatedBadge.php?id='. $cm->id .'&badge=' . $idOfIncentivetoEdit . '" name="pes-creator" id="pes-creator" style="padding-left:10px;padding-right:10px;">
			<h3>Incentive</h3>
			<table>
				<tr>
					<td style="width:100px">Name</td>
					<td><input type="text" value="' . $Incentive->parentGetter("name") . '" style="margin-right:10px;width:200px;" id="incentiveName" name="incentiveName"><br/><span id="nameReq" style="color:red;display:none;">You Must Specify the Name of the Badge</span></td>
				</tr>
				<tr>
					<td>Price in Points</td>
					<td><input type="text" value="' . $Incentive->parentGetter("priceInPoints") . '" class="required" style="margin-right:10px;width:200px;" id="incentivePrice" name="incentivePrice"><br/><span id="pvReq" style="color:red;display:none;">You Must Specify a price for the incentive</span><span id="pvReqInt" style="color:red;display:none;">You Must Specify a positive number for a Price</span></td>
				</tr>
		
				<tr>
					<td style="vertical-align:top;">Store Visibility</td>
					<td>
					<input type="checkbox" class="required" name="storevis" id="storevis" style="margin-right:10px;width:200px;" value=" '. $Incentive->parentGetter("storeVisibility").' " '. $storeVis.' />
						
					</td>
				</tr>
				<tr>
					<td>Image Selection</td>
					<td><input type="file" value=" '. $Incentive->parentGetter("iconSelection").' "  style="margin-right:10px;width:200px;" id="incentiveImg" name="incentiveImg" ><br/>
					</td>
				</tr>
			</table>
				<br/><br/>
				<!--<input name="Cancel" type="button" style="margin: 0 auto; display:block; border:1px solid #000000; height:20px; padding-left:2px; padding-right:2px; padding-top:0px; padding-bottom:2px; line-height:14px; background-color:#EFEFEF;" onclick="cancelEdit()" value="Cancel Edit"/>
				<br/>-->
				<input name="Submit" type="submit" style="margin: 0 auto; display:block; border:1px solid #000000; height:20px; padding-left:2px; padding-right:2px; padding-top:0px; padding-bottom:2px; line-height:14px; background-color:#EFEFEF;" value="Save Changes to Existing Reward"/>
			
		</form>
		</div>
		<div style="width:300px; float:left; padding-top:50px; padding-left:20px">
			<h4> Current Icon </h4>
			<img style="width:200px;height:200px;"" src="data:image/jpg;base64, '. $Incentive->parentGetter("iconSelection").' " />	
		</div>
	</div>');
	}
	else {
		echo $OUTPUT->box('This page cannot be accessed directly');
	}
	echo "<br/>";
	echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="viewAllIncentives.php?id='. $cm->id .'">Return to the Education++: Manage Incentives Page (Cancel Updating this Incentive)</a></div>');
}
else{
	echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
			<br/>
			<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Rewards</h1>
			<p><a href="storefront.php?id='. $cm->id .'">Visit the Store here</a></p>
		  </div><br/>';
	echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');
}

// Finish the page
echo $OUTPUT->footer();

