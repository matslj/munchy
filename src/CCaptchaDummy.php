<?php

// ===========================================================================================
//
// Class CCaptchaDummy
//
// Provides no Captcha.
// 
// Author: Mats Ljungquist
//
class CCaptchaDummy extends CCaptcha {
    
	// ------------------------------------------------------------------------------------
	//
	// Constructor
        //
	public function __construct() {
	}

	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
	}
        
        // ------------------------------------------------------------------------------------
        // 
        public function displayHTML() {
            ;
        }
        
        // ------------------------------------------------------------------------------------
	//
        public function validateInput() {
            return true;
        }

} // End of Of Class

?>