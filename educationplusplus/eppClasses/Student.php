<?php
	class Student {
		private $id;
		private $courseId;
		private $firstName;
		private $lastName;
		private $studentId;
		private $currentPointBalance;
		private $accumulatedPoints;
		private $leaderboardOptStatus;
		
		// CONSTRUCTOR
		function __construct(){
			$this->id = null;
			$this->courseId = null;
			$this->firstName = null;
			$this->lastName = null;
			$this->studentId = null;
			$this->currentPointBalance = null;
			$this->accumulatedPoints = null;
			$this->leaderboardOptStatus = null;
		}
		
		function addData( $id, $course_id, $firstname, $lastname, $student_id, $currentpointbalance, $accumulatedpoints, $leaderboardoptstatus ) {
			$this->id = intval($id);
			$this->courseId = intval($course_id);
			$this->firstName = $firstname;
			$this->lastName = $lastname;
			$this->studentId = intval($student_id);
			$this->currentPointBalance = intval($currentpointbalance);
			$this->accumulatedPoints = intval($accumulatedpoints);
			$this->leaderboardOptStatus = intval($leaderboardoptstatus);
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
			$stringToReturn = $this->firstName . ' ' . $this->lastName;
			return $stringToReturn;
		}
	}
?>