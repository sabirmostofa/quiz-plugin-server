<?php

/*
  Plugin Name: Viral Conversion Control
  Plugin URI: http://www.viralconversion.com
  Description: Generate or manage viralconversion keys
  Version: 2.1.0
  Author: Rob Jones
  Author URI: http://rob-jones.com
 */

$viral_control_object = new Viral_Control ();

class Viral_Control {

    function __construct() {
        global $wpdb;
        $this->table = "{$wpdb->prefix}viral_mail_keys";
        add_action('admin_menu', array($this, 'CreateMenu'), 50);
        add_action('plugins_loaded', array($this, 'check_if_validate'));
        add_action('user_register', array($this, 'generate_new_at_register'));
        add_action('delete_user', array($this, 'delete_from_table'));
        add_shortcode('vc_activation_key', array($this, 'key_shortcode'));
        add_shortcode('vc_activation_mail', array($this, 'mail_shortcode'));
        register_activation_hook(__FILE__, array($this, 'create_table'));
    }
    function  delete_from_table($id){
          global $wpdb;
        $user = get_user_by('id', $id);
        if(!$this->exists_in_table($user->user_email))return; 
        $mail = $user->user_email;
        $wpdb->query("delete from {$this->table} where email='$mail'");
        
    }
    function generate_new_at_register($id) {
        global $wpdb;
        $user = get_user_by('id', $id);
        if($this->exists_in_table($user->user_email))return;        
        $key = uniqid(rand(1,10), true);
        $mail = $user->user_email;
        $d = array('email' => $mail, 'key' => $key);
        $wpdb->insert($this->table, $d);
    }

    function key_shortcode() {
        global $wpdb;
        $user = wp_get_current_user();
        $user_id = $user->ID;
        if (!$user_id)
            return 'Not Available';
        return $this->get_key($user->user_email);
    }
    function mail_shortcode() {
        global $wpdb;
        $user = wp_get_current_user();
        $user_id = $user->ID;
        if (!$user_id)
            return 'Not Available';
        return $user->user_email;
    }

    function get_key($mail) {
        global $wpdb;
        return $wpdb->get_var("select `key` from $this->table where email='$mail'");
    }

    function generate_keys() {
        global $wpdb;
        $mails = $wpdb->get_col("select user_email from $wpdb->users");

        $count = 0;
        foreach ($mails as $mail):
            if (!$this->exists_in_table($mail)) {
                $key = uniqid(++$count, true);
                $d = array('email' => $mail, 'key' => $key);
                $wpdb->insert($this->table, $d);
            }
        endforeach;
        return $count;
    }

    function exists_in_table($mail) {
        global $wpdb;
        return $wpdb->get_var("select email from {$this->table} where email='$mail'");
    }

    function CreateMenu() {
        add_submenu_page('options-general.php', 'Viralconvertion Control', 'Viralconverstion Control', 'activate_plugins', 'wpViralControl', array($this, 'OptionsPage'));
    }

    function OptionsPage() {
        include 'options-page.php';
    }

    function check_if_validate() {
        global $wpdb;
        if (isset($_GET['viralconversion_activate'])):
            if (!isset($_GET['mail']) && !isset($_GET['key']))
                die('Invalid mail or key');
            $mail = urldecode($_GET['mail']);
            $key = urldecode($_GET['key']);
            if ($this->exists_in_table($mail)):
                if ($key == $this->get_key($mail)) {
                    header('Content-Type: text');
                    $act_count=$wpdb->get_var("select activate_count from {$this->table} where email='$mail'");
                    $limit_license= get_option('vc_license_limit')?  get_option('vc_license_limit'):0;
                    if($act_count <= $limit_license){
                        $wpdb->update($this->table, array('activate_count'=> ++$act_count), array('email'=>$mail) );
                          exit('proceed');                      
                    }
                  
                    else
                        exit('count_exceeds');
                    
                }else
                    echo 'key_invalid';

                exit;
            else:
                echo 'mail_invalid';
                exit;
            endif;
        endif;
    }
    
    function quiz_check_if_field_exists($table, $field) {
    $fields = mysql_list_fields(DB_NAME, $table);

    $columns = mysql_num_fields($fields);

    for ($i = 0; $i < $columns; $i++) {
        $field_array[] = mysql_field_name($fields, $i);
    }
    return in_array($field, $field_array);
}


    function create_table() {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}viral_mail_keys` (
		`id` int unsigned NOT NULL AUTO_INCREMENT, 
		`email` varchar(250)  NOT NULL,
		`key` varchar(250)  NOT NULL,
                                     `activate_count` int unsigned not_null default 0,
		PRIMARY KEY (`id`),
		unique (`email`)  
		)";
        if( !$this->quiz_check_if_field_exists($this->table, 'activate_count') ){
              $sql="ALTER TABLE `" . $this->table . "` ADD `activate_count` int unsigned NOT NULL DEFAULT 0;";
                $wpdb->query($sql);
        }

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

}
