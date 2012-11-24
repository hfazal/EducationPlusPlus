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
			$this->name = $name;
			$this->pointValue = $pointValue;
			$this->description = $description;
			$this->requirementSet = $requirementSet;
			$this->expiryDate = $expiryDate;
			$this->deletedByProf = $deletedByProf;
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
			$stringToReturn = $this->name . "<br/>" . $this->pointValue . " Point Value<br/>Expires on " . $this->expiryDate->format('m-d-Y') . "<br/>";
			for ($i=0; $i<count($this->requirementSet); $i++){
				$stringToReturn = $stringToReturn . $this->requirementSet[$i] . "<br/>";
			}
			return $stringToReturn;
		}
	}
	
	class Requirement {
		private $activity;
		private $condition;				//This is an integer. See Rules for Condition below
		private $percentToAchieve;
		
		/* 	Rules for Condition:
			"0"  -> Complete  | If Activity has been Completed ($percentToAchieve will be ignored)
			"1"  -> >         | If Activity's Grade (in percent) >= $percentToAchieve
			"2"  -> >=        | If Activity's Grade (in percent) > $percentToAchieve
			"3"  -> =         | If Activity's Grade (in percent) is exactly $percentToAchieve
		*/
		
		// CONSTRUCTOR
		function __construct( $activity, $condition, $percentToAchieve ) {
			$this->activity = $activity;
			$this->condition = $condition;
			$this->percentToAchieve = $percentToAchieve;
		}

		// DESTRUCTOR
		function __destruct() {
			// Nothing to do here
		}
		
		// TOSTRING
		public function __toString() {
			switch ($this->condition) {
				case 0:	// Complete
					$stringToReturn = "Complete the activity " . $this->activity;
					break;
				case 1:	// >
					$stringToReturn = "Get more than " . $this->percentToAchieve . "% on " . $this->activity;
					break;
				case 2:	// >=
					$stringToReturn = "Get " . $this->percentToAchieve . " % or more on " . $this->activity;
					break;
				case 3:	// =
					$stringToReturn = "Get exactly " . $this->percentToAchieve . "% on the activity " . $this->activity;
					break;
				default:
					$stringToReturn = "ERROR";
			}
			return $stringToReturn;
		}
	}
	
	class Activity {
		private $name;
		private $weight;
		
		// CONSTRUCTOR
		function __construct( $name, $weight ) {
			$this->name = $name;
			if (!empty($weight)){
				$this->weight = $weight;
			}
		}
		
		// DESTRUCTOR
		function __destruct() {
			// Nothing to do here
		}
		
	   	// TOSTRING
		public function __toString() {
			$stringToReturn = $this->name;
			return $stringToReturn;
		}
	}
?>