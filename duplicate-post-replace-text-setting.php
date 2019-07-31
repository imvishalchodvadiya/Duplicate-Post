<?php
/**
 * Add an option page
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_admin() ){ // admin actions
	add_action( 'admin_menu', 'dprt_menu' );
	add_action( 'admin_init', 'dprt_register_settings' );
}

function dprt_register_settings() { // whitelist options
	register_setting( 'dprt_group', 'dprt_replace_from');
	register_setting( 'dprt_group', 'dprt_replace_to');	
	register_setting( 'dprt_group', 'dprt_redirect_url');	
}


function dprt_menu() {
	add_options_page(__("Duplicate Post Options", 'dprt'), __("Duplicate Post and Replace Text", 'dprt'), 'manage_options', 'dprt', 'dprt_options');
}

function dprt_options() {

	if ( current_user_can( 'promote_users' ) && (isset($_GET['settings-updated'])  && $_GET['settings-updated'] == true)){
		global $wp_roles;
		$roles = $wp_roles->get_names();

		$dp_roles = get_option('dprt_roles');
		if ( $dp_roles == "" ) $dp_roles = array();

		foreach ($roles as $name => $display_name){
			$role = get_role($name);

			/* If the role doesn't have the capability and it was selected, add it. */
			if ( !$role->has_cap( 'copy_posts' )  && in_array($name, $dp_roles) )
				$role->add_cap( 'copy_posts' );

			/* If the role has the capability and it wasn't selected, remove it. */
			elseif ( $role->has_cap( 'copy_posts' ) && !in_array($name, $dp_roles) )
			$role->remove_cap( 'copy_posts' );
		}
	}
	?>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>
	<h1>
		<?php esc_html_e("Duplicate Post Options", 'dprt'); ?>
	</h1>

	<form method="post" action="options.php" style="clear: both">
		<?php settings_fields('dprt_group'); ?>

		<section>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e("Replace From", 'dprt'); ?>
					</th>
					<td><input type="text" name="dprt_replace_from"
						value="<?php echo esc_attr(get_option('dprt_replace_from')); ?>" />
					</td>
					<td><span class="description"><?php esc_html_e("i.e: Chicago", 'dprt'); ?>
					</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e("Replace To", 'dprt'); ?>
					</th>
					<td><input type="text" name="dprt_replace_to"
						value="<?php echo esc_attr(get_option('dprt_replace_to')); ?>" />
					</td>
					<td><span class="description"><?php esc_html_e("i.e: USA,London,Canada", 'dprt'); ?>
					</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e("Redirect URL", 'dprt'); ?>
					</th>
					<td><input type="text" name="dprt_redirect_url"
						value="<?php echo esc_attr(get_option('dprt_redirect_url')); ?>" />
					</td>
					<td><span class="description"><?php esc_html_e("?post_type=page", 'dprt'); ?>
					</span>
					</td>
				</tr>
			</table>
		</section>
		<p class="submit">
			<input type="submit" class="button-primary"
				value="<?php esc_html_e('Save Changes', 'dprt') ?>" />
		</p>

	</form>
</div>
<?php
}
?>