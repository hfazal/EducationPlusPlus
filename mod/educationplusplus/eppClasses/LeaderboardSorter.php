<?php
	class LeaderboardSorter {
		private $studentid;
		private $firstname;
		private $lastname;
		private $badgecount;

		// CONSTRUCTOR
		function __construct( $id, $firstname, $lastname, $badgecount ){
			$this->studentid = $id;
			$this->firstname = $firstname;
			$this->lastname = $lastname;
			$this->badgecount = $badgecount;
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
			$stringToReturn = "";
			return $stringToReturn;
		}
	}
?>