<?php
	class Transaction {
		private $id;
		private $name;
		private $pointsInvolved;
		private $dateOfTransaction;

		// CONSTRUCTOR
		function __construct( $id, $name, $pointsInvolved, $dateOfTransaction ){
			$this->id = $name;
			$this->name = $name;
			$this->pointsInvolved = $pointsInvolved;
			$this->dateOfTransaction = $dateOfTransaction;
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