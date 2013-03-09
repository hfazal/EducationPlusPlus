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
										<p>BADGE</p>
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
															<td style='width:100%;font-size:x-small;background-color:#FF8989;text-align:center;'>Sold Out</td>
														</tr>
													</table>";
			}
			else{
				$stringToReturn = $stringToReturn . "<form action='storefront.php?id=" . $cmid . "' method='post'>
													<input type='hidden' name='buy' id='buy' value='" . $iid . "' />
													<table style='width:100%;margin-right:10px;'>
														<tr>";
				if (!$disableBuy){								
					$stringToReturn = $stringToReturn . "<td id='buybutton' style='width:33%;border-right:thin solid #999999;font-size:x-small;background-color:#98fb98;text-align:center;'>Buy</td>";
				}
				else{
					$stringToReturn = $stringToReturn . "<td id='buybutton' style='width:33%;border-right:thin solid #999999;font-size:x-small;background-color:#98fb98;text-align:center;'>Buy Disabled</td>";
				}
				
				$stringToReturn = $stringToReturn .        "<td style='width:33%;border-right:thin solid #999999;font-size:x-small;'>Qty: " . $remainingQty . "/" . parent::__get("qtyPerStudent") . "</td>
															<td style='width:33%;font-size:x-small;'>" . parent::__get("priceInPoints") . " Points</td>
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
										<div style='width:200px;height:200px;display:table-cell;vertical-align:middle'>
											<img style='max-width:200px;max-height:200px;' src='data:image/jpg;base64," . parent::__get("iconSelection") . "' title='" . parent::__get("name") . "' />
										</div>
									</td>
								</tr>
								</table>";
			return $stringToReturn;
		}
	}
?>