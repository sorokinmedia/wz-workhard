<?php

if(!defined('WP_UNINSTALL_PLUGIN')){
    exit;
}

wz_clear_scheduled_additional_statistics('hourly');
wz_clear_scheduled_additional_statistics('5seconds');
wz_clear_scheduled_billing();

?>