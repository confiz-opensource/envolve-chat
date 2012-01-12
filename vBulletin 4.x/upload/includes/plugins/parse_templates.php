<?php
	require_once("envolve_util.php");
	
	//restrict these user groups for chat
	//don't show chat on restricted pages
	//don't show chat on mobile browsers
	if(envolve_do_restrict_usergroups() || envolve_do_restrict_on_pages() || envolve_is_wap())
		return;
	
	if(($vbulletin->options['envolve_api_key'] == NULL) || (strlen($vbulletin->options['envolve_api_key']) == 0)) 
	{ 
		//They haven't finished installation. Warn them. 
		$template_hook['headinclude_javascript'] .= "<div style=\"position: fixed; right: 0; bottom: 0;\"><a href=\"http://www.envolve.com/plugins/vbulletin-chat-plugin.html\"><img src=\"http://www.envolve.com/plugins/vBulletin/vbulletin_not_done.png\" /></a></div>"; 
	} 
	else
	{ 
		$env_userInf = $vbulletin->userinfo; 
		$env_isLoggedIn = $vbulletin->userinfo['userid'] > 0; 

		if($vbulletin->options['envolve_admin'] == "")
			$usergroups = "6,7"; //default admin groups by vBulletin
		else
			$usergroups = $vbulletin->options['envolve_admin'];

		$env_isAdmin = in_array($vbulletin->userinfo['usergroupid'], explode(",", $usergroups)); 

		//check if the user is logged in 
		if($env_isLoggedIn) 
		{ 
			$env_footer_code = envapi_get_html_for_reg_user($vbulletin->options['envolve_api_key'], html_entity_decode($env_userInf['username'], ENT_NOQUOTES, 'UTF-8'), NULL , envolve_get_picture(), $env_isAdmin, envolve_get_rollover_html($env_isAdmin)); 
		} 
		else  
		{ 
			$env_footer_code = envapi_get_code_for_anon_user($vbulletin->options['envolve_api_key']); 
		} 
		
		$template_hook['headinclude_javascript'] .= $env_footer_code; 
	} 

?>