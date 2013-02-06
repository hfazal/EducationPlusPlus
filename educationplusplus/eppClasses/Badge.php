<?php
	require_once 'Incentive.php';

	class Badge extends Incentive {
		//Possible Description - TBD

    	//CONSTRUCTOR
    	function __construct( $name, $qtyPerStudent, $storeVisibility, $priceInPoints, $iconSelection, $deletedByProf, $creationDate){
    		parent::__construct($name, $qtyPerStudent, $storeVisibility, $priceInPoints, $iconSelection, $deletedByProf, $creationDate);
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
		
		public function parentGetter($var) {
			return parent::__get($var);
		}
			 		 
		// TOSTRING
		public function __toString() {
			$stringToReturn = "<table style='width:100%'>
								<tr>
									<td style='width:50%'>
										<span class='badgeName'>" . parent::__get("name") . "</span>
									</td>
									<td style='width:50%'>
										<span class='badgePrice'>" . parent::__get("priceInPoints") . " Points</span><br/>
									</td>
								</tr>
								<tr>
									<td>
										<img style='width:200px;height:200px;' src='data:image/jpg;base64," . parent::__get("iconSelection") . "' alt='" . parent::__get("name") . "' />
									</td>
								</tr>
								</table>";
			return $stringToReturn;
		}
	}
?>