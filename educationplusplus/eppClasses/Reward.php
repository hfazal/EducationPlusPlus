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
		
		public function parentGetter($var) {
			return parent::__get($var);
		}
		
		public function getExpiryDateString(){
			return date($this->expiryDate->format('Y-m-d'));
		}
		
		public function getPurchaseTile($remainingQty, $cmid, $iid, $disableBuy){
			$stringToReturn = 	"<style>
									.rewardbox{
										width:400px;
										height:180px;
										background-color:#E0E0E0;
										-webkit-border-radius: 10px;
										-khtml-border-radius: 10px;	
										-moz-border-radius: 10px;
										border-radius: 10px;
										float: left;
										margin: 20px;
									}
									.rewardboxtop{
										border-bottom:thin solid #999999;
										width:100%;
										height:30px;
										line-height:30px;
										text-align:center;
										letter-spacing:4px;
										color:#999999;
									}
									.rewardboxbottom{
										width:100%;
										height:150px;
									}
									.rewardimagecontainer{
										margin: 0 auto;
										width:130px;
										height:130px;
										display: table-cell;
										text-align: center;
										vertical-align: middle;
										float:left;
										padding:10px;
									}
									.rewardimage{
										vertical-align: middle;
										width:130px;
										height:130px;
										-webkit-border-radius: 10px;
										-khtml-border-radius: 10px;	
										-moz-border-radius: 10px;
										border-radius: 10px;
									}
									.rewarddetails{
										text-align:right;
										padding:10px;
										height: 100px;
										width:230px;
										float:right;
										border-bottom:thin solid #999999;
									}
									.rewardpurchasedetails{
										text-align:right;
										width:245px;
										float:right;
										padding-right:5px;
									}
								</style>
								<div class='rewardbox'>
									<div class='rewardboxtop'>
										<p>REWARD</p>
									</div>
									<div class='rewardboxbottom'>
										<div class='rewardimagecontainer'>
											<img class='rewardimage' src='data:image/jpg;base64," . parent::__get("iconSelection") . "' title='" . parent::__get("name") . "' alt='" . parent::__get("name") . "' />
										</div>
										<div class='rewarddetails'>
											<h3 style='max-height:20px;overflow:hidden;'>". parent::__get("name") ."</h3>
											<p style='max-height:80px;overflow:auto;'>" . $this->prize . "</p>
										</div>
										<div class='rewardpurchasedetails'>";
			if ($remainingQty == 0){
				$stringToReturn = $stringToReturn . "<table style='width:100%;margin-right:10px;'>
														<tr>
															<td style='border-left:thin solid #999999;width:100%;font-size:x-small;background-color:#FF8989;text-align:center;'>Sold Out</td>
														</tr>
													</table>";
			}
			else{
				$stringToReturn = $stringToReturn . "<form action='storefront.php?id=" . $cmid . "' method='post'>
													<input type='hidden' name='buy' id='buy' value='" . $iid . "' />
													<table style='width:100%;margin-right:10px;'>
														<tr>";
				if (!$disableBuy){								
					$stringToReturn = $stringToReturn . "<td id='buybutton' style='border-left:thin solid #999999;width:33%;border-right:thin solid #999999;font-size:x-small;background-color:#98fb98;text-align:center;'><input style='float:right;border:none;width:100%;height:100%;background-color:#98fb98' type='submit' name='purchase' id='purchase' value='Buy' /></td>";
				}
				else{
					$stringToReturn = $stringToReturn . "<td id='buybutton' style='width:33%;border-right:thin solid #999999;font-size:x-small;background-color:#98fb98;text-align:center;'>Buy Disabled</td>";
				}
				
				$stringToReturn = $stringToReturn .        "<td style='width:33%;border-right:thin solid #999999;font-size:x-small'>Qty: " . $remainingQty . "/" . parent::__get("qtyPerStudent") . "</td>
															<td style='width:33%;font-size:x-small'>" . parent::__get("priceInPoints") . " Points</td>
														</tr>
													</table>
													</form>";
			}
			
			$stringToReturn = $stringToReturn . "		</div>
													</div>
												</div>";
			return $stringToReturn;
		}
		
		// TOSTRING
		public function __toString() {
			$stringToReturn = 	'You should use getPurchaseTile($remainingQty, $cmid, $iid, $disableBuy) to output the Reward Object';
			return $stringToReturn;
		}       
	}
?>