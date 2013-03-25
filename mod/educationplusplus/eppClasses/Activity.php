<?php
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
			$stringToReturn = $this->name;
			return $stringToReturn;
		}
	}
?>