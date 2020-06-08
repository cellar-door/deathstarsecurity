<?php

/**
 * Admin page for Droid Scanner.
 *
 * @package DroidScanner
 */

// Don't call the file directly and give up info!
if (!function_exists('add_action')) {
	echo __('Lock it up!', 'droid-scanner');
	exit;
}

require_once DROIDSCANNER__PLUGIN_DIR . '/class-droid-scanner-list.php';

add_action('admin_menu', 'droidscanner_admin_menu');

/**
 * Create our admin pages and put them into the admin menu.
 */
function droidscanner_admin_menu()
{
	add_menu_page(__('Droid Scanner', 'droid-scanner'), __('Droid Scanner', 'droid-scanner'), 'publish_posts', 'droidscanner', 'droidscanner_table');
}

/**
 * Create a table of all the Droid Scanner Entries.
 */
function droidscanner_table()
{

	$droidscanner_table = new Droid_Scanner_List();
	$droidscanner_table->prepare_items();
?>
	<div class="wrap">

		<h1><?php _e('Droid Scanner Entries:', 'droid-scanner'); ?></h1>
		<p><?php _e('Below is a list of the entries from the Droid Scanner Forms.', 'droid-scanner'); ?></p>
		<form id="droidscanner-table" method="post">
			<input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? intval($_REQUEST['page']) : ''; ?>" />
			<?php $droidscanner_table->display(); ?>
		</form>
	</div>
<?php
}
