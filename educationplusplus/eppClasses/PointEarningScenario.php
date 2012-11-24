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
?>