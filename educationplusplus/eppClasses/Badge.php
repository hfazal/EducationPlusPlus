<?php
	require_once 'Incentive.php';

	class Badge extends Incentive {
		//Possible Description - TBD

    	//CONSTRUCTOR
    	function __construct( $name, $qtyPerStudent, $storeVisibility, $priceInPoints, $iconSelection, $deletedByProf, $prize, $expiryDate){
    		parent::__construct($name, $qtyPerStudent, $storeVisibility, $priceInPoints, $iconSelection, $deletedByProf);
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
			 		 
		// TOSTRING
		public function __toString() {
			$stringToReturn = "";
			return $stringToReturn;
		}    
	}
?>