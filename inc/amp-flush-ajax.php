<?php

add_action( 'wp_ajax_amp_flush_ajax_get_post','amp_flush_ajax_get_post_callback' ) ;
/**
 * amp flush ajax get post
 */
function amp_flush_ajax_get_post_callback() {

	$limit 	= ( isset($_POST['limit']) && !empty($_POST['limit']) ) ? sanitize_text_field($_POST['limit']) : 25;
	$day 	= ( isset($_POST['day']) && !empty($_POST['day']) ) ? sanitize_text_field($_POST['day']) : 7;

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
					'after' => $day, 
				),
		    )
		);

		$start_date = date('Y-m-d', strtotime('-'.$day.' days')) ;

		$amp_post = new WP_Query( $args_query );

		$ID_array = $amp_post->posts;

		$limit_array = $ID_array;

		if(count($ID_array)>$limit)
		$limit_array = array_slice($ID_array, 0, $limit);

		wp_send_json($limit_array);

		die();
}

add_action( 'wp_ajax_amp_flush_ajax_process','amp_flush_ajax_process_callback' ) ;
/**
 * amp flushing process
 */
function amp_flush_ajax_process_callback() {
	
	$post_id 	= ( isset($_GET['post_id']) && !empty($_GET['post_id']) ) ? (int) $_GET['post_id'] : "";

	if(!empty($post_id)) {
		if (function_exists('w3tc_pgcache_flush_post')){
			w3tc_pgcache_flush_post($post_id);
		}

		$permalink= get_the_permalink($post_id).'amp/';

		$site_url_flush = str_replace('.', '-', home_url());

		$remove_http =  preg_replace('#^https?://#', '', rtrim($permalink,'/'));

		$tonjoo_visit = wp_remote_get($site_url_flush.'.cdn.ampproject.org/c/s/'.$remove_http); 

		$tonjoo_flush = wp_remote_get($site_url_flush.'.cdn.ampproject.org/update-ping/c/s/'.$remove_http); 

		if( isset($tonjoo_visit['response']) ) {
			if ($tonjoo_visit['response']['code']==200 ) {
				$succes_msg = $site_url_flush.'.cdn.ampproject.org/c/s/'.$remove_http;
				echo "<tr> <td> <label class='amp-log success'> succes </label>   ".$succes_msg." </td> </tr>";
			} else {
				$error_msg = $site_url_flush.'.cdn.ampproject.org/c/s/'.$remove_http;
				echo "<tr> <td>  <label class='amp-log danger'> failed </label>   ".$error_msg." </td> </tr>";
			}	
		}
	}

	die();
}