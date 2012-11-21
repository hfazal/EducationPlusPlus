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
 * Prints a particular instance of education++
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage education++
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace education++ with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // education++ instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('education++', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $education++  = $DB->get_record('education++', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $education++  = $DB->get_record('education++', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $education++->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('education++', $education++->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'education++', 'view', "view.php?id={$cm->id}", $education++->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/education++/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($education++->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('education++-'.$somevar);

// Output starts here
echo $OUTPUT->header();

if ($education++->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('education++', $education++, $cm->id), 'generalbox mod_introbox', 'education++intro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Yay! It works!');

// Finish the page
echo $OUTPUT->footer();
