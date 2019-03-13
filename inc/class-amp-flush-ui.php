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

/**
 * class tonjoo amp flus ui
 */
class TonjooAmpFlushUI  {
	/**
	 * construct
	 */
	function __construct() {

		add_action('admin_enqueue_scripts',array($this , 'amp_flush_admin_script') );
	}

	/**
	 * add custom admin styling
	 */
	function amp_flush_admin_script() {
		$page = isset($_GET['page']) ? $_GET['page'] : '';

		$amp_flush_page = array('amp-flush-ui','amp-flush-option');

  		//css
  		wp_register_style('amp-flush-admin-styles', AMP_FLUSH_DIR.'assets/css/admin.css');

  		//js
  		wp_register_script('amp-flush-admin-js', AMP_FLUSH_DIR.'assets/js/admin.js',array(),false,true);
  		wp_localize_script( 'amp-flush-admin-js', 'ajaxampflush', array( 
	        'ajaxurl' => admin_url( 'admin-ajax.php' ),
	        'pluginurl' => AMP_FLUSH_DIR,
	    ));

  		if(in_array($page, $amp_flush_page)) {
			wp_enqueue_style( 'amp-flush-admin-styles' );
  			wp_enqueue_script( 'jquery-ui-datepicker' );
  			wp_enqueue_script( 'amp-flush-admin-js' );
  		}

	}

	/**
	 * ui function
	 */
	function amp_flush_user_interface(){
	?>
	<div class="wrap" id="amp-flush">
		<h1 class="wp-heading-inline"><?php _e( 'Amp Flush', 'hipwee' ); ?></h1>
		<hr class="wp-header-end">

		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<form id="amp-flush-form">
					<label class="amp-label">Limit Post</label>
					<select name="jumlah_post" id="jumlah_post">
						<?php for($i=1; $i < 5; $i++) : ?>
							<option value="<?php echo (25*$i); ?>"> <?php echo (25*$i); ?> </option>
						<?php endfor; ?> 
						<option value="300">300</option>
					</select>
					<label class="amp-label">From date</label>
					<?php $default_startdate = date('Y-m-d', strtotime('-30 days')); ?>
					<input type="text" value="<?php echo $default_startdate; ?>" name="amp-flush-start-date" id="amp-flush-start-date" placeholder="Start Date" autocomplete="off"> 

					<button type="submit" id="purge-amp-flush" class="button action" > Purge </button>

				</form>
			</div>
		</div>


		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col"  class="manage-column  column-primary ">
						<span id="label-flush">	Purged post from <?php echo $default_startdate; ?> until now  </span>
					</th>	
				</tr>
			</thead>
			<tbody id="amp-flush-result">
				
			</tbody>
		</table>
	</div>
	<?php
	}

}

