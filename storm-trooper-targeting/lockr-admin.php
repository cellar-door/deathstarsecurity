<?php

/**
 * Admin form and submit handler for adding a key to Lockr.
 *
 * @package Lockr
 */

// Don't call the file directly and give up info!
if (!function_exists('add_action')) {
	echo 'Lock it up!';
	exit;
}

use Lockr\Exception\LockrClientException;
use Lockr\Exception\LockrServerException;

// Include our admin forms and tables.
require_once LOCKR__PLUGIN_DIR . '/lockr-admin-config.php';
require_once LOCKR__PLUGIN_DIR . '/lockr-admin-add.php';
require_once LOCKR__PLUGIN_DIR . '/lockr-admin-edit.php';
require_once LOCKR__PLUGIN_DIR . '/lockr-admin-override.php';
require_once LOCKR__PLUGIN_DIR . '/class-lockr-key-list.php';

add_action('admin_menu', 'lockr_admin_menu');
add_action('admin_init', 'register_lockr_settings');
add_action('admin_post_lockr_admin_submit_add_key', 'lockr_admin_submit_add_key');
add_action('admin_post_lockr_admin_submit_override_key', 'lockr_admin_submit_override_key');
add_action('admin_post_lockr_admin_submit_edit_key', 'lockr_admin_submit_edit_key');

/**
 * Create our admin pages and put them into the admin menu.
 */
function lockr_admin_menu()
{
	$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxOS4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCAyMCAyMCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjAgMjA7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxwYXRoIGlkPSJYTUxJRF8yODlfIiBkPSJNMTUuNSw5LjZoLTIuNFY0LjdjMC0xLjQtMC43LTIuMS0yLTIuMWgtMC44VjAuMmgwLjNjMy4yLDAsNC44LDEuNSw0LjgsNC42VjkuNnoiLz4NCgk8cGF0aCBpZD0iWE1MSURfMjg2XyIgZD0iTTQuNCwxMC4zdjQuNWMwLDMsMS43LDUsNC44LDVoMC4yaDEuMmgwLjFjMy4xLDAsNC43LTEuOSw1LTV2LTQuNUg0LjR6IE0xMC42LDE1LjJ2MS4yDQoJCWMwLDAuNC0wLjMsMC44LTAuNywwLjhjLTAuNCwwLTAuNy0wLjMtMC43LTAuOHYtMS4yYy0wLjMtMC4zLTAuOC0wLjgtMC44LTEuNGMwLTAuOSwwLjctMS42LDEuNi0xLjZjMC45LDAsMS42LDAuNywxLjYsMS42DQoJCUMxMS41LDE0LjMsMTAuOSwxNC45LDEwLjYsMTUuMnoiLz4NCgk8cGF0aCBpZD0iWE1MSURfMjc3XyIgZD0iTTQuNCw0LjdjMC4xLTMsMS43LTQuNiw0LjgtNC42aDAuM3YyLjVIOC44Yy0xLjMsMC0yLDAuNy0yLDIuMXY0LjlINC40VjQuN3oiLz4NCjwvZz4NCjwvc3ZnPg0K';
	add_menu_page(__('Lockr Key Storage', 'storm-trooper-targeting'), __('Lockr', 'storm-trooper-targeting'), 'manage_options', 'lockr', 'lockr_keys_table', $icon_svg);
	add_submenu_page('lockr', __('Lockr Key Storage', 'storm-trooper-targeting'), __('All Keys', 'storm-trooper-targeting'), 'manage_options', 'lockr');
	add_submenu_page('lockr', __('Create Lockr Key', 'storm-trooper-targeting'), __('Add Key', 'storm-trooper-targeting'), 'manage_options', 'lockr-add-key', 'lockr_add_form');
	add_submenu_page('lockr', __('Override Option', 'storm-trooper-targeting'), __('Override Option', 'storm-trooper-targeting'), 'manage_options', 'lockr-override-option', 'lockr_override_form');
	add_submenu_page(null, __('Edit Lockr Key', 'storm-trooper-targeting'), __('Edit Key', 'storm-trooper-targeting'), 'manage_options', 'lockr-edit-key', 'lockr_edit_form');
	add_submenu_page('lockr', __('Lockr Configuration', 'storm-trooper-targeting'), __('Lockr Configuration', 'storm-trooper-targeting'), 'manage_options', 'lockr-site-config', 'lockr_configuration_form');
}

/**
 * Queue up our stylesheet for the admin interface.
 *
 * @param string $hook The name of the admin page we're on.
 */
function lockr_admin_styles($hook)
{

	if ('lockr' === substr($hook, 0, 5)) {
		wp_enqueue_style('lockrStylesheet', plugins_url('css/lockr.css', __FILE__), array(), '2.4', 'all');
		wp_enqueue_script('lockrScript', plugins_url('js/lockr.js', __FILE__), array(), '2.4', true);
	} elseif ('post' === substr($hook, 0, 4)) {
		wp_enqueue_script('lockrScript', plugins_url('js/lockr-post.js', __FILE__), array(), '2.4', true);
	}
}
add_action('admin_enqueue_scripts', 'lockr_admin_styles');

if (!get_option('lockr_partner')) {
	$partner = lockr_get_partner();

	if ($partner) {
		add_option('lockr_partner', $partner['name']);
	}
}

/**
 * Create a table of all the keys in Lockr.
 */
function lockr_keys_table()
{

	global $wpdb;
	$table_name      = $wpdb->prefix . 'lockr_keys';
	$query           = "SELECT * FROM $table_name WHERE key_name = 'lockr_default_key'";
	$default_key     = $wpdb->get_results($query); // WPCS: unprepared SQL OK.
	$status          = lockr_check_registration();
	$exists          = $status['exists'];
	$available       = $status['available'];
	$deleted_default = get_option('lockr_default_deleted');
	$auto_created    = (int) $default_key[0]->auto_created;

	if ($exists && !$default_key && !$deleted_default) {
		// Create a default encryption key.
		$client    = lockr_key_client();
		$key_value = base64_encode($client->create(256));

		lockr_set_key('lockr_default_key', $key_value, 'Lockr Default Encryption Key', null, true);
	}
	if ($default_key && !$auto_created) {
		$key_id    = array('id' => $default_key[0]->id);
		$key_data  = array('auto_created' => true);
		$key_store = $wpdb->update($table_name, $key_data, $key_id);
	}

	if (isset($status['info']['env'])) {

		if ('prod' === $status['info']['env']) {
			$environment = $status['info']['env'];
		} else {
			$environment = 'dev';
		}
		if (!get_option('lockr_' . $environment . '_abstract_migrated')) {
			lockr_update_abstracts($environment);
		}
	}

	$key_table = new Lockr_Key_List();
	$key_table->prepare_items();
?>
	<div class="wrap">
		<?php if (!$exists) : ?>
			<h1><?php _e('Register Lockr First', 'storm-trooper-targeting'); ?></h1>
			<?php /* translators: 1. <a>, 2. </a> */ ?>
			<p><?php sprintf(_e('Before you can add keys, you must first %sregister your site%s with Lockr.', 'storm-trooper-targeting'), '<a href="' . esc_url(admin_url('admin.php?page=lockr-site-config')) . '">', '</a>'); ?></p>
		<?php else : ?>
			<h1><?php _e('Lockr Key Storage:', 'storm-trooper-targeting'); ?></h1>
			<?php if (isset($_GET['message']) && 'success' === $_GET['message']) : ?>
				<div id='message' class='updated fade'>
					<p><strong><?php _e('You successfully added the key to Lockr.', 'storm-trooper-targeting'); ?></strong></p>
				</div>
			<?php endif; ?>
			<?php if (isset($_GET['message']) && 'editsuccess' === $_GET['message']) : ?>
				<div id='message' class='updated fade'>
					<p><strong><?php _e('You successfully edited your key in Lockr.', 'storm-trooper-targeting'); ?></strong></p>
				</div>
			<?php endif; ?>
			<?php /* translators: 1. <a>, 2. </a> */ ?>
			<p><?php sprintf(_e('Below is a list of the keys currently stored within Lockr. You may edit/delete from here or %sadd one manually%s for any plugins not yet supporting Lockr.', 'storm-trooper-targeting'), '<a href="' . esc_url(admin_url('admin.php?page=lockr-add-key')) - '">', '</a>'); ?></p>
			<form id="lockr-key-table" method="post">
				<input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? intval($_REQUEST['page']) : ''; ?>" />
				<?php $key_table->display(); ?>
			</form>
		<?php endif; ?>
	</div>
<?php
}


/**
 * Migrate the abstracts into their correct environment display.
 *
 * @param string $environment What environment the site is in.
 */
function lockr_update_abstracts($environment)
{

	global $wpdb;
	$table_name = $wpdb->prefix . 'lockr_keys';
	$query      = "SELECT * FROM $table_name";
	$keys       = $wpdb->get_results($query); // WPCS: unprepared SQL OK.

	foreach ($keys as $key) {
		$key_value = lockr_get_key($key->key_name);

		if ($key_value) {
			$key_abstract = '**************' . substr($key_value, -4);
			$key_id       = array('id' => $key->id);

			if ('prod' !== $environment) {
				$key_data = array('dev_abstract' => $key_abstract);
			} else {
				$key_data = array('key_abstract' => $key_abstract);
			}

			$key_store = $wpdb->update($table_name, $key_data, $key_id);
		}
	}
	update_option('lockr_' . $environment . '_abstract_migrated', true);
}
