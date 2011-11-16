<?php

/*
  Plugin Name: Viral Conversion Control
  Plugin URI: http://www.viralconversion.com
  Description:
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
        add_shortcode( 'vc_activation_key' , array($this,'shortcode') );
        register_activation_hook(__FILE__ , array($this,'create_table'));
    }
    
    function shortcode(){
       global $wpdb;
       $user=wp_get_current_user();
       $user_id= $user->ID;
       if(!$user_id)return 'Not Available';
      return $this->get_key($user->user_email);
        
    }
    
    function get_key($mail){
         global $wpdb;
         return $wpdb->get_var("select `key` from $this->table where email='$mail' ");        
    }
    
    function generate_keys(){
        global $wpdb;
        $mails = $wpdb->get_col("select user_email from $wpdb->users");
    
        $count=0;
        foreach($mails as $mail):
            if(!$this->exists_in_table($mail)){
                $key=uniqid(++$count,true);
                $d = array('email'=> $mail, 'key'=>$key );
                $wpdb->insert($this->table, $d);
            }
        endforeach;
        return $count;
    }
    
    function exists_in_table($mail){
        global $wpdb;
        return $wpdb->get_var("select `user_email` from $wpdb->users where user_email='$mail' ");       
    }
    
    

    function CreateMenu() {
        add_submenu_page('options-general.php', 'Viralconvertion Control', 'Viralconverstion Control', 'activate_plugins', 'wpViralControl', array($this, 'OptionsPage'));
    }

    function OptionsPage() {
        include 'options-page.php';
    }

    function check_if_validate() {
        if (isset($_GET['viralconversion_activate'])):
            if (!isset($_GET['mail']) && !isset($_GET['key']))
                die('Invalid mail or key');
            $mail = urldecode($_GET['mail']);
            $key = urldecode($_GET['key']);
            if (get_user_by('email', $mail)):
                if ($key == $this->get_key($mail)) {
                    header('Content-Type: text');
                    echo 'proceed';
                    exit;
                }else
                    echo 'key_invalid';

                exit;
                else:
                    echo 'mail_invalid';
                exit;
            endif;
        endif;
    }
    
    function create_table(){
        global $wpdb;
           $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}viral_mail_keys` (
		`id` int unsigned NOT NULL AUTO_INCREMENT, 
		`email` varchar(250)  NOT NULL,
		`key` varchar(250)  NOT NULL,		
		PRIMARY KEY (`id`),
		unique (`email`)  
		)";
      
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
    }

}