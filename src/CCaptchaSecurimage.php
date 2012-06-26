<?php
require_once(TP_SOURCEPATH . 'securimage/securimage.php');
// ===========================================================================================
//
// Class CCaptchaSecurimage
//
// Provides the securimage captcha.
// Requires GD
// 
// For more information see: http://www.phpcaptcha.org/
// 
// Author: Mats Ljungquist
//
class CCaptchaSecurimage extends CCaptcha {
    
        private $sitelink = "";
    
	// ------------------------------------------------------------------------------------
	//
	// Constructor
        //
	public function __construct() {
            $this -> sitelink = WS_SITELINK . "/src/";
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
            $html = <<< EOD
                <img id="captcha" src="{$this -> sitelink}securimage/securimage_show.php" alt="CAPTCHA Image" />
                <p>
                    Please enter the hidden word above:
                </p>
                <div>
                <input id="captchaText" type="text" name="captcha_code" size="10" maxlength="6" /> or 
                <a href="#" onclick="document.getElementById('captcha').src = '{$this -> sitelink}securimage/securimage_show.php?' + Math.random(); return false">[ Different Image ]</a>
                </div>
EOD;
            return $html;
        }
        
        // ------------------------------------------------------------------------------------
	//
        public function validateInput() {
            $this -> errorMsg = "";
            $securimage = new Securimage();
            if ($securimage->check($_POST['captcha_code']) == false) {
                // the code was incorrect
                // handle the error so that the form processor doesn't continue
                $this -> errorMsg = "The captcha wasn't entered correctly. Go back and try again.";
                return false;
            }
            return true;
        }

} // End of Of Class

?>