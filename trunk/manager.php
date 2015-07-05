<?php
global $vicomi_feelbacks_api;

if ( !function_exists('wp_nonce_field') ) {
    function wp_nonce_field() {}
}

// if reset request
if ( isset($_POST['reset']) ) {
	
	foreach ( array('vicomi_feelbacks_replace', 'vicomi_feelbacks_active', 'vicomi_feelbacks_api_key', 'vicomi_feelbacks_version') as $option ) {
		delete_option($option);
	}
    unset($_POST);

	?>
	<div class="wrap">
		<h2>Vicomi Reset</h2>
		<p>Vicomi has been reset successfully. You can <a href="?page=vicomi-feelbacks&amp;phase=1">reinstall</a> this plugin.</p>
	</div>
	<?php
	die();
}

// set if vicomi plugin is active
if (isset($_GET['active'])) {
    update_option('vicomi_feelbacks_active', ($_GET['active'] == '1' ? '1' : '0'));
}

// api key update
if ( isset($_POST['vc_api_key']) ) {
    $key = $_POST['vc_api_key'];
    $key = stripslashes($key);
    $key = strip_tags($key);

    if($key != null && $key != "") {

        update_option('vicomi_feelbacks_replace', 'all');
        update_option('vicomi_feelbacks_api_key', $key);
    }
}

// init vicomi api key
//$vicomi_api_key = isset($_POST['vicomi_api_key']) ? strip_tags($_POST['vicomi_api_key']) : null;


$login_url = 'http://cms.vicomi.com?platform=wordpress&wt=1&uid='.get_option('vicomi_feelbacks_uuid');
$moderation_url = 'http://dashboard.vicomi.com/';
//$login_url = 'http://localhost:9002?platform=wordpress&wt=1&uid='.get_option('vicomi_feelbacks_uuid');
//$moderation_url = 'http://localhost:9000/';

if (vicomi_feelbacks_is_installed()) {
    $current_url = $moderation_url;
} else {
    $current_url = $login_url;
}

?>
<div class="wrap">

	<div class="vicomi-feelbacks-header">
		<div class="vicomi-feelbacks-menu">
			<span rel="vicomi-feelbacks-page" class="selected"><?php echo (vicomi_feelbacks_is_installed() ? 'Dashboard' : 'Install'); ?></span>
            <?php if (vicomi_feelbacks_is_installed()) { ?>
                <span rel="vicomi-feelbacks-settings">Settings</span>
            <?php } ?>
		</div>
	</div>

    <div class="vicomi-feelbacks-content">

         <div class="vicomi-feelbacks-page">
            <iframe src="<?php echo $current_url ?>" style="width: 100%; height: 80%; min-height: 600px;"></iframe>
            <form method="POST" action="?page=vicomi-feelbacks" style="display:none;" name="vicomiForm" id="vicomiForm">
                <?php wp_nonce_field('vicomi-feelbacks-install-1'); ?>
            </form>
        </div>  

    </div>

    <!-- Settings -->
    <div class="vicomi-feelbacks-content vicomi-feelbacks-settings" style="display:none">
        <h2>Settings</h2>
        <p>Version: <?php echo VICOMI_FEELBACKS_V; ?></p>
        <?php
        if (get_option('vicomi_feelbacks_active') === '0') {
            echo '<p class="status">Vicomi feelbacks are currently disabled. (<a href="?page=vicomi-feelbacks&amp;active=1">Enable</a>)</p>';
        } else {
            echo '<p class="status">Vicomi feelbacks are currently enabled. (<a href="?page=vicomi-feelbacks&amp;active=0">Disable</a>)</p>';
        }
        ?>
        <form method="POST" enctype="multipart/form-data">
        <?php wp_nonce_field('vicomi-feelbacks-settings'); ?>

        <form action="?page=vicomi-feelbacks" method="POST">
			<?php wp_nonce_field('vicomi-feelbacks-reset'); ?>
			<input type="submit" value="Reset Vicomi" name="reset" onclick="return confirm('Are you sure you want to reset the Vicomi plugin?')" class="button" /> This removes all Vicomi settings.
		</form>

    </div>

</div>

<script>
/***********************************
 * Post Message
 **********************************/
 window.vcPostMessageService = new VCPostMessageService();

window.vcPostMessageService.listen(function(e) {

    var api_key_message_prefix = "vicomi:cms:apikey:";
    var finish_message_prefix = "vicomi:cms:finish";
    var api_key_and_finish_message_prefix = "vicomi:cms:apikeyfinish:";

    if(e.data.indexOf(api_key_message_prefix) > -1) {

        var apiKey = e.data.replace(api_key_message_prefix, "");
        updateApiKey(apiKey, false);
    }

    if(e.data.indexOf(finish_message_prefix) > -1) {

        reload();
    }

    if(e.data.indexOf(api_key_and_finish_message_prefix) > -1) {

        var apiKey = e.data.replace(api_key_and_finish_message_prefix, "");
        updateApiKey(apiKey, true);
    }

 });

function updateApiKey(apiKey, doReload) {

    // submit form
    jQuery.ajax({
        type: "POST",
        url: "?page=vicomi-feelbacks",
        data: {vc_api_key: apiKey},
        cache: false,
        success: function(result){
            if(doReload) {
                reload();
            }
        }
    });
}

function reload() {
    jQuery('#vicomiForm').submit();
}

function VCPostMessageService() {

    var _origin = "";
    var _listener;

    return {

        listen: function(listener) {
            _listener = listener;
            if (window.addEventListener) {
                window.addEventListener('message', this.postMessageListener);
            }
            else { // IE8 or earlier
                window.attachEvent('onmessage', this.postMessageListener);
            }

        },

        setOrigin: function(org) {
            _origin = org;
        },

        postMessage: function(msg, target) {
            if(_origin != null && _origin != "") {
                target.postMessage(msg, _origin);
            }

        },

        postMessageListener: function(e) {
            _listener(e);
        }
    }
}
</script>

