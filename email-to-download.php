<?php
/**
 * Plugin Name:	Email To Download for Parallel Financial
 * Plugin URI:	https://github.com/hampton1122/email-to-download
 * Author:		Chris Hampton
 * Author URI:	https://github.com/hampton1122/email-to-download
 * Description:	Save name and email address before downloading file
 * Version:		1.1
 * License:		GPLv2
 */

require_once(ABSPATH . 'wp-load.php');


global $etd_db_version, $wpdb;
$etd_db_version = '1.1';

register_activation_hook( __FILE__, 'etd_install' );

// Our custom post type function
function create_posttype() {

	register_post_type( 'email-to-download',
	// CPT Options
		array(
			'labels' => array(
				'name' => __( 'ETDs' ),
				'singular_name' => __( 'ETD' )
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'email-to-download'),
		)
	);
}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );

function custom_post_type_boxes(){
    remove_meta_box( 'postexcerpt', 'email-to-download', 'normal' );
    add_meta_box( 'postexcerpt', __( 'Email Subject' ), 'post_excerpt_meta_box', 'email-to-download', 'normal', 'high' );
}
add_action('do_meta_boxes', 'custom_post_type_boxes');


add_action('admin_menu', 'etd_add_pages');
function etd_add_pages(){
    add_submenu_page('edit.php?post_type=email-to-download', __('Downloaders','menu-etd'), __('Downloaders','menu-etd'), 'manage_options', 'downloaders', 'email_to_download_menu_content');
}

function etd_install()
{
    global $wpdb;

    $table_name = $wpdb->prefix."etd_subscribers";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
    {
        $results = etd_auth_create_table();
        if(!$results)
        {
            echo "<p>Opps! We were not able to create the plugin table.</p>";
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

    return;
}


function etd_auth_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix."etd_subscribers";
    $settings_table = $wpdb->prefix."etd_settings";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
    id int(11) NOT NULL AUTO_INCREMENT,
    time_downloaded TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    first_name varchar(255) NOT NULL,
    last_name varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    UNIQUE KEY id (id)
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


function etd_css_and_js() {
    wp_register_style('etd-css', plugins_url('css/etd-css.css',__FILE__ ));
    wp_enqueue_style('etd-css');

    wp_register_script( 'etd-js', plugins_url('js/etd-js.js',__FILE__ ), array('jquery', 'jquery-effects-core', 'jquery-ui-core'));
    wp_enqueue_script('etd-js');
    wp_localize_script( 'etd-js', 'etdAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

    wp_enqueue_script('jquery-ui', '//code.jquery.com/ui/1.12.1/jquery-ui.js', false, '1.8.8');

    wp_register_style('jqueryuistyle', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', 3.3,true);
    wp_enqueue_style('jqueryuistyle');

}
add_action( 'wp_enqueue_scripts','etd_css_and_js');


add_action('wp_ajax_saveEmail', 'saveEmail' );
add_action('wp_ajax_nopriv_saveEmail', 'saveEmail');
function saveEmail() {
	global $wpdb;

    $table_name = $wpdb->prefix."etd_subscribers";

	$first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email_address = $_POST['email_address'];
    $slug = $_POST['slug'];

    //get email content from custom post type
    $emailPost = get_page_by_path( $slug, OBJECT, 'email-to-download' );


    if($_POST['first_name'] > '' && $_POST['last_name'] > '' && $_POST['email_address'] > '')
    {
        //add record
        $emailExists = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE email ='".$email_address."'");

        if(count($emailExists) == 0){
            $wpdb->insert($table_name, array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email_address,
            ));
        }


        //create email
        $admin_email = get_option('admin_email');
        $admin_name = "Parallel Financial";

        //get email content from custom post type
        $emailPost = get_page_by_path( $slug, OBJECT, 'email-to-download' );

        $headers = array('From: '.$admin_name.' <'.$admin_email.'>');

        $array = array('[first_name]' => $first_name, '[last_name]' => $last_name);
        $message = str_replace(array_keys($array), array_values($array), $emailPost->post_content);

        add_filter( 'wp_mail_content_type', 'set_html_content_type' );
        $emailStatus = wp_mail($email_address, $emailPost->post_excerpt, $message, $headers);
        remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

        $array = array('status' => 'success','email' => $emailStatus, 'first_name'=>$first_name, 'last_name' => $last_name, 'email_address' => $email_address);
    } else
    {
        $array = array('status' => 'error','email' => false, 'first_name'=>$first_name, 'last_name' => $last_name, 'email_address' => $email_address);
    }
    echo json_encode($array);

	wp_die(); // this is required to terminate immediately and return a proper response
}

function set_html_content_type() {
	return 'text/html';
}


function email_to_download_menu_content() {
    global $wpdb;
    $plugin_url = plugin_dir_url( __FILE__ );


    echo "<h2> Email to Download</h2>";
    echo "<p>Below is a list of people who have downloaded the free eBook.</p>";
    $table_name = $wpdb->prefix."etd_subscribers";
    $results = $wpdb->get_results("SELECT * FROM ".$table_name);

    if(count($results) > 0)
    {
        foreach($results as $result)
        {
            echo "<li>".$result->first_name." ".$result->last_name." <a href='mailto:".$result->email."'>".$result->email."</a></li>";
        }
    } else {
        echo "<p>Sorry, there are no downloads yet.</p>";
    }
}