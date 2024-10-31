<?php
/*
	Plugin Name: Onswipe Link
	Plugin URI: http://onswipe.com/setup
	Description: Onswipe Link connects your WordPress installation to Onswipe.
	Author: Onswipe
	Version: 1.1
	Author URI: http://onswipe.com
*/


/**
 * global constant for the plugin directory
 */
define( 'PADPRESS_PLUGIN_DIR', dirname(__FILE__) );

/**
 * global constant for the framework directory
 */
define( 'PADPRESS_FRAMEWORK_DIR', PADPRESS_PLUGIN_DIR . '/framework' );

/*
	this are utility functions
*/
require_once( PADPRESS_FRAMEWORK_DIR . '/functions.php' );
require_once( PADPRESS_FRAMEWORK_DIR . '/api.php' );

/**
 * global constant that points to the url where the plugin lives.
 * we should guess if it's inside mu-plugins
 */
if ( str_contains( 'mu-plugins', __FILE__ ) )
	define( 'PADPRESS_PLUGIN_URL', content_url() . '/mu-plugins/' . wp_basename( dirname(__FILE__) ) );
else
	define( 'PADPRESS_PLUGIN_URL', plugins_url() . '/' . wp_basename( dirname(__FILE__) ) );

class OnswipeLink {
    function __construct() {
        add_filter( 'admin_menu', array( $this, 'createMenu' ) );
        add_action( 'admin_init', array( $this, 'initOptions') );
        add_action( 'wp_head', array( $this, 'addSynapse') );
    }

    function initOptions() {
        register_setting('onswipe_link_options', 'onswipe_link');
    }

    function addSynapse() {
        $options = get_option('onswipe_link');
        if (isset($options['onswipe_username'])) {
            if($options['onswipe_username'] != '' && $options['onswipe_username'] != NULL) {
                echo '<script type="text/javascript" src="http://cdn.onswipe.com/synapse/on.js?usr=' . $options['onswipe_username'] . '" id="onswipe_synapse"></script>' . "\n";
            }
        }
    }

	/**
	 * Creates a Top Level admin menu for this plugin
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function createMenu() {
		global $menu;
		add_menu_page( 'Onswipe Link', 'Onswipe Link', 'manage_options', 'onswipelink', array( $this, 'panel' ), plugin_dir_url( __FILE__ ) . '/images/logo.png' );
	}

	/**
	 * Renders the plugin options page. Pretty simple stuff right now.
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function panel() {
        // use jQuery
        wp_enqueue_script( 'jquery' );

        // get options
        $options = get_option('onswipe_link');

        // set iframe URL
        $iframe_url = 'http://dashboard.onswipe.com/';
        $new_user = false;

        // new user changes to the iframe_url
        if ($options['onswipe_username'] == '' || $options['onswipe_username'] == NULL) { 
            $iframe_url .= 'signup';
            $iframe_url .= '?url=' . urlencode(get_site_url());
            $iframe_url .= '&wordpress=1';
            $new_user = true;
        }
?>
<style type="text/css">
#dashFrame {
    width: 100%;
    height: 760px;
}

#dashPane {
    <?php if ($new_user) { ?>
    display: none;
    <?php } ?>
}

#dashOpener {
    <?php if (!$new_user) { ?>
    display: none;
    <?php } ?>
}

#dashOpener {
    font-size: 12pt;
}

#dashOpener #dashOpenerLink {
    text-decoration: none;
}

#dashOpener #dashOpenerLink #arr {
    font-size: 11pt;
}
</style>

<script type="text/javascript">
// document ready handler: recalc dash frame on page load
jQuery(document).ready(function () {
    recalculateDashFrame();
    prepDashOpenerLink();
});

// window resize handler: recalc dash frame on window resize
jQuery(window).resize(function() {
    recalculateDashFrame();
});

// recalculate the height of the dashFrame to make it fit the height of the window
function recalculateDashFrame() {
    jQuery('#dashFrame').css('height', (jQuery(window).height() - 300) + 'px');
}

// event listener for username
function dashUsernameListener(m) {
    // ensure correct data
    if (m.data && m.data.indexOf('username: ') > -1) {
        // put it in the username value and submit the form
        var username = m.data.replace('username: ', '');
        jQuery('#username').val(username);
        jQuery('#onswipeLinkForm').submit();
    }
}

// attach event listener
window.addEventListener("message", dashUsernameListener, false);

// attach click event to dashOpenerLink();
function prepDashOpenerLink() {
    jQuery('#dashOpenerLink').click(function () {
        jQuery('#dashPane').show();
        jQuery('#dashOpener').hide();
        recalculateDashFrame();
    });
}
</script>

<div class="wrap">
    <div class="metabox-holder">
        <form method="post" action="options.php" id="onswipeLinkForm">
            <?php settings_fields('onswipe_link_options'); ?>

            <h2>Onswipe Link <span style="font-size: 11pt;">Connect your WordPress site to Onswipe.</span></h2>

            <div class="postbox" style="width: 455px; margin-top: 20px;">
                <div class-"inside">
                    <table class="form-table" width="450" style="width: 450px; margin-top: 0;">
                        <tr>
                            <th scope="row" style="vertical-align: middle;" width="130" style="width: 130px;">Onswipe username</th>
                            <td><input type="text" name="onswipe_link[onswipe_username]" value="<?php echo $options['onswipe_username']; ?>" id="username" /></td>
                            <td><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div style="margin-top: 30px;">
                <div id="dashOpener"><a href="#" id="dashOpenerLink"><span id="arr">&#9654;</span> Not an Onswipe user? Click here for accelerated signup.</a></div>
                <div id="dashPane">
                    <iframe src="<?php echo $iframe_url; ?>" id="dashFrame"></iframe>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
	}
}

new OnswipeLink;
