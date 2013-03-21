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

/// (Replace educationplusplus with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$sid = optional_param('sid', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'leaderboardStudentProfile', "leaderboardStudentProfile.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/leaderboardStudentProfile.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();
if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}
echo "<link rel='stylesheet' type='text/css' href='./css/usecaseboxes.css'>
	<div class='floatingdiv'>Use Case Scenario(s): 5.5.6</div>";
echo '<div id="introbox" style="width:900px;margin:0 auto;text-align:center;margin-bottom:15px;">
		<br/>
		<h1><span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span> Leaderboard Student Profile</h1>
		<p>This is the Leaderboard Student Profile, here you can view all badges earned by a student for all of their classes!</p>
	  </div>';

global $DB;
$studentRecord = $DB->get_record('user',array('id'=>$sid));									// Get user record (From moodle)
$studentEppRecords = $DB->get_records('epp_student',array('student_id'=>$sid));				// Get all student's E++ Records
$studentEppBadgeRecords = $DB->get_records('epp_student_badge',array('student_id'=>$sid));	// Get all student's Badges
$eppIncentiveRecords = $DB->get_records('epp_incentive');									// Get all Incentives
$courseRecords = $DB->get_records('course');												// Get all Courses

$opt = 1;
foreach ($studentEppRecords as $s){
	$opt = $s->leaderboardoptstatus;
	break;
}

if ( $opt == 0 || $USER->id==$sid && ($studentRecord!=null)){
	echo '	<style>
				.badge{
					-webkit-border-radius: 10px;
					-khtml-border-radius: 10px;	
					-moz-border-radius: 10px;
					border-radius: 10px;
					width:100px;
					height:100px;
					vertical-align:middle;
					margin:10px;
				}
				.selfprofile{
					margin:0 auto;
					width:60%;
					border:thin solid black;
					text-align:center;
					padding:20px;
				}
			</style>';

	if ( $USER->id==$sid ){
		if ( $opt == 1 ){
			echo "<div class='selfprofile'>This is your Student Profile Page. You are currently opted out of the leaderboard system.<br/>When another student or professor tries to access this page, they won't be able to see anything. <a href='leaderboardOpt.php?id=". $cm->id ."'>If you like, you can Opt-In here.</a></div>";
		}
		else{
			echo "<div class='selfprofile'>This is your Student Profile Page. You are currently opted into the leaderboard system.<br/>When another student or professor tries to access this page, this is what they'll see. <a href='leaderboardOpt.php?id=". $cm->id ."'>If you like, you can Opt-Out here.</a></div>";
		}
	}
	
	//Figure out the badges
	$badgesOutput = "<span style='font-weight:bold;'>Badge Count: " . count($studentEppBadgeRecords) . "</span><br/><br/>"; // displays number of badges earned

	foreach ($studentEppBadgeRecords as $earnedBadge){
		foreach ($eppIncentiveRecords as $incentive){
			if ($incentive->id == $earnedBadge->incentive_id){
				$badgesOutput = $badgesOutput .  '<img class="badge" src="data:image/jpg;base64,' . $incentive->icon . '" alt="' . $incentive->name . '" title="' . $incentive->name . '" />';
			}
		}
	}
	
	//Figure out the courses
	$coursesOutput = "";
	foreach ($studentEppRecords as $ser){
		foreach ($courseRecords as $c){
			if ($ser->course_id == $c->id){
				$coursesOutput = $coursesOutput . "<li>" . $c->fullname . "</li>";
			}
		}
	}
	
	echo '<br/><table style="border:none;margin:0 auto;">
			<tr>
				<td colspan="2" style="text-align:center;width:100%;"><h2>'.
					fullname($studentRecord)
				.'</h2></td>
			</tr>
			<tr>
				<td style="width:230px;text-align:center;background-color:#EEE">'. $OUTPUT->user_picture($studentRecord, array('size'=>200)) .'</td>
				<td rowspan="2" style="text-align:center;width:400px;background-color:#DDD;vertical-align:top;">'. $badgesOutput .'</td>
			</tr>
			<tr>
				<td style="background-color:#AAA">
					<span style="font-weight:bold;">Education++ Courses</span>
					<ul>'.
						$coursesOutput
					.'</ul>
				</td>
			</tr>
		  </table>';
}
else{
	echo "<div style='text-align:center'>This User has opted out of the leaderboard system and their profile is not available</div>";
}

echo '<br/><br/><br/>';
echo $OUTPUT->box('<div style="width:100%;text-align:center;cursor:pointer;cursor: hand;"><a onclick="window.history.back()">Return to the Previous Page</a></div>');
echo '<br/>';
echo $OUTPUT->box('<div style="width:100%;text-align:center;"><a href="view.php?id='. $cm->id .'">Return to the Education++ homepage</a></div>');
	  
// Finish the page
echo $OUTPUT->footer();
