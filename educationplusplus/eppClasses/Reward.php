<?php
	require_once 'Incentive.php';

	class Reward extends Incentive {
    	private $prize;
    	private $expiryDate;

    	//CONSTRUCTOR
    	function __construct( $name, $qtyPerStudent, $storeVisibility, $priceInPoints, $iconSelection, $deletedByProf, $creationDate, $prize, $expiryDate){
    		parent::__construct($name, $qtyPerStudent, $storeVisibility, $priceInPoints, $iconSelection, $deletedByProf, $creationDate);
    		$this->prize = $prize;
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
		
		public function quantityAllowedPerStudent() {
			return parent::__get("qtyPerStudent");
		}
			 		 
		// TOSTRING
		public function __toString() {
			$stringToReturn = "<table style='width:100%'>
								<tr>
									<td style='width:50%'>
										<span class='rewardName'>" . parent::__get("name") . "</span>
										<br/>
										<span class='rewardExpiryDate'>Expires on " . $this->expiryDate->format('m-d-Y') ."</span>
									</td>
									<td style='width:50%'>
										<span class='rewardPrice'>" . parent::__get("priceInPoints") . " Points</span><br/>
									</td>
								</tr>
								<tr>
									<td>
										<img style='width:200px;height:200px;' src='data:image/jpg;base64," . parent::__get("iconSelection") . "' alt='" . parent::__get("name") . "' />
									</td>
									<td>
										<span class='rewardDescription'>" . $this->prize . "</span>
									</td>
								</tr>
							</table>";
							   
			return $stringToReturn;
		}       
	}
?>