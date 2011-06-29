<?php
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'AND_REPORT' => 'and report the user to SFS',

	'CANT_BAN_FOUNDER' => 'You can not ban a founder',

	'N/A'				=> 'N/A',
	'NOT'				=> '<strong>Not</strong>',

	'OCBAN_COMPLETE'	=> 'User successfully banned%1$s.<br /><a href="%2$s">Click here to return to the user’s profile.</a>',
	'OCBAN_CONFIRM'		=> 'Are you sure you want to one click ban %1$s?<br /><br />This will:<br />
%2$s ban this user’s username<br />
move the user to group %3$s<br />
%4$s delete the user’s posts<br />
%5$s delete the user’s avatar<br />
%6$s delete the user’s signature<br />
%7$s delete the user’s profile fields<br />
%8$s',

	'SFS_SUCCESSFUL' => ' and reported to StopForumSpam',
	'SFS_UNSUCCESSFUL' => ' but there where some error reporting to StopForumSpam. The error message from SFS can be found below<br /><br />',

	'USER_OCBAN'		=> 'OC Ban',
));

?>