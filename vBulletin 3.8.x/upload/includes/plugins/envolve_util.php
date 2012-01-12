<?php

	require_once('./global.php');
	require_once(DIR . '/includes/class_postbit.php');
	require_once(DIR . '/includes/functions_user.php');
	require_once(DIR . '/includes/class_userprofile.php');
	require_once("envolve_api_client.php");

	/**
	 * Fetches the URL for a User's Avatar
	 * @return	path for avatar
	 */
	function envolve_fetch_avatar_url()
	{
		try
		{
			global $vbulletin;
			$avatarurl = fetch_avatar_url($vbulletin->userinfo['userid']);
			if($avatarurl)
			{
				return $vbulletin->options['bburl'] ."/". $avatarurl[0];
			}
			else
				return "";
		
		} catch (Exception $e) {
			return "";
		}
	}
	
	/**
	 * Fetches the URL for a User's Profile Picture
	 * @return	path for profile picture
	 */
	function envolve_prepare_profilepic()
	{
		try
		{
			global $vbulletin;
			
			$fetch_userinfo_options = (
				FETCH_USERINFO_AVATAR | FETCH_USERINFO_LOCATION |
				FETCH_USERINFO_PROFILEPIC | FETCH_USERINFO_SIGPIC |
				FETCH_USERINFO_USERCSS | FETCH_USERINFO_ISFRIEND
			);
			
			$userinfo = verify_id('user', $vbulletin->userinfo['userid'], 1, 1, $fetch_userinfo_options);
			
			$profileobj = new vB_UserProfile($vbulletin, $userinfo);
			$prepared =& $profileobj->prepared;
			
			if($prepared['profilepicurl'])
			{
				return $vbulletin->options['bburl'] ."/". $prepared['profilepicurl'];
			}
			else
				return "";
		} catch (Exception $e) {
			return "";
		}
	}
	
	/**
	 * Fetches the URL for a User's picture, either avatar or profile picture 
	 * depending on options
	 * @return	path for picture
	 */
	function envolve_get_picture()
	{
		global $vbulletin;
		if($vbulletin->options['envolve_picture_for'] == "avatar")
			$picture_path = envolve_fetch_avatar_url();
		else if($vbulletin->options['envolve_picture_for'] == "profile_picture")
			$picture_path = envolve_prepare_profilepic();
		else
			$picture_path = "";
		return $picture_path;
	}
	
	/**
	 * Fetches restricted pages list where you don't want to show chat
	 * @return	True/False
	 */
	function envolve_do_restrict_on_pages()
	{
		global $vbulletin;
		$filename = explode("?", basename($_SERVER["REQUEST_URI"]));
		return in_array($filename[0], explode(",", $vbulletin->options['envolve_hide_chat_on_pages']));
	}
	
	/**
	 * Fetches mobile browser information.
	 * @return	True/False
	 */
	function envolve_is_wap()
	{
		global $vbulletin;
		$mobile_browsers = array("android", "ipod", "opera mini", "blackberry", "palm","hiptop","avantgo","plucker", "xiino","blazer","elaine", "windows ce; ppc;", "windows ce; smartphone;","windows ce; iemobile", "up.browser","up.link","mmp","symbian","smartphone", "midp","wap","vodafone","o2","pocket","kindle", "mobile","pda","psp","treo");
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$is_wap = false;
		foreach ($mobile_browsers as $value) {
			if(preg_match("/".$value."/im", $agent))
			{
				$is_wap = true;
				break;
			}
		}
		if($is_wap && !$vbulletin->options['envolve_mobile_version'])
			return true;
		return false;
	}

	/**
	 * Fetches user group information to restrict user's for chat
	 * @return	True/False
	 */
	function envolve_do_restrict_usergroups()
	{
		global $vbulletin;
		return in_array($vbulletin->userinfo['usergroupid'], explode(",", $vbulletin->options['envolve_display_to'])); 
	}
	
	/**
	 * Fetches rollover HTML for a user
	 * @return	HTML
	 */
	function envolve_get_rollover_html($env_isAdmin)
	{
		global $vbulletin;
		$profile_picture = envolve_get_picture();
		$user_profile_url = envolve_get_user_profile($vbulletin);
		$color = "#666";
		if($env_isAdmin)
			$color = "red";
		if($profile_picture == "")
			$profile_picture = $vbulletin->options['bburl']. "/images/avatars/user_pic.png";
		$picture = "<div style='border: 1px solid #CCCCCC;float: left;height: 60px;margin: 12px 7px 0px; width: 60px;'>
						<div style='float: left;height: 50px;margin: 2px 0px 0px 4px; width: 50px;'>
							<a href='".$user_profile_url."' target='_blank'>
								<img width='40' src='".$profile_picture."'>
							</a>
						</div>
					</div>";
		$html = "<div style='position:relative; width:290px; margin:0px auto; overflow:hidden; text-align: left; padding-bottom: 8px;'>
				".$picture."
				<div style='float:left;'>
						<p style='font-family: arial;font-weight:bold;font-size:12px;line-height: 26px;margin: 7px 7px 0px;'>
						".$vbulletin->userinfo['username']." 
						<span style='color: ".$color.";'> (".$vbulletin->userinfo['usertitle'].") </span>
						<br /> 
					<div><span style='margin:0px 10px; list-style:none; float:left;'>
						<a href='".$user_profile_url."' target='_blank' style='text-decoration: none; color:#0000ff;' > View Profile </a>
						</span></div> <br />
						<div style='text-align: left;'>
							<span style='font-family: arial;font-weight:bold;font-size:12px; margin: 0px 0px 0px 10px; color:#777777;'>Posts : 
							<span style='font-weight: normal;'>&nbsp;".$vbulletin->userinfo['posts']."</span> </span> <br />
							<span style='font-family: arial;font-weight:bold;font-size:12px; margin: 0px 0px 0px 10px; color:#777777;'>Join Date : 
							<span style='font-weight: normal;'>".vbdate($vbulletin->options['dateformat'], $vbulletin->userinfo['joindate'], 0)."</span></span> <br />
							<span style='font-family: arial;font-weight:bold;font-size:12px; margin: 0px 0px 0px 10px; color:#777777;'>Occupation: 
							<span style='font-weight: normal;'> ".$vbulletin->userinfo['field4']." </span></span> <br />
						</div>	
						</p>
					</div>
				</div>";
		return $html;
	}
	
	/**
	 * Fetche user profile link
	 * @return	link
	 */
	function envolve_get_user_profile($vbulletin)
	{
		return $vbulletin->options['bburl'] ."/member.php?u=". $vbulletin->userinfo['userid'];
		
	}
	
?>