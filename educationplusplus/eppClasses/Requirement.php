<?php
	class Requirement {
		private $activity;
		private $condition;				//This is an integer. See Rules for Condition below
		private $percentToAchieve;
		
		/* 	Rules for Condition:
			"0"  -> Complete  | If Activity has been Completed ($percentToAchieve will be ignored)
			"1"  -> >         | If Activity's Grade (in percent) >= $percentToAchieve
			"2"  -> >=        | If Activity's Grade (in percent) > $percentToAchieve
			"3"  -> =         | If Activity's Grade (in percent) is exactly $percentToAchieve
		*/
		
		// CONSTRUCTOR
		function __construct( $activity, $condition, $percentToAchieve ) {
			$this->activity = $activity;
			$this->condition = $condition;
			$this->percentToAchieve = $percentToAchieve;
		}

		// DESTRUCTOR
		function __destruct() {
			// Nothing to do here
		}
		
		// TOSTRING
		public function __toString() {
			switch ($this->condition) {
				case 0:	// Complete
					$stringToReturn = "Complete " . $this->activity;
					break;
				case 1:	// >
					$stringToReturn = "Get more than " . $this->percentToAchieve . "% on " . $this->activity;
					break;
				case 2:	// >=
					$stringToReturn = "Get " . $this->percentToAchieve . " % or more on " . $this->activity;
					break;
				case 3:	// =
					$stringToReturn = "Get exactly " . $this->percentToAchieve . "% on " . $this->activity;
					break;
				default:
					$stringToReturn = "ERROR";
			}
			return $stringToReturn;
		}
	}
?>