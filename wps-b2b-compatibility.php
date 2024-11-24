<?php 
/**
 * Plugin Name: WPS B2B Compatibility
 * Plugin URI: https://www.itthinx.com
 * Description: Add compatibility for WooCommerce B2B
 * Version: 1.0.0
 * Author: itthinx
 * Author URI: https://www.itthinx.com
 */

function b2b_woocommerce_product_search_service_post_ids_for_request( &$include, $cache_context ) {
	if ( count( $include ) > 0 ) {
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'b2b_wps_woocommerce_product_data_store_cpt_get_products_query', 10, 3 );
		$q = new WC_Product_Query( array(
			'return' => 'ids',
			'post_type' => array( 'product', 'product_variation' ),
			'posts_per_page' => -1,
			'status' => 'publish',
			'suppress_filters' => false,
			'no_found_rows' => true
		) );
		$ids = $q->get_products();
		remove_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'b2b_wps_woocommerce_product_data_store_cpt_get_products_query', 10 );
		$ids = array_unique( array_map( 'intval', $ids ) );
		$include = array_intersect( $include, $ids );
	}
}
add_action( 'woocommerce_product_search_service_post_ids_for_request', 'b2b_woocommerce_product_search_service_post_ids_for_request', 10, 2 );


function b2b_wps_woocommerce_product_data_store_cpt_get_products_query( $wp_query_args, $query_args, $data_store ) {
	if ( is_user_logged_in() ) {
		$b2b_group = get_user_meta( get_current_user_id(), 'wcb2b_group', true );
		$b2b = !in_array( $b2b_group, apply_filters( 'templ_b2c_groups', array(70330) ) );

		if ( !$b2b ) {
			$wp_query_args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => 'hide_for_b2c',
					'compare' => 'NOT LIKE',
					'value'   => '1',
				),
				array(
					'key'     => 'hide_for_b2c',
					'compare' => 'NOT EXISTS',
				),
			);
		}
	}
	$wp_query_args['post_type'] = array( 'product', 'product_variation' );

	unset( $wp_query_args['tax_query'] );
	return $wp_query_args;
}
