<?php
/*
Plugin Name: Workhard
Description: Workhard
Author: User
Version: 1.1
*/

include(dirname(__FILE__).'/wz_workhard_api.php');
include(dirname(__FILE__).'/wz_workhard_sql.php');

include(dirname(__FILE__).'/wz_workhard_dialog.php');
include(dirname(__FILE__).'/wz_workhard_articles.php');
include(dirname(__FILE__).'/wz_workhard_statistics.php');

include(dirname(__FILE__).'/wz_workhard_settings_token.php');
include(dirname(__FILE__).'/wz_workhard_settings_users.php');
include(dirname(__FILE__).'/wz_workhard_settings_google_spreadsheet.php');
include(dirname(__FILE__).'/wz_workhard_google_spreadsheet_users.php');
include(dirname(__FILE__).'/wz_workhard_additional_statistics_cron.php');
include(dirname(__FILE__).'/wz_workhard_additional_billing_cron.php');


include(dirname(__FILE__).'/wz_workhard_enqueue_scripts.php');

register_activation_hook(__FILE__, 'wz_create_table_folders');
register_deactivation_hook(__FILE__, 'wz_deactivate_workhard');

function wz_deactivate_workhard() {
    wz_clear_scheduled_additional_statistics('hourly');
    wz_clear_scheduled_additional_statistics('5seconds');
    wz_clear_scheduled_billing();
}

/**
 * Replace all occurrences of the search string with the replacement string.
 *
 * @author Sean Murphy <sean@iamseanmurphy.com>
 * @copyright Copyright 2012 Sean Murphy. All rights reserved.
 * @license http://creativecommons.org/publicdomain/zero/1.0/
 * @link http://php.net/manual/function.str-replace.php
 *
 * @param mixed $search
 * @param mixed $replace
 * @param mixed $subject
 * @param int $count
 * @return mixed
 */
if (!function_exists('wz_mb_str_replace')) {
	function wz_mb_str_replace($search, $replace, $subject, &$count = 0) {
		if (!is_array($subject)) {
			// Normalize $search and $replace so they are both arrays of the same length
			$searches = is_array($search) ? array_values($search) : array($search);
			$replacements = is_array($replace) ? array_values($replace) : array($replace);
			$replacements = array_pad($replacements, count($searches), '');
			foreach ($searches as $key => $search) {
				$parts = mb_split(preg_quote($search), $subject);
				$count += count($parts) - 1;
				$subject = implode($replacements[$key], $parts);
			}
		} else {
			// Call mb_str_replace for each subject in array, recursively
			foreach ($subject as $key => $value) {
				$subject[$key] = mb_str_replace($search, $replace, $value, $count);
			}
		}
		return $subject;
	}
}
