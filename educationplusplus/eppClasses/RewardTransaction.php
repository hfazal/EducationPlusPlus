<?php
	require_once 'Transaction.php';

	class RewardTransaction extends Transaction {
		private $student;
		private $completionStatus;
	
    	//CONSTRUCTOR
    	function __construct( $id, $name, $pointsInvolved, $dateOfTransaction, $stu, $completionStatus ){
    		parent::__construct( $id, $name, $pointsInvolved, $dateOfTransaction );
			if ($stu){
				$this->student = $stu;
			}
			if ($completionStatus){
				$this->completionStatus = $completionStatus;
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
		public function parentGetter($var) {
			return parent::__get($var);
		}

		// SETTER
		public function __set($property, $value) {
			if (property_exists($this, $property)) {
				$this->$property = $value;
			}
			return $this;
		}
			 		 
		// TOSTRING: Prints a row
		public function __toString() {
			// Reporting
			if ($this->student){
				$stringToReturn = 	"<tr>
										<td style='width:20%'>
											<span class='rewardDate'>" . parent::__get("dateOfTransaction")->format('F jS Y') . "</span>
										</td>
										<td style='width:20%'>
											<span class='rewardStudent'>" . $this->student->firstName . " " . $this->student->lastName . "</span>
										</td>
										<td style='width:40%'>
											<span class='rewardName'>" . parent::__get("name") . "</span>
										</td>
										<td style='width:20%'>";

				if (intval($this->completionStatus) != 0){
					$stringToReturn = $stringToReturn . "Awarded <input type='checkbox' name='completion[]' value='" . parent::__get("id") . "' checked='checked' ";
				}
				else{
					$stringToReturn = $stringToReturn . "Not Awarded Yet <input type='checkbox' name='completion[]' value='" . parent::__get("id") . "'";
				}
				$stringToReturn = $stringToReturn . "/></td></tr>";
			}
			// Transaction History
			else{
				$stringToReturn = 	"<tr>
										<td style='width:25%'>
											<span class='rewardDate'>" . parent::__get("dateOfTransaction")->format('F jS Y') . "</span>
										</td>
										<td style='width:50%' class='rewardIndent'>
											<span class='rewardName'><span style='font-weight:bold'>Spent:</span> " . parent::__get("name") . "</span>
										</td>
										<td style='width:25%' class='rewardIndent'>
											<span class='rewardPoints'>" . parent::__get("pointsInvolved") . "</span>
										</td>
									</tr>";
			}
			return $stringToReturn;
		}       
	}
?>