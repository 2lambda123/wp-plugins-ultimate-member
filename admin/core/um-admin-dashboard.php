<?php

class UM_Admin_Dashboard {

	function __construct() {
		
		$this->slug = 'ultimatemember';
		
		$this->about_tabs['about'] = 'About';
		$this->about_tabs['start'] = 'Getting Started';

		add_action('admin_menu', array(&$this, 'primary_admin_menu'), 0);
		add_action('admin_menu', array(&$this, 'secondary_menu_items'), 1000); 
		
	}
	
	/***
	***	@setup admin menu
	***/
	function primary_admin_menu() {
		
		$this->pagehook = add_menu_page( __('Ultimate Member', $this->slug), __('Ultimate Member', $this->slug), 'manage_options', $this->slug, array(&$this, 'admin_page'), 'dashicons-admin-users', '66.78578');
		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
		
		add_submenu_page( $this->slug, __('Dashboard', $this->slug), __('Dashboard', $this->slug), 'manage_options', $this->slug, array(&$this, 'admin_page') );

		foreach( $this->about_tabs as $k => $tab ) {
			add_submenu_page( '_'. $k . '_um', sprintf(__('%s | Ultimate Member', $this->slug), $tab), sprintf(__('%s | Ultimate Member', $this->slug), $tab), 'manage_options', $this->slug . '-' . $k, array(&$this, 'admin_page') );
		}
		
	}

	/***
	***	@secondary admin menu (after settings)
	***/
	function secondary_menu_items() {

		add_submenu_page( $this->slug, __('Forms', $this->slug), __('Forms', $this->slug), 'manage_options', 'edit.php?post_type=um_form', '', '' );
		add_submenu_page( $this->slug, __('User Roles', $this->slug), __('User Roles', $this->slug), 'manage_options', 'edit.php?post_type=um_role', '', '' );

		if ( um_get_option('members_page' ) || !get_option('um_options') ){
			add_submenu_page( $this->slug, __('Member Directories', $this->slug), __('Member Directories', $this->slug), 'manage_options', 'edit.php?post_type=um_directory', '', '' );
		}
		
		do_action('um_extend_admin_menu');
	
	}
	
	/***
	***	@load metabox stuff
	***/
	function on_load_page() {

		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');

		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
		
		/** custom metaboxes for dashboard defined here **/
		
		add_meta_box('um-metaboxes-contentbox-1', __('Users Overview','ultimatemember'), array(&$this, 'users_overview'), $this->pagehook, 'normal', 'core');
		
		add_meta_box('um-metaboxes-sidebox-1', __('Purge Temp Files','ultimatemember'), array(&$this, 'purge_temp'), $this->pagehook, 'side', 'core');

	}
	
	function users_overview() {
		global $ultimatemember;
		include_once um_path . 'admin/templates/dashboard/users.php';
	}
	
	function purge_temp() {
		global $ultimatemember;
		include_once um_path . 'admin/templates/dashboard/purge.php';
	}
	
	/***
	***	@get a directory size
	***/
	function dir_size( $directory ) {
		global $ultimatemember;
		if ( $directory == 'temp' ) {
			$directory = $ultimatemember->files->upload_temp;
			$size = 0;
			foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
				$size+=$file->getSize();
			}
			return round ( $size / 1048576, 2);
		}
		return 0;
	}
	
	/***
	***	@which admin page to show?
	***/
	function admin_page() {
		
		$page = $_REQUEST['page'];
		if ( $page == 'ultimatemember' ) {

		?>
		
		<div id="um-metaboxes-general" class="wrap">
		
			<h2>Ultimate Member <sup><?php echo ultimatemember_version; ?></sup></h2>
			
			<form action="admin-post.php" method="post">
				
				<?php wp_nonce_field('um-metaboxes-general'); ?>
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
				
				<input type="hidden" name="action" value="save_um_metaboxes_general" />
				
				<div id="poststuff">
		
					 <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>"> 

						  <div id="postbox-container-2" class="postbox-container"><?php do_meta_boxes($this->pagehook,'normal',null);  ?></div>	    
						  <div id="postbox-container-1" class="postbox-container"><?php do_meta_boxes($this->pagehook,'side',null); ?></div>   						  

					 </div>

				</div>
				 
			</form>
		</div><div class="um-admin-clear"></div>
		
		<div class="um-admin-dash-share"><?php global $reduxConfig; foreach ( $reduxConfig->args['share_icons'] as $k => $arr ) { ?><a href="<?php echo $arr['url']; ?>" class="um-about-icon um-admin-tipsy-n" title="<?php echo $arr['title']; ?>" target="_blank"><i class="<?php echo $arr['icon']; ?>"></i></a><?php } ?>	
		</div><div class="um-admin-clear"></div>
		
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			});
			//]]>
		</script>
		
		<?php
			
		} else if ( strstr( $page, 'ultimatemember-' ) ) {

			$template = str_replace('ultimatemember-','',$page);
			$file = um_path . 'admin/templates/welcome/'. $template . '.php';

			if ( file_exists( $file ) ){
				include_once um_path . 'admin/templates/welcome/'. $template . '.php';
			}

		}
	
	}

}

$um_dashboard = new UM_Admin_Dashboard();