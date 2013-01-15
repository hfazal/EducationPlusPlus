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
		function __construct( $id, $course_id, $firstname, $lastname, $student_id, $currentpointbalance, $accumulatedpoints, $leaderboardoptstatus ) {
			if (!empty($id)){	//Only if constructed off of a DB retrieval
				$this->id = $id;
			}
			$this->courseId = $course_id;
			$this->firstName = $firstname;
			$this->lastName = $lastname;
			$this->studentId = $student_id;
			$this->currentPointBalance = $currentpointbalance;
			$this->accumulatedPoints = $accumulatedpoints;
			$this->leaderboardOptStatus = $leaderboardoptstatus;
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