<?php
/**
 * Plugin Name: AMP Flush 
 * Description: Enable flush cache amp
 * Author: tonjoo
 * Author URI: http://tonjoostudio.com/
 * Plugin URI: http://tonjoostudio.com
 * Version: 1.0
 * Text Domain: amp-flush
 *
 * @package AMP routher
 */

require 'inc/class-amp-flush-ui.php';
require 'inc/amp-flush-ajax.php';
define( 'AMP_FLUSH_DIR', plugin_dir_url( __FILE__ ) );
/**
 * class tonjoo amp flush
 */

class TonjooAmpFlush {
	/**
	 * 
	 */
	private $user_interface;
	/**
	 * construct
	 */
	function __construct() {

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'flush-amp', array ($this,'tonjoo_flush_amp_cli' ), array(
                'shortdesc' => 'Flush cache amp lama.',
                'longdesc' => 'Flush cache amp lama.',
                'synopsis' => array(
                    array(
                        'type'        => 'assoc',
                        'name'        => 'day',
                        'description' => 'Tanggal post dibuat',
                        'optional'    => true,
                        'default'     => '7',
                    )
                )
            ) );
		}

		$this->user_interface = new TonjooAmpFlushUI();

		add_action( 'admin_menu', array($this,'tonjoo_amp_flush_menu_option') );
		add_action( 'admin_notices', array( $this, 'sample_admin_notice__success' ) );
	}

	/**
	 *  process flushing amp 
	 */
	public function tonjoo_flush_amp_cli($args, $assoc_args) {
		
		$get_amp_flush_option = get_option('amp_flush_option');
		
		if( !$get_amp_flush_option or empty($get_amp_flush_option) )
		$get_amp_flush_option = array('post'); 

		$args_query = array(
		    'posts_per_page' => -1,
		    'fields' => 'ids',
		    'post_type' => $get_amp_flush_option,
		    'post_status' => 'publish',
		    'date_query' => array(
				array(
					'column' => 'post_modified',
					'after' => date('Y-m-d', strtotime('-'.$assoc_args['day'].' days')) 
				),
		    )
		);
		$start_date = date('Y-m-d', strtotime('-'.$assoc_args['day'].' days')) ;
		$amp_post = new WP_Query( $args_query );

		WP_CLI::log( 'AMP flush post start from date : '.$start_date );

		$ID_array = $amp_post->posts;

		if($ID_array){
			foreach ( $ID_array as $id ) {
				
				if (function_exists('w3tc_pgcache_flush_post')){
					w3tc_pgcache_flush_post($id);
				}

				$permalink= get_the_permalink($id).'amp/';
				
				$site_url_flush = str_replace('.', '-', home_url());

				$remove_http =  preg_replace('#^https?://#', '', rtrim($permalink,'/'));

				$tonjoo_flush = wp_remote_get($site_url_flush.'.cdn.ampproject.org/update-ping/c/s/'.$remove_http); 

				$tonjoo_visit = wp_remote_get($site_url_flush.'.cdn.ampproject.org/c/s/'.$remove_http); 

				if( isset($tonjoo_visit['response']) ) {
					if ($tonjoo_visit['response']['code']==200 ) {
						$succes_msg = $site_url_flush.'.cdn.ampproject.org/update-ping/c/s/'.$remove_http." has been flushed". PHP_EOL;
						WP_CLI::success($succes_msg);
					} else {
						// $error_msg = $site_url_flush.'.cdn.ampproject.org/update-ping/c/s/'.$remove_http." wrong url ". PHP_EOL;
						// WP_CLI::error( $error_msg, false );
					}	
				}
			}
		}
	}

	/**
	 * register menu
	 */
	public function tonjoo_amp_flush_menu_option () {
		//set default
		$get_amp_flush_option = get_option('amp_flush_option');
		$default_value = array('post');
		if( !$get_amp_flush_option or empty($get_amp_flush_option) )
		update_option('amp_flush_option',$default_value);

		add_menu_page(
			'AMP Flush',
			'AMP Flush',
			'manage_options',
			'amp-flush-option',
			array($this,'tonjoo_amp_flush_option')
		);
		
		add_submenu_page( 'amp-flush-option', 
			'Amp Flush Option', 
			'Amp Flush Option', 
			'manage_options',
			'amp-flush-option', 
			array($this,'tonjoo_amp_flush_option') 
		);

		add_submenu_page( 'amp-flush-option', 
			'Amp Flush Page', 
			'Amp Flush Page', 
			'manage_options',
			'amp-flush-ui', 
			array($this->user_interface,'amp_flush_user_interface') 
		);

	}

	/**
	 * form post type setting 

	 */
	public function tonjoo_amp_flush_option () {
		$post_types = get_post_types(array(
					'public'   => true,
					'_builtin' => false,
				),
				'names');


		?>
		<h1>AMP Flush Setting</h1>
		<table class="form-table">
			<tr>
				<th scope="row">AMP Flush Post Type</th>
				<td>
					<form method="post" action="" ?>
						<?php wp_nonce_field( 'save-amp-flush-option', 'security-amp-flush' ); ?>
						<fieldset>
							<?php $get_amp_flush_option = get_option('amp_flush_option'); ?>
							<?php if($post_types) : ?>
								<input
									type="checkbox"
									id="post"
									name="amp_flush_post_type[]"
									value="post" 
									<?php echo (in_array('post',$get_amp_flush_option) ? 'checked' : ''); ?>
									>

								<label for="post">
									post
								</label> <br>

								<?php foreach ($post_types as $types) : ?>
									<input
									type="checkbox"
									id="<?php echo $types; ?>"
									name="amp_flush_post_type[]"
									value="<?php echo $types; ?>"
									<?php echo (in_array($types,$get_amp_flush_option) ? 'checked' : ''); ?>
									>
								<label for="<?php echo esc_html($types); ?>">
									<?php echo esc_html( $types ); ?>
								</label>
								<br>
								<?php endforeach; ?>		
							<?php endif; ?>	
							<button id="sync" class="button button-primary">Simpan</button>

							<p class="description">
								<?php
								if ( ! amp_is_canonical() ) :
									esc_html_e( 'Enable/disable AMP FLUSH post type(s) support', 'amp' );
								else :
									esc_html_e( 'Canonical AMP is enabled in your theme, so all post types will render.', 'amp' );
								endif;
							?>
							</p>

						</fieldset>

					</form>
				</td>
			</tr>
			
		</table>
	<?php

		if(isset($_POST['security-amp-flush'])) {
			if ( wp_verify_nonce( $_POST['security-amp-flush'], 'save-amp-flush-option' ) ) {
				$secure_data = array_map( 'sanitize_text_field', wp_unslash( $_POST['amp_flush_post_type'] ) );
				update_option( 'amp_flush_option', $secure_data  );
				add_action( 'admin_notices', array($this,'sample_admin_notice__success') );
				echo "<script> window.location.replace('".get_admin_url().'admin.php?page=amp-flush-option&amp-flush-success=1'."'); </script>";
			}
		}
	}

	/**
	 * admin notifikasi
	 */
	function sample_admin_notice__success() {
		if(isset($_GET['amp-flush-success'])) {
	    ?>
	    <div class="notice notice-success is-dismissible">
	        <p><?php _e( 'AMP flush set post type Done!', 'sample-text-domain' ); ?></p>
	    </div>
	    <?php
		}
	}
	

}

new TonjooAmpFlush();

