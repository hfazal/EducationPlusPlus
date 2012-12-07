<?php
	class Notification {
		private $student_id;
		private $title;
		private $content;
		private $read;
		private $expiryDate;

		// CONSTRUCTOR
		function __construct( $student_id, $title, $content, $read, $expiryDate ) {
			$this->student_id = $student_id;
			$this->title = $title;
			$this->content = $content;
			$this->read = $read;
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
		
		// TOSTRING
		public function __toString() {
			$stringToReturn = "<span class='notificationTitle'>" 
			. $this->title 
			. "</span> <span class='notificationContents'><br/>" 
			. $this->content 
			. "</span><br/><span class='notificationExpiryDate'>Expires on <em>" 
			. $this->expiryDate->format('m-d-Y')
			. "</em></span><br/>";
			return $stringToReturn;
		}
	}
?>