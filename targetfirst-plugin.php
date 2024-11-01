<?php
/*
Plugin Name: Target First Plugin
Plugin URI: http://www.targetfirst.com
Description: A plugin that eases the integration of the <a href="http://www.targetfirst.com">targetfirst</a> live chat application. Please finish the installation/reactivation process by clicking on Target First in left menu .
Version: 1.0
Author: Target First
Author URI: http://support.targetfirst.com
*/

$wee_domain = 'watcheEasyWPPlugin';

// Runs on plugin deactivation
register_deactivation_hook( __FILE__, 'targetfirst_remove' );

$weeFN = '';
$weeLN = '';
$weePwd = '';
$weeEmail = '';
$weeUrl = '';
$weeWzKey = '';

if(isset($_POST['weeFN']) AND !empty($_POST['weeFN']))
  $weeFN = htmlspecialchars($_POST['weeFN']);
if(isset($_POST['weeLN']) AND !empty($_POST['weeLN']))
  $weeLN = htmlspecialchars($_POST['weeLN']);
if(isset($_POST['weePwd']) AND !empty($_POST['weePwd']))
  $weePwd = htmlspecialchars($_POST['weePwd']);
if(isset($_POST['weeEmail']) AND !empty($_POST['weeEmail']))
  $weeEmail = htmlspecialchars($_POST['weeEmail']);
if(isset($_POST['weeUrl']) AND !empty($_POST['weeUrl']))
  $weeUrl = htmlspecialchars($_POST['weeUrl']);
if(isset($_POST['weeWzKey']) AND !empty($_POST['weeWzKey']))
  $weeWzKey = htmlspecialchars($_POST['weeWzKey']);

if(!empty($weeFN) AND !empty($weeLN) AND !empty($weePwd) AND !empty($weeEmail))
{
  update_option('weeFN', $weeFN);
  update_option('weeLN', $weeLN);
  update_option('weeEmail', $weeEmail);
  update_option('weePwd', $weePwd);
  
  if(!get_option('weeID') OR strlen(get_option('weeID')) < 5)
  {
    //Generate key and activate Watcheezy
    $res = getRemote('https://www.watcheezy.net/public/get_remote_licence.php?cms=WP&key='.get_option('weeID').'&url='.$weeUrl.'&firstname='.get_option('weeFN').'&lastname='.get_option('weeLN').'&password='.get_option('weePwd').'&email='.get_option('weeEmail'));
    
    if(strlen($res) <= 5 )
    {
      update_option('weeError', $res); 
    }
    else
    {
      update_option('weeError', '');
      add_option('weeID', $res);
    }
  }
}

if(!empty($weeWzKey))
{
  update_option('weeID', $weeWzKey);
}

add_action('wp_footer', 'wee_insert');

function wee_insert()
{
  if(get_option('weeID'))
  {
    wp_enqueue_script( 'Target First', "https://www.watcheezy.net/deliver/targetfirst.js?wzkey=".get_option('weeID') );
  }
}

add_action('admin_notices', 'wee_admin_notice');

function wee_admin_notice()
{
  if(!get_option('weeID') OR strlen(get_option('weeID')) < 5) 
    echo('<div class="error"><p><strong>'.sprintf(__('Target First plugin is disabled. Please go to the <a href="%s">plugin page</a> and complete form to enable it.' ), admin_url('options-general.php?page=TargetFirst')).'</strong></p></div>');
}

function we_plugin_actions($links, $file)
{
  static $this_plugin;
  if(!$this_plugin) $this_plugin = plugin_basename(__FILE__);
  if($file == $this_plugin && function_exists('admin_url'))
  {
    $settings_link = '<a href="'.admin_url('options-general.php?page=TargetFirst').'">'.__('Settings', $wee_domain).'</a>';
    array_unshift($links, $settings_link);
  }
  return($links);
}

function getRemote($url, $port = 80)
{
  if (function_exists("curl_init"))
  {
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_PORT, $port);
    curl_setopt($curl_handle, CURLOPT_HEADER, FALSE);
    curl_setopt($curl_handle, CURLOPT_COOKIESESSION, TRUE);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl_handle, CURLOPT_POST, FALSE);
    $data = curl_exec($curl_handle);
    curl_close($curl_handle);
    
    if(!$data OR empty($data))
      $data = file_get_contents($url);
  }
  else
  {
    $data = file_get_contents($url);
  }

  return $data;
}

function targetfirst_admin_menu_styles()
{
  //global $watcheezy;
  wp_enqueue_style('targetfirst_admin_menu_styles', plugins_url() . '/watcheezy-wordpress-plugin/assets/css/menuw.css');
}

function targetfirst_remove()
{
  // Deletes the database field
  delete_option('weeId');
}

if ( is_admin() )
{
  add_action('admin_print_styles', 'targetfirst_admin_menu_styles');
  add_action('admin_menu', 'targetfirst_admin_menu');
  
  function targetfirst_admin_menu() 
  {
    $menu_Config= 'Settings';
    $lang_popup='us';
    
    add_menu_page(
    'TargetFirst', //$page_title
    'TargetFirst', //$menu_title
    'administrator', //$capability
    'TargetFirst', //$menu_slug
    'targetfirst_settings_page', //$function
    'https://www.targetfirst.com/plugins/images/logo36.png', //$icon_url
    55 // position
    );
  }
}
function targetfirst_settings_page()
{
  echo "<br><div>Thank you for using Target First</div>";
  global $wee_domain; 
  //Check if the website has already an old registration
  
  $res1="null";
  $hosturl = get_site_url();
  $res1 = getRemote('https://www.watcheezy.net/public/get_remote_licence.php?cms=WP&url='.$hosturl);
  
  if($res1 != "null")
    if(!get_option('weeID') OR strlen(get_option('weeID')) < 5)
      update_option('weeID', $res1);
  
  if(get_option('weeID') AND strlen(get_option('weeID')) > 5 )
  {
    echo '<div style="height:200px">&nbsp;</div>';
    echo '<div class="update-nag"><h2>Your licence is activated, you have nothing else to do<br>Start using Target First by logging into your backoffice (please check your email to get your IDs)</h2></div>';
  } 
  if(get_option('weeError'))
  {
    echo '<div style="height:200px">&nbsp;</div>';
    echo '<div class="update-nag"><h2>Error:'.get_option('weeError').'<br> Please <a href="http://www.targetfirst.com/societe/contact">Contact us</a></h2></div>';
  }
  if(!get_option('weeID') OR strlen(get_option('weeID')) < 5)
  {
?>
    <div class="wrap">
      <?php screen_icon(); ?>
      <h2><?php _e('Target First Plugin', $wee_domain) ?></h2>
      
      <form method="post" action="<?php echo admin_url('admin.php?page=targetfirst') ?>">
        <?php wp_nonce_field('update-options') ?>
        <p><?php _e('If you already have a Target First licence key, just put in here.', $wee_domain) ?></p>
        <p><label for="weeWzKey"><?php _e('Licence key', $wee_domain) ?></label><br />
        <input size="40" type="text" name="weeWzKey" id="weeWzKey" value="<?php echo $weeWzKey; ?>" required />
        <input type="submit" name="weeSubmit" id="idzSubmit" value="<?php _e('Check and start', $wee_domain) ?>" class="button-primary" /> 
        </p>
        <br>
        <p><?php _e("If you don't have any account, please visit our registration page.", $wee_domain) ?><a href='https://www.targetfirst.com/create-trial/' target='_blank'> here</a></p>
      </form>
    </div>
<?php 
  }
}
?>