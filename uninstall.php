<?php

//if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
        exit;
}

delete_option('bettergdpr_subdomain');
delete_option('bettergdpr_xtoken');
delete_option('bettergdpr_sitekey');

