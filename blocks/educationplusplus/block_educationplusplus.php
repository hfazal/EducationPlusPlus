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
 * @package    block_educationplusplus
 * @copyright  2013 Husain Fazal, Preshoth Paramalingam, Robert Stanica
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Education++ Classes
class Notification {
        private $student_id;
        private $title;
        private $content;
        private $read;
        private $expiryDate;
        private $course;

        // CONSTRUCTOR
        function __construct( $student_id, $title, $content, $read, $expiryDate, $course ) {
            $this->student_id = $student_id;
            $this->title = $title;
            $this->content = $content;
            $this->read = $read;
            $this->expiryDate = $expiryDate;
            $this->course = $course;
        }

        // DESTRUCTOR
        function __destruct() {
           // Nothing to do here
        }
       
        // GETTER
        public function __get($property) {
            if (property_exists($this, $property)) {
                return $this->$property;
            }
        }

        // SETTER
        public function __set($property, $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
            return $this;
        }
        
        // TOSTRING
        public function __toString() {
            $stringToReturn = "<span class='notificationTitle'>" 
            . $this->title 
            . "</span> <span class='notificationContents'><br/>" 
            . $this->content 
            . "</span><br/><span class='notificationExpiryDate'>Expires on <em>" 
            . $this->expiryDate->format('m-d-Y')
            . "</em></span><br/>";
            return $stringToReturn;
        }
    }

class block_educationplusplus extends block_base {

    function init() {
        $this->title = get_string('educationplusplus', 'block_educationplusplus');
    }

    public function get_content() {
        if ($this->content !== null) {
          return $this->content;
        }

        $contentstring = "";

        $this->content         =  new stdClass;
        $this->content->footer = '';
     
        $logoimage = new moodle_url('/blocks/educationplusplus/pix/logo.png', null);
        $contentstring = '<div id="container" style="text-align:center"><img src="'. $logoimage .'" alt="Education++" style="width:30%;float:left;" /><strong>Messages</strong><br/>';
        global $DB, $USER;
        
        $allNotifications = $DB->get_records('epp_notification',array('student_id'=>$USER->id),'course','*',null,10);
        $arrayOfNewNotificationObjects = array();
        $arrayOfIDsForNotificationObjects = array();
        $allCourses = $DB->get_records('course',null);

        //echo var_dump($allNotifications);

        // Output starts here
        if ($allNotifications){
            foreach ($allNotifications as $notification){
                //New
                if ($notification->isread == 0){
                    array_push($arrayOfNewNotificationObjects, $newNotification = new Notification(0, $notification->title, $notification->content, 1, new DateTime($notification->expirydate), $notification->course));
                    array_push($arrayOfIDsForNotificationObjects, $notification->id);
                }
            }
            $contentstring = $contentstring . '<ul>';
            if ($arrayOfNewNotificationObjects){
                for ($i=0; $i < count($arrayOfNewNotificationObjects); $i++){
                    if ($allCourses){
                        foreach ($allCourses as $c){
                            //echo $c->id . " -----> " . $arrayOfNewNotificationObjects[$i]->course . "<br/>";
                            if ($c->id == $arrayOfNewNotificationObjects[$i]->course){
                                $contentstring = $contentstring . '<li style="list-style:none;" title="' . $arrayOfNewNotificationObjects[$i]->content . '">' . $arrayOfNewNotificationObjects[$i]->title . ' (' . $c->shortname . ')</li>';
                            }
                        }
                    }
                }
            }
            $contentstring = $contentstring . '</ul>';
        }
        else {
            $contentstring = $contentstring . "you have no new notifications";
        }

        $this->content->text = $contentstring . "</div>";


        return $this->content;
    }
}
