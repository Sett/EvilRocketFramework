<?php
/**
 * Class-oriented model for working with reCaptcha
 *
 * @author Se#
 */
require_once(__DIR__ . '/Recaptcha/recaptchalib.php');

class Evil_Captcha_Recaptcha
{
    /**
     * Contain HTML of a captcha
     *
     * @var string
     */
	protected $_captcha = '';

    /**
     * Create and possible echo a captcha
     *
     * @param string $pub
     * @param string $pri
     * @param bool $echo
     * @return void
     */
	public function __construct($pub, $pri, $echo = false, $challenge = true, $error = null)
	{
        if(true === $challenge)
		    $error = Evil_Captcha_Recaptcha::challenge($pri);

		$this->_captcha = recaptcha_get_html($pub, $error);
		if(true === $echo)
			echo $this->_captcha;
	}

    /**
     * Echo the $this->_captcha
     *
     * @return void
     */
	public function __toString()
	{
		echo $this->_captcha;
	}

    /**
     * Check captcha
     *
     * @static
     * @param  $privateKey
     * @return null | string
     */
	public static function challenge($pri, $post = null, $server = null)
	{
        if(empty($post))
            $post = $_POST;

        if(empty($server))
            $server = $_SERVER;

		$error = null;
		if ($post["recaptcha_response_field"]) {
		
			$resp = recaptcha_check_answer($pri,
                                        $server["REMOTE_ADDR"],
                                        $post["recaptcha_challenge_field"],
                                        $post["recaptcha_response_field"]);

	        if ($resp->is_valid)
				echo "You got it!";
			else {
				# set the error code so that we can display it
				$error = $resp->error;
	        }
		}
		
		return $error;
	}
}