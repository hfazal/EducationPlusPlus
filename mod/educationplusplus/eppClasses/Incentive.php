<?php
	class Incentive {
		private $name;
		private $qtyPerStudent;
		private $storeVisibility;
		private $priceInPoints;
		private $iconSelection;
		private $deletedByProf;
		private $creationDate;

		// CONSTRUCTOR
		function __construct( $name, $qtyPerStudent, $storeVisibility, $priceInPoints, $iconSelection, $deletedByProf, $creationDate ) {
			$this->name = $name;
			$this->qtyPerStudent = $qtyPerStudent;
			$this->storeVisibility = $storeVisibility;
			$this->priceInPoints = $priceInPoints;
			$this->iconSelection = $iconSelection;
			$this->deletedByProf = $deletedByProf;
			$this->creationDate = $creationDate;
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
		
		public function getQuantity(){
			return $self->qtyPerStudent;
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