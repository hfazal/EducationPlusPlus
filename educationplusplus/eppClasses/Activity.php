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
		
	   	// TOSTRING
		public function __toString() {
			$stringToReturn = $this->name;
			return $stringToReturn;
		}
	}
?>