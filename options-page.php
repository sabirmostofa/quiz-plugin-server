<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
global $wpdb;
if(isset($_REQUEST['submit'])):
 if(!wp_verify_nonce($_POST['generate_key'],'quiz_action_generate_key')) die("Nonce verification failed");
 
    $num= $this ->generate_keys();
    $message ="$num key(s) were generated successfully";
endif;
 $message = isset($message)? $message:'';
 
?>
<div class="wrap">
    <div id="generate-key" style="width:400px;margin:50px auto;text-align: center">
        <b>Shortcode Available:</b> [vc_activation_key]
        <br/>
        <br/>
        <h2>Generate activation keys for all users</h2>
        <b>All keys will be saved in the <?php echo "{$wpdb-> prefix}viral_mail_keys"; ?> Table</b>
    <form action="" method="post">
        <div class="updated"><?php echo $message ?></div> 
       


    <br/>
    <input class="button-primary" type="submit" name="submit" value="Generate Keys"/>
    <?php wp_nonce_field('quiz_action_generate_key','generate_key'); ?>
    </form>
    </div>
</div>