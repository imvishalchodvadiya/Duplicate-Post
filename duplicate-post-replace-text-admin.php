<?php
if(!is_admin())
	return;

// Admin Settings
require_once (dirname(__FILE__).'/duplicate-post-replace-text-setting.php');

function dprt_duplicate_main_wrapper(){
	global $wpdb;
	
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
		wp_die(esc_html__('No post to duplicate has been supplied!'));
	}

	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );

	$post = get_post( $post_id );
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;

	$replaceTo = sanitize_text_field(get_option('dprt_replace_to'));
	$replaceToArray = explode(",",$replaceTo);
	
	if (isset( $post ) && $post != null) {
		if(get_option('dprt_replace_to') != null && get_option('dprt_replace_from') != null) {
			for($i=0;$i<count($replaceToArray);$i++) {
				$args = array(
					'comment_status' => $post->comment_status,
					'ping_status'    => $post->ping_status,
					'post_author'    => $new_post_author,
					'post_content'   => $post->post_content,
					'post_excerpt'   => $post->post_excerpt,
					'post_name'      => $post->post_name,
					'post_parent'    => $post->post_parent,
					'post_password'  => $post->post_password,
					'post_status'    => 'publish',
					'post_title'     => $post->post_title,
					'post_type'      => $post->post_type,
					'to_ping'        => $post->to_ping,
					'menu_order'     => $post->menu_order
				);

				$postaray = str_ireplace(get_option('dprt_replace_from'), $replaceToArray[$i], $args);
			// print_R($postaray);
			// exit;
				$new_post_id = wp_insert_post( $postaray );

				$taxonomies = get_object_taxonomies($post->post_type);

				foreach ($taxonomies as $taxonomy) {
					$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
					wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
				}

				$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
				if (count($post_meta_infos)!=0) {
					$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
					foreach ($post_meta_infos as $meta_info) {
						$meta_key = $meta_info->meta_key;
						if( $meta_key == '_wp_old_slug' ) continue;
						$meta_value = addslashes($meta_info->meta_value);
						$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
					}
					$sql_query.= implode(" UNION ALL ", $sql_query_sel);
					$wpdb->query($sql_query);
				}
			}
		} else {
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'publish',
				'post_title'     => $post->post_title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);

			$new_post_id = wp_insert_post( $args );

			$taxonomies = get_object_taxonomies($post->post_type);

			foreach ($taxonomies as $taxonomy) {
				$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
			}

			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
			if (count($post_meta_infos)!=0) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ($post_meta_infos as $meta_info) {
					$meta_key = $meta_info->meta_key;
					if( $meta_key == '_wp_old_slug' ) continue;
					$meta_value = addslashes($meta_info->meta_value);
					$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
				}
				$sql_query.= implode(" UNION ALL ", $sql_query_sel);
				$wpdb->query($sql_query);

			}
		}

		$url = 'edit.php';
		if(get_option('dprt_redirect_url') != null) {
			$url = $url.get_option('dprt_redirect_url');
		}
		wp_redirect( admin_url($url) );
	} else {
		wp_die(esc_html__('Post creation failed, could not find original post: ' . $post_id));
	}
}
add_action( 'admin_action_dprt_duplicate_main_wrapper', 'dprt_duplicate_main_wrapper' );

function dprt_duplicate_post_link( $actions, $post ) {
	if (current_user_can('edit_posts')) {
		$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=dprt_duplicate_main_wrapper&post=' . $post->ID, basename(__FILE__), 'dprt' ) . '" title="'.esc_html("Duplicate this item", "dprt").'" rel="permalink">'.esc_html("Duplicate", "dprt").'</a>';
	}
	return $actions;
}

add_filter( 'post_row_actions', 'dprt_duplicate_post_link', 10, 2 );
add_filter('page_row_actions', 'dprt_duplicate_post_link', 10, 2);