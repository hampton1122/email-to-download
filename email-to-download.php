<?php
/**
 * Plugin Name:	Email To Download
 * Plugin URI:	https://github.com/hampton1122/email-to-download
 * Author:		Chris Hampton
 * Author URI:	https://github.com/hampton1122/email-to-download
 * Description:	Save name and email address before downloading file
 * Version:		1.0
 * License:		GPLv2
 */


global $etd_db_version;
$etd_db_version = '1.1';

register_activation_hook( __FILE__, 'etd_install' );


function etd_install()
{
    global $wpdb;

    $table_name = $wpdb->prefix."etd_subscribers";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
    {
        $results = etd_auth_create_table();
        if(!$results)
        {
            echo "<p>Opps! We were not able to create the login logging table.</p>";
        }
    }
}


function etd_update_db()
{
    // global $wpdb;

    // $table_name = $wpdb->prefix."etd_subscribers";
    // $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table_name."' AND column_name = 'login_requestor'"  );

    // if(empty($row)){
    //     $wpdb->query("ALTER TABLE ".$table_name." ADD login_requestor varchar(100) DEFAULT 'false' NOT NULL");
    // }
}


function etd_auth_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix."etd_subscribers";
    $charset_collate = $wpdb->get_charset_collate();

    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
    id int(11) NOT NULL AUTO_INCREMENT,
    time timestamp DEFAULT CURRENT_TIMESTAMP' ON UPDATE CURRENT_TIMESTAMP,
    first_name varchar(255) NULL DEFAULT NOT NULL,
    last_name varchar(100) DEFAULT DEFAULT NOT NULL,
    email varchar(100) DEFAULT DEFAULT NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    try
    {
        dbDelta( $sql );
        return true;
    }
    catch(Exception $e)
    {
        return false;
    }
}

function etd_update_db_check() {
    global $etd_db_version;
    if ( get_site_option( 'etd_db_version' ) != $etd_db_version ) {
        etd_update_db();
    }
}
add_action( 'plugins_loaded', 'etd_update_db_check' );
