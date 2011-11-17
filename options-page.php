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

//limit

if(isset($_REQUEST['limit-license'])):

 update_option('vc_license_limit', trim($_REQUEST['limit-license']));
    $message ="License limit updated";
endif;
 $message = isset($message)? $message:'';
 $limit_license= get_option('vc_license_limit')?  get_option('vc_license_limit'):0;
 
?>
<div class="wrap">
    <div id="generate-key" style="width:400px;margin:50px auto;text-align: center">
        <b>Shortcode Available:</b> [vc_activation_key] ,  [vc_activation_mail] ,[vc_activation_count] 
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
        
        <div id="field-form" style="width:500px;margin:50px auto;text-align: center">
            <form action="" method="post">
                <b>Set the Licence Limit:</b>
                <input type="text" name="limit-license" value="<?php echo $limit_license  ?>"/>
                <br/>
                <br/>
                <input class="button-primary" type='submit' name="submit-limit" value="Set/Change limit">
            </form>
        </div>
    </div>
</div>