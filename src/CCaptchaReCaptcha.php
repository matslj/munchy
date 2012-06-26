<?php
require_once(TP_SOURCEPATH . 'recaptcha-php/recaptchalib.php');
// ===========================================================================================
//
// Class CCaptchaReCaptcha
//
// Provides the reCaptcha captcha.
// 
// For more information see: http://recaptcha.net
// 
// Author: Mats Ljungquist
//
class CCaptchaReCaptcha extends CCaptcha {
    
	// ------------------------------------------------------------------------------------
	//
	// Constructor
        //
	public function __construct() {
            $this -> errorMsg = "";
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
            $publickey = reCAPTCHA_PUBLIC; // you got this from the signup page   
            return recaptcha_get_html($publickey);
        }
        
        // ------------------------------------------------------------------------------------
	//
        public function validateInput() {
            $this -> errorMsg = "";
            $privatekey = reCAPTCHA_PRIVATE;
            $resp = recaptcha_check_answer ($privatekey,
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["recaptcha_challenge_field"],
                    $_POST["recaptcha_response_field"]);
            
            if (!$resp->is_valid) {
                // What happens when the CAPTCHA was entered incorrectly 
                $this -> errorMsg = "The reCAPTCHA wasn't entered correctly. Go back and try it again." . 
                        "(reCAPTCHA said: " . $resp->error . ")";
            }
            return true;
        }

} // End of Of Class

?>