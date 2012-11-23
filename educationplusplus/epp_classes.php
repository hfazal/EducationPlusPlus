<?php
	class PointEarningScenario {
		private $name;
		private $pointValue;
		private $description;
		private $requirementSet;
		private $expiryDate;
		private $deletedByProf;

		// CONSTRUCTOR
		function __construct( $name, $pointValue, $description, $requirementSet, $expiryDate, $deletedByProf ) {
			//print "In constructor\n";
			$this->name = $name;
			$this->pointValue = $pointValue;
			$this->description = $description;
			$this->requirementSet = $requirementSet;
			$this->expiryDate = $expiryDate;
			$this->deletedByProf = $deletedByProf;
		}

		// DESTRUCTOR
		function __destruct() {
		   //print "Destroying " . $this->name . "\n";
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
	}
	
	class Requirement {
		private $activity;
		private $condition;				//This is a string. See Rules for Condition below
		private $percentToAchieve;
		
		/* 	Rules for Condition:
			"1"    | If Activity has been Completed ($percentToAchieve will be ignored)
			">"    | If Activity's Grade (in percent) >= $percentToAchieve
			">="   | If Activity's Grade (in percent) > $percentToAchieve
			"="    | If Activity's Grade (in percent) is exactly $percentToAchieve
		*/
		
		function __construct( $activity, $condition, $percentToAchieve ) {
		//print "In constructor\n";
			$this->activity = $activity;
			$this->condition = $condition;
			$this->percentToAchieve = $percentToAchieve;
		}

	   function __destruct() {
		   //print "Destroying";
	   }
	}
	
	class Activity {
		private $name;
		private $gradeInPercent;
		
	   function __construct( $name, $gradeInPercent ) {
		   //print "In constructor\n";
		   $this->name = $name;
		   $this->gradeInPercent = $gradeInPercent;
	   }

	   function __destruct() {
		   //print "Destroying " . $this->name . "\n";
	   }
	}
	
	//TESTER AREA TO TEST YOUR CLASSES
	//Activity
	$act = new Activity("Midterm", 90);
	
	//Requirement
	$req = new Requirement("Pass all Tests", 1000, "Pass all tests in this course and earn 1000 points", "x", new DateTime("now"), false);
	print "A New Point Earning Scenario: " . $pes->name;
	
	//PointEarningScenario
	$pes = new PointEarningScenario("Pass all Tests", 1000, "Pass all tests in this course and earn 1000 points", "x", new DateTime("now"), false);
	print "A New Point Earning Scenario: " . $pes->name;
	
?>