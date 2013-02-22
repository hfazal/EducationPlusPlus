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
// Education++ Classes
require 'eppClasses/Notification.php';

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // educationplusplus instance ID - it should be named as the first character of the module
$added = optional_param('newpes', 0, PARAM_INT);

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

add_to_log($course->id, 'educationplusplus', 'view', "view.php?id={$cm->id}", $educationplusplus->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/educationplusplus/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($educationplusplus->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('educationplusplus-'.$somevar);

// Output starts here
echo $OUTPUT->header();

if ($educationplusplus->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('educationplusplus', $educationplusplus, $cm->id), 'generalbox mod_introbox', 'educationplusplusintro');
}

//Help box, displayed on first launch, and when triggered

global $DB;

/* Set up to Education++ Resources.
1. Determine if current user has Professor Level Access
2. If not a professor, determine if first usage (no entry in epp_student). If so, make an entry, tell them about opting out
3. Scan Gradebook if updated since last scan (Preshoth)
4. Show Notifications for user (Robert)
 */

// START OF 1. Determine if current user has Professor Level Access
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$isProfessor = false;
if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
	$isProfessor = true;
}
// END OF 1. Determine if current user has Professor Level Access

// START OF 2. If not a professor, determine if first usage (no entry in epp_student). If so, make an entry, tell them about opting out.
if (!$isProfessor){	//Student
	$students = $DB->get_records('epp_student',array('course_id'=>$course->id));
	$matchingRecordFound = false;
	
	foreach ($students as $studentRecord){
		if ($USER->id == $studentRecord->student_id){
			//Record Match
			$matchingRecordFound = true;
			break;
		}
	}
		
	echo '<script src="jquery-1.9.0.min.js"></script>
	  <div id="introbox" style="width:900px;margin:0 auto;text-align:center;border:thin gray solid;margin-bottom:15px;';
	  
	if ($matchingRecordFound == false){ //If no matching student record for this class was found,
		// make one, then tell user about opting out
		$record 						= new stdClass();
		$record->course_id 				= intval($course->id);
		$record->firstname	 			= $USER->firstname;
		$record->lastname 				= $USER->lastname;
		$record->student_id 			= intval($USER->id);
		$record->currentpointbalance 	= 0;
		$record->accumulatedpoints		= 0;
		
		// Pull all instances of the student in epp_student. If there is no record (in another course), opt them in. If there is, follow that leaderboard opt status 
		$s = $DB->get_records('epp_student',array('student_id'=>$USER->id));
		$newOptStatus = 0;
		if ($s){
			foreach ($s as $s1){
				$newOptStatus = $s1->leaderboardoptstatus;
				break;
			}
		}
		
		$record->leaderboardoptstatus 	= $newOptStatus;	//See above
		$idOfPES = $DB->insert_record('epp_student', $record, true);
	}
	else {
		echo 'display:none';
	}
	echo'">
		<div style="width:90%;display:inline-block;text-align:right;color:red;font-size:small;cursor:pointer;cursor:hand;" onclick="$(\'#introbox\').hide();">dismiss</div>
		<br/>
		<h1>Welcome to <span style="color:#FFCF08">Education</span><span style="color:#EF1821">++</span>!</h1>
		<p>Ever felt you deserved more for all the work you do in school? With Education++, you can earn points for all you do, then spend them on rewards! For example, if you Ace your Midterm, you could get points you can spend on dropping a quiz or two!</p>
		<p>In addition, you can <a style="text-decoration:underline" href="leaderboardClass.php?id='. $cm->id .'">compete with your classmates</a> for bragging rights of who has the most points in the class. Best of all, you can <a style="text-decoration:underline" href="leaderboardSchool.php?id='. $cm->id .'">solidfy your name in your school\'s history</a> by earning the most badges ever on the school leaderboard. Don\'t want to take part in the leaderboard? No problem, you can <a style="text-decoration:underline" href="leaderboardOpt.php?id='. $cm->id .'">opt out here</a> and still earn rewards!</p>
		<p>Ready to get started? <a style="text-decoration:underline" href="viewAllPES.php?id='. $cm->id .'">Check out all the rewards your Professor has set up for you here!</a></p>
	  </div>';
}
// END OF 2. If not a professor, determine if first usage (no entry in epp_student). If so, make an entry, tell them about opting out.

// START OF 3. Scan Gradebook if updated since last scan
	//Preshoth's Scan Gradebook code
// END OF 3. Scan Gradebook if updated since last scan

// START OF 4. Show Notifications for user
	
	$allNotifications = $DB->get_records('epp_notification',array('course'=>$course->id, 'student_id'=>$USER->id));
	$arrayOfNewNotificationObjects = array();
	$arrayOfIDsForNotificationObjects = array();

	// Output starts here
	if ($allNotifications){
		foreach ($allNotifications as $notification){
			//New
			if ($notification->isread == 0){
				array_push($arrayOfNewNotificationObjects, $newNotification = new Notification(0, $notification->title, $notification->content, 1, new DateTime($notification->expirydate)));
				array_push($arrayOfIDsForNotificationObjects, $notification->id);
			}
		}
	}

	echo '	<script>
			function confirmDelete(index){
				var x;
				var r = confirm("Are you sure you want to dismiss this notification?");
				if (r==true){
					var newNotification = document.getElementById("new"+index);
					newNotification.setAttribute("style", "color: black; background-color: white");
				}
				else{}
			}
		</script>';	
	
// END OF 4. Show Notifications for user

echo '<div id="eppContainer" style="width:950px;margin:0 auto;">';
	echo '<div style="float:left; margin-left:30px;margin-right:30px;text-align:center;"><img src="pix/logo.png" alt="education++" />';
	if (!$isProfessor){	//show help only if student
		echo '<br/><span style="color:red;font-size:small;cursor:pointer;cursor:hand;" onclick="$(\'#introbox\').show();">help!</span>';
	}
	echo '</div>';
	if ($isProfessor){
		echo '<div style="float:left;margin:30px;">
			<h2 style="font-size:large">Administrator</h2>
			<h3>Point Earning Scenerio Tools</h3>
			<a href="viewAllPES.php?id='. $cm->id .'">Manage Scenarios in which Students can Earn Points</a><br/>
			<s><a href="gradebookparser.php?id='. $cm->id .'">Scan Gradebook to detect Met Scenarios (Will auto trigger)</a></s><br/>
			<br/>
			<h3>Reward Tools</h3>
			<a href="viewAllIncentives.php?id='. $cm->id .'">Manage Rewards in which Students can Spend Points on</a><br/>
			<br/>
			<h3>Reporting</h3>
			<a href="transactionHistory.php?id='. $cm->id .'">View A Student\'s Transactions</a><br/>
			<a href="reportingGeneral.php?id='. $cm->id .'">Generate a report and track All Reward Purchases</a><br/>
			<br/>
			<h3>Notifications</h3>
			<a href="createANotification.php?id='. $cm->id .'">Create a New Notification for all Users in this Class</a><br/>
			<a href="viewNotifications.php?id='. $cm->id .'">View all Notifications Sent Out</a><br/>
			<br/>
			<h3>View the Leaderboard</h3>
			<a href="leaderboardClass.php?id='. $cm->id .'">View the Class Leaderboard</a><br/>
			<a href="leaderboardSchool.php?id='. $cm->id .'">View the Schoolwide Leaderboard</a><br/>
		</div>';
	}
	else{
		//Since Student, Obtain Point Balance
		$eppStudentRecord = $DB->get_record('epp_student',array('course_id'=>$course->id, 'student_id'=>$USER->id));
		//echo var_dump($eppStudentRecord);
		echo '<span style="color:red;font-weight:bold;">';
		if (!$eppStudentRecord){
			//echo 'Error: No student record was found, check step 2 of page setup';
		}
		else{
			echo $eppStudentRecord->currentpointbalance . ' Points';
		}
		echo '</span>';
	
		//Options
		echo '<div style="float:left;margin:30px;">
			
			<h2 style="font-size:large">Student <a href="leaderboardStudentProfile.php?id=' . $cm->id . '&sid=' . $USER->id .'">(View Your Profile)</a></h2>
			<h3>Point Earning Scenerios</h3>
			<a href="viewAllPES.php?id='. $cm->id .'">View All the Ways you Can Earn Points</a><br/>
			<br/>
			<h3>Rewards</h3>
			<a href="storeFront.php?id='. $cm->id .'">View the Store to Purchase Rewards</a><br/>
			<br/>
			<h3>Transactions</h3>
			<a href="transactionHistory.php?id='. $cm->id .'">View All the Ways You Have Already Spent and Earned Points</a><br/>
			<br/>
			<h3>View the Leaderboard</h3>
			<a href="leaderboardClass.php?id='. $cm->id .'">View the Class Leaderboard</a><br/>
			<a href="leaderboardSchool.php?id='. $cm->id .'">View the Schoolwide Leaderboard</a><br/>
			<a href="leaderboardOpt.php?id='. $cm->id .'">Opt in or out of the Leaderboard</a><br/>
			<br/>
			<h3>Your Notifications</h3>
			<ul>';
			if ($arrayOfNewNotificationObjects){
				for ($i=0; $i < count($arrayOfNewNotificationObjects); $i++){
					echo '<li title="' . $arrayOfNewNotificationObjects[$i]->content . '">' . $arrayOfNewNotificationObjects[$i]->title . ' <a href="dismissNotification.php?id='. $cm->id .'&notId='. $arrayOfIDsForNotificationObjects[$i] .'" onclick="confirmDelete(' . $i . ')"><em style="color:red">dismiss</em></a></li>';
				}
			}
			else {
				echo '<li>(no new notifications)</li>';
			}
			echo '</ul><a style="font-style:italic;" href="viewNotifications.php?id='. $cm->id .'">View Dismissed Notifications</a></div>';
	}
echo '</div>';
// Finish the page
echo $OUTPUT->footer();
