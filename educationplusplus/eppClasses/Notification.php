<?php
	class Notification {
		private $student_id;
		private $title;
		private $content;
		private $read;
		private $expiryDate;

		// CONSTRUCTOR
		function __construct( $student_id, $title, $content, $read, $expiryDate ) {
			$this->name = $name;
			$this->pointValue = $pointValue;
			$this->description = $description;
			$this->requirementSet = $requirementSet;
			$this->expiryDate = $expiryDate;
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
			$stringToReturn = "<span class='pesName'>" . $this->name . "</span> <span class='pesPointValue'>(" . $this->pointValue . " Points)</span><br/><span class='pesExpiryDate'>Expires on " . $this->expiryDate->format('m-d-Y') . "</span><br/><span class='pesDescription'>" . $this->description . "</span>";
			$stringToReturn = $stringToReturn . "<ul>";
			for ($i=0; $i<count($this->requirementSet); $i++){
				$stringToReturn = $stringToReturn . "<li span='pesRequirements'>" . $this->requirementSet[$i] . "</li>";
			}
			$stringToReturn = $stringToReturn . "</ul>";
			return $stringToReturn;
		}
	}
?>