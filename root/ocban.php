<?php
/*
* START CONFIG
*/

// Ban the username?
$ban_username = true;

/* Set to the group ID if you want to move a user to a certain group.  This will set that group as default and remove the user from any other groups */
$move_to_group = 0;

// Delete the user's posts?
$delete_posts = true;

// Delete the user's avatar?
$delete_avatar = true;

// Delete the user's signature?
$delete_signature = true;

// Delete profile fields (website, IM addresses, location, etc)
$delete_profile_fields = true;

// If you want to report spammers automatically to StopForumSpam you need a API key.
// http://www.stopforumspam.com/apikey
$sfs_api_key = '';

/*
* END CONFIG
*/

define('IN_PHPBB', true);
define('ADMIN_START', true);
define('NEED_SID', true);

// Include files
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/functions_user.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('acp/common', 'mods/ocban'));
// End session management

// Do this user have ban permissions.
if (!$auth->acl_get('a_ban') && !$auth->acl_get('m_ban'))
{
	trigger_error('CANT_USE_BAN');
}

$user_id = request_var('u', 0);

if (!$user_id)
{
	trigger_error('NO_USER');
}

$sql = 'SELECT * FROM ' . USERS_TABLE . '
	WHERE user_id = ' . $user_id;
$result = $db->sql_query_limit($sql, 1);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$row)
{
	trigger_error('NO_USER');
}

$username = $row['username'];
$user_ip = $row['user_ip'];
$user_email = $row['user_email'];

if ($row['user_type'] == USER_FOUNDER)
{
	trigger_error('CANT_BAN_FOUNDER');
}

// If the user selected cancel, redirect to the profile.
if (isset($_POST['cancel']))
{
	redirect(append_sid($phpbb_root_path . 'memberlist.' . $phpEx, 'mode=viewprofile&amp;u=' . $user_id));
	// exit is not really needed here.
	exit;
}

if (confirm_box(true))
{
	if ($ban_username)
	{
		user_ban('user', $username, 0, '', 0, '');
	}

	if ($delete_posts)
	{
		delete_posts('poster_id', $user_id);
	}

	if ($delete_avatar && !empty($row['user_avatar']))
	{
		avatar_delete('user', $row, true);
	}

	if ($delete_signature)
	{
		$db->sql_query('UPDATE ' . USERS_TABLE . ' SET ' .
			$db->sql_build_array('UPDATE', array(
				'user_sig' => '',
				'user_sig_bbcode_uid' => '',
				'user_sig_bbcode_bitfield' => ''
			)) . '
			WHERE user_id = ' . $user_id);
	}

	if ($delete_profile_fields)
	{
		$sql_ary = array(
			'user_birthday' 	=> '',
			'user_from'			=> '',
			'user_icq'			=> '',
			'user_aim'			=> '',
			'user_yim'			=> '',
			'user_msnm'			=> '',
			'user_jabber'		=> '',
			'user_website'		=> '',
			'user_occ'			=> '',
			'user_interests'	=> '',
		);
		$db->sql_query('UPDATE ' . USERS_TABLE . ' SET ' .
			$db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE user_id = ' . $user_id);

		// Also delete all extra profile fields for that user.
		$db->sql_query('DELETE FROM ' . PROFILE_FIELDS_DATA_TABLE . '
			WHERE user_id = ' . $user_id);
	}

	if ($move_to_group)
	{
		$sql = 'SELECT group_id FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . $user_id;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			group_user_del($row['group_id'], array($user_id), array($username));
		}
		$db->sql_freeresult($result);

		group_user_add($move_to_group, array($user_id), array($username), false, true);
	}

	// Report spammer to SFS
	if (!empty($sfs_api_key))
	{
		$http_request = 'http://www.stopforumspam.com/add.php';
		$http_request .= '?username=' . $username;
		$http_request .= '&ip_addr=' . $user_ip;
		$http_request .= '&email=' . $user_email;
		$http_request .= '&api_key=' . $sfs_api_key;

		$response = file_get_contents($http_request);

		if (strpos($response, 'Data submitted successfully') !== false)
		{
			// Successful
			$message = $user->lang['SFS_SUCCESSFUL'];
		}
		else
		{
			$error_div = strpos($response, '<div class="msg error">') + 23;
			$error_end = strpos($response, '</div>', $error_div);
			$error_p = strpos($response, '<p>', $error_end) + 3;
			$error_p_end = strpos($response, '</p>', $error_p);

			$error = htmlspecialchars(substr($response, $error_div, ($error_end - $error_div))) . '<br />';
			$error .= htmlspecialchars(substr($response, $error_p, ($error_p_end - $error_p))) . '<br />';

			$message = $user->lang['SFS_UNSUCCESSFUL'] . $error;
		}
	}
	else
	{
		$message = '';
	}

	trigger_error(sprintf($user->lang['OCBAN_COMPLETE'], $message, append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=viewprofile&amp;u=$user_id")));
}
else
{
	confirm_box(false, sprintf($user->lang['OCBAN_CONFIRM'],
			$username,
			($ban_username) ? '' : $user->lang['NOT'],
			($move_to_group) ? $move_to_group : $user->lang['N/A'],
			($delete_posts) ? '' : $user->lang['NOT'],
			($delete_avatar) ? '' : $user->lang['NOT'],
			($delete_signature) ? '' : $user->lang['NOT'],
			($delete_profile_fields) ? '' : $user->lang['NOT'],
			(empty($sfs_api_key)) ? '' : $user->lang['AND_REPORT']
	));
}

?>