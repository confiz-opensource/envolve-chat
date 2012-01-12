<?php

/**
* Envolve API Client Library
*
* @version 0.1
*/

/**
 * This method creates the full HTML that should be included in a page for an anonymous user.
 * @param string $apiKey Your site's Envolve API Key
 * @return string The HTML to include in the host page to activate Envolve.
 */
function envapi_get_code_for_anon_user($apiKey)
{
	return envapi_get_html($apiKey, envapi_get_logout_command($apiKey));
}

/**
 * This method creates the full HTML that should be included in a page for a logged-in user.
 * @param string apiKey Your site's Envolve API Key
 * @param string firstName The first name or username for the user. (required)
 * @param string lastName The last name of the user. Pass null if unused.
 * @param string picture An absolute URL to the location of the user's profile picture.
 * @param boolean isAdmin Is this user an administrator?
 * @param string profileHTML The html to be included in the user's profile rollover.
 * @return string The HTML to include in the host page to activate Envolve.
 */
function envapi_get_html_for_reg_user($apiKey, $firstName, $lastName, $picture, $isAdmin, $profileHTML)
{
	$command = envapi_get_login_command($apiKey, $firstName, $lastName, $picture, $isAdmin, $profileHTML);
	return envapi_get_html($apiKey, $command);
}

function envapi_get_html($apiKey, $command)
{
	$envolve_js_root = "d.envolve.com/env.nocache.js";
	//first, lets validate the args.
	$apiKeyPieces = preg_split('/-/', $apiKey);
	if((count($apiKeyPieces) != 2) || (((int) $apiKeyPieces[0]) == 0) )
	{
		error_log("EnvolveAPI: Invalid API Key");
		return;
	}
	$siteID = (int) $apiKeyPieces[0];
	
	$retVal = '<script type="text/javascript">' . "\n" . 'var envoSn=' . $siteID . ";\n";
	if($command != NULL)
	{
		$retVal = $retVal . 'var env_commandString="' . $command . '";' . "\n";
	}
    $retVal = $retVal . 'var envProtoType = (("https:" == document.location.protocol) ? "https://" : "http://");' . "\n";
    $retVal = $retVal . 'document.write(unescape("%3Cscript src=\'" + envProtoType + "' . $envolve_js_root . '\' type=\'text/javascript\'%3E%3C/script%3E"));';
    $retVal = $retVal . '</script>';
	return $retVal;
}

/**
 * This method creates a login command string that can be passed to Envolve in order to
 * programmatically log a user in. Use this directly if you intend to use the Envolve JS API. Otherwise
 * you should use one of the functions above.
 * @param string apiKey Your site's Envolve API Key
 * @param string firstName The first name or username for the user. (required)
 * @param string lastName The last name of the user. Pass null if unused.
 * @param string picture An absolute URL to the location of the user's profile picture.
 * @param boolean isAdmin Is this user an administrator?
 * @param string profileHTML The html to be included in the user's profile rollover.
 * @return string The command string to pass to Envolve
 */
function envapi_get_login_command($apiKey, $firstName, $lastName, $picture, $isAdmin, $profileHTML)
{
	$commandString =  "v=0.2,c=login,fn=" . base64_encode($firstName);
	if($firstName == NULL)
	{
		error_log("EnvolveAPI: You must provide a first name to log in to Envolve");
		return;
	}
	if($lastName != null)
	{
		$commandString = $commandString . ",ln=" . base64_encode($lastName);
	}
	if($picture != null)
	{
		$commandString = $commandString . ",pic=" . base64_encode($picture);
	}
	if($isAdmin)
	{
		$commandString = $commandString . ",admin=t";
	}
	if($profileHTML != null)
	{
		$commandString = $commandString . ",prof=" . base64_encode($profileHTML);
	}
	return envapi_sign_command($apiKey, $commandString);	
}

/**
 * This method creates a logout command that tells Envolve to replace the current logged in user
 * with an anonymous (generated) username.
 * you should use one of the functions above.
 * @param string apiKey Your site's Envolve API Key
 * @return string The command string to pass to Envolve
 */
function envapi_get_logout_command($apiKey)
{
	return envapi_sign_command($apiKey, 'c=logout');	
}

function envapi_sign_command($apiKey, $command)
{
	//validate the args
	$apiKeyPieces = preg_split('/-/', $apiKey);
	if((count($apiKeyPieces) != 2) || (((int) $apiKeyPieces[0]) == 0) )
	{
		error_log("EnvolveAPI: Invalid API Key");
		return;
	}
	$secretKey = $apiKeyPieces[1];
	
	$data =  time() . '000' . ';' . $command;
	$hash = my_hash_hmac($secretKey, utf8_encode($data));
	return $hash . ";" . $data;
}

//php4 safe version of sha1_hash_hmac
function my_hash_hmac ($key, $data)
{
	$b = 64;
	if (strlen($key) > $b)
	{
		$key = pack("H*",sha1($key));
	}
	$key = str_pad($key, $b, chr(0x00));
	$ipad = str_pad('', $b, chr(0x36));
	$opad = str_pad('', $b, chr(0x5c));
	$k_ipad = $key ^ $ipad ;
	$k_opad = $key ^ $opad;
	return sha1($k_opad . pack("H*",sha1($k_ipad . $data)));
} 

?>
