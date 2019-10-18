<?php
/**
 * Social Snap social share counters. Returns share counts.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
 */

// Count Clicks on share buttons.
add_action( 'wp_ajax_ss_social_share_clicks',        'socialsnap_update_share_count_click' );
add_action( 'wp_ajax_nopriv_ss_social_share_clicks', 'socialsnap_update_share_count_click' );

/**
 * Update share network click counter for a post/page.
 * Cache the values to post meta.
 *
 * @since 1.0.0
 */
function socialsnap_update_share_count_click() {

	// Security check.
	check_ajax_referer( 'socialsnap-nonce' );
	
	// Data is required.
	if ( ! isset( $_POST['ss_click_data'] ) ) {
		wp_send_json_error();
	}

	// Parse data.
	$click_data = str_replace( '\\', '', $_POST['ss_click_data'] );
	$click_data = json_decode( $click_data, true );

	// Sanitize data.
	$network = isset( $click_data['network'] ) ? sanitize_text_field( $click_data['network'] ) 			: '';
	$post_id = isset( $click_data['post_id'] ) ? intval( sanitize_text_field( $click_data['post_id'] ) ) : '';

	$click_data['type'] = 'heart' == $network ? 'like' : 'share';

	// Add to Stats DB. This function will validate and sanitize data.
	$share_count = socialsnap_add_to_stats_db( $click_data );

	if ( is_null ( $share_count ) ) {
		wp_send_json_error();
	}

	// Store new share count values.
	if ( -1 == $post_id ) {
		update_option( 'socialsnap_homepage_click_share_count_' . $network, $share_count );
	} else {
		update_post_meta( $post_id, 'ss_ss_click_share_count_' . $network, $share_count );
	}	

	wp_send_json_success();
}

/**
 * Get share count of a network for a particular post/page.
 *
 * @return Integer number of $network shares for a post/page object
 * @since 1.0.0
 */
function socialsnap_get_share_count( $network, $args = array() ) { 

	// Default Args.
	$defaults = array(
		'post_id'	=> '',
		'url'		=> '',
	);

	$args = wp_parse_args( $args, $defaults );

	// Take current post URL and ID.
	if ( empty( $args['url'] ) && empty( $args['post_id'] ) ) {
		$args['url'] 		= get_permalink();
		$args['post_id'] 	= get_the_ID();
	}

	// Get post ID based on URL.
	if ( empty( $args['post_id'] ) && ! empty( $args['url'] ) ) {
		$args['post_id'] = socialsnap_get_current_post_id( $args['url'] );
	}

	// Get URL based on post ID.
	if ( empty( $args['url'] ) && ! empty( $args['post_id'] ) ) {
		$args['url'] = socialsnap_get_shared_permalink( array( 'post_id' => $args['post_id'], 'network' => $network ) );
	}

	if ( 0 === $args['post_id'] ) {
		return;
	}

	$count = false;

	// Get share counts from network API
	if ( in_array( $network, socialsnap_get_social_share_networks_with_api() ) ) {

		if ( -1 === $args['post_id'] ) {
			$return = get_option( 'socialsnap_homepage_share_count_' . $network );
		} else {
			$return = get_post_meta( $args['post_id'], 'ss_ss_share_count_' . $network, true );
		}

		$count = intval( $return );
	} 

	return apply_filters( 'socialsnap_social_share_counts', $count, $network, $args );
}

// Update total share counts
add_action( 'wp_ajax_ss_social_share_total', 		 		'socialsnap_update_total_share_count' );
add_action( 'wp_ajax_nopriv_ss_social_share_total', 		'socialsnap_update_total_share_count' );

/**
 * Ajax force update of total share counts.
 *
 * @return boolean, cache is expired
 * @since 1.0.0
 */
function socialsnap_update_total_share_count() {

	// Security check.
	check_ajax_referer( 'socialsnap-nonce' );	

	// Data is required.
	if ( ! isset( $_POST['ss_data'] ) ) {
		wp_send_json_error();
	}

	// Parse data.
	$_data = str_replace( '\\', '', $_POST['ss_data'] );
	$_data = json_decode( $_data, true );

	// Sanitize data.
	$url     = isset( $_data['url'] ) ? esc_url_raw( $_data['url'] ) : '';
	$post_id = isset( $_data['post_id'] ) ? intval( sanitize_text_field( $_data['post_id'] ) ) : '';
	$total   = socialsnap_get_total_share_count( array( 'post_id' => $post_id, 'url' => $url ), true );

	wp_send_json_success( array( 
		'total_count' => socialsnap_format_number( $total ) 
	) );
}

/**
 * Check if share counts have expired (only for api counters).
 *
 * @return boolean, cache is expired
 * @since 1.0.0
 */
function socialsnap_share_count_expired( $url = null, $post_id = null ) {

	if ( ! isset( $post_id ) ) {
		$post_id = socialsnap_get_current_post_id( $url );
	}

	if ( ! $post_id ) {
		return false;
	}

	// Last updated timestamp.
	if ( -1 == $post_id ) {
		$old_time  = strtotime( get_option( 'socialsnap_homepage_share_count_timestamp' ) );
		$post_date = strtotime( 'now' );
	} else {
		$old_time  = strtotime( get_post_meta( $post_id, 'socialsnap_share_count_timestamp', true ) );
		$post_date = get_the_date( 'U', $post_id );
	}

	if ( false === $old_time ) {
		return true;
	}

	$intervals = array(
		array(
			'post_date' => strtotime( '-1 day' ),
			'interval'  => strtotime( '-1 hour' ),
		),
		array(
			'post_date' => strtotime( '-5 days' ),
			'interval'  => strtotime( '-6 hours' ),
		),
		array(
			'post_date' => 0,
			'interval'  => strtotime( '-5 days' ),
		),
	);

	$intervals = apply_filters( 'socialsnap_expiry_intervals', $intervals );

	$expired_time = false;

	foreach ( $intervals as $i ) {
		if ( $post_date > $i['post_date'] ) {
			$expired_time = $i['interval'];
			break;
		}
	}

	return $old_time < $expired_time;
}

/**
 * Generate share count request url for $url on $network.
 *
 * @return string, request URL.
 * @since 1.0.0
 */
function socialsnap_generate_share_request_url( $network, $url ) {

	// Default args.
	$request_url = '';

	switch ( $network ) {
		case 'facebook':

			$provider = socialsnap_settings( 'ss_ss_facebook_count_provider' );

			if ( 'sharedcount' === $provider ) {
				$request_url = 'https://api.sharedcount.com/v1.0/?apikey=' . rawurlencode( socialsnap_settings( 'ss_ss_facebook_shared_count_api' ) ) . '&url=';
			} else {

				$token = '';

				if ( 'token' === $provider ) {
					$token = socialsnap_settings( 'ss_ss_facebook_access_token' );
				} else {

					$_token = get_transient( 'ss_facebook_token' );

					if ( false !== $_token && isset( $_token['access_token'] ) ) {
						$token = $_token['access_token'];
					}
				}

				if ( ! empty( $token ) ) {
					$request_url = 'https://graph.facebook.com/?fields=engagement&access_token=' . rawurlencode( $token ) . '&id=';
				}
			}

			break;
	}
	
	$url = str_replace( array( '?ss_cache_refresh', '&ss_cache_refresh' ), '', $url );

	$request_url = apply_filters( 'socialsnap_social_share_request_url', $request_url, $network, $url );

	if ( '' === $request_url || '' === $url ) {
		return;	
	}

	return $request_url . rawurlencode( $url );
}

/**
 * Contact network API to get share count.
 * Only for networks that support API.
 *
 * @return integer, share count for $permalink on $network.
 * @since 1.0.0
 */
function socialsnap_get_share_count_api( $network, $permalink = '' ) {
	
	$result        = 0;
	$request_url   = socialsnap_generate_share_request_url( $network, $permalink );
	$args          = array( 'timeout' => 30 ); 
	$count_request = wp_remote_get( $request_url, $args );

	if ( ! is_wp_error( $count_request ) && wp_remote_retrieve_response_code( $count_request ) == 200 ) {
		
		$response = wp_remote_retrieve_body( $count_request );

		if ( ! empty( $response ) ) {
			
			switch ( $network ) {
				case 'facebook':
					$response = json_decode( $response );

					if ( isset( $response ) ) {
						if ( isset( $response->engagement ) ) {

							$engagement = $response->engagement;

							if ( isset( $engagement->reaction_count ) ) {
								$result += intval( $engagement->reaction_count );
							}

							if ( isset( $engagement->comment_count ) ) {
								$result += intval( $engagement->comment_count );
							}

							if ( isset( $engagement->share_count ) ) {
								$result += intval( $engagement->share_count );
							}

							if ( isset( $engagement->comment_plugin_count ) ) {
								$result += intval( $engagement->comment_plugin_count );
							}
							
						} elseif ( isset( $response->Facebook ) ) {
							if ( isset( $response->Facebook->total_count ) ) {
								$result = intval( $response->Facebook->total_count );
							}
						}
					}

					break;
			}
		}
	}

	return apply_filters( 'socialsnap_social_share_api_response', $result, $network, $count_request );
}

/**
 * Get total share count for a post.
 *
 * @return integer, share count for for post
 * @since 1.0.0
 */
function socialsnap_get_total_share_count( $args = array(), $force_refresh = false ) {

	// Default Args.
	$defaults = array(
		'post_id' => '',
		'url'     => '',
	);

	$args = wp_parse_args( $args, $defaults );

	// Take current post URL and ID.
	if ( '' == $args['url'] && '' == $args['post_id'] ) {
		$args['url']     = get_permalink();
		$args['post_id'] = get_the_ID();
	}

	// Get post ID based on URL.
	if ( empty( $args['post_id'] ) && ! empty( $args['url'] ) ) {
		$args['post_id'] = socialsnap_get_current_post_id( $args['url'] );
	}

	// Post ID is required.
	if ( empty( $args['post_id'] ) || empty( $args['post_id'] ) ) {
		return;
	}

	$args['post_id'] = intval( $args['post_id'] );

	$total = 0;

	// Force refresh the total share count. Sum up share counts from all networks.
	if ( $force_refresh ) {

		$networks 		= socialsnap_get_social_share_networks();
		$current_url 	= socialsnap_get_current_url( $args['post_id'] );

		foreach ( $networks as $network => $name ) {

			if ( in_array( $network, array( 'heart' ) ) ) {
				continue;
			}

			// Get URL based on post ID.
			if ( empty( $args['url'] )  && ! empty( $args['post_id'] ) ) {
				$args['url'] = socialsnap_get_shared_permalink( array( 'permalink' => $current_url, 'network' => $network ) );
			}

			$total += socialsnap_get_share_count( $network, array( 'url' => $args['url'], 'post_id' => $args['post_id'] ) );
		}

		// Cache the new value.
		if ( -1 === $args['post_id'] ) {
			update_option( 'socialsnap_homepage_share_count_total', intval( $total ) );
		} else {
			update_post_meta( $args['post_id'], 'ss_total_share_count', intval( $total ) );
		}

	} else {

		// Get cached value
		if ( -1 === $args['post_id'] ) {
			$total = get_option( 'socialsnap_homepage_share_count_total' );
		} else {
			$total = get_post_meta( $args['post_id'], 'ss_total_share_count', true );
		}
	}

	return apply_filters( 'socialsnap_total_share_count', $total, $args['post_id'], $args['url'] );
}

// Get URL Variations.
add_filter( 'socialsnap_share_url_slashes_sanitize', 'socialsnap_social_share_url_variations', 10, 3 );

/**
 * Get URL variations, with or without slashes.
 *
 * @since 1.0.0
 */
function socialsnap_social_share_url_variations( $urls, $network, $args = array() ) {

	if ( ! is_array( $urls ) || empty( $urls ) ) {
		return $urls;
	}

	if ( ! in_array( $network, array( 'pinterest', 'tumblr', 'reddit', 'vkontakte' ) ) ) {
		return $urls;
	}

	$new_urls = array();
	foreach ( $urls as $url ) {

		$new_urls[] = $url;
		$new_urls[] = untrailingslashit( $url );

		if ( false == strpos( $url, '?' ) ) {
			$new_urls[] = trailingslashit( $url );
		}
	}

	return $new_urls;
}

// Contact Network API for share counts.
add_action( 'wp_ajax_ss_social_share_api_counts',        'socialsnap_refresh_share_counts' );
add_action( 'wp_ajax_nopriv_ss_social_share_api_counts', 'socialsnap_refresh_share_counts' );

/**
 * Get share counts from network API if possible. 
 * Save the values to post meta.
 *
 * @since 1.0.0
 */
function socialsnap_refresh_share_counts() {

	// Security check.
	check_ajax_referer( 'socialsnap-nonce' );

	// Data is required.
	if ( ! isset( $_POST['socialsnap_data'] ) ) {
		wp_send_json_error();
	}

	// Parse data.
	$_data = str_replace( '\\', '', $_POST['socialsnap_data'] );
	$_data = json_decode( $_data, true );

	// Sanitize data.
	$url     = isset( $_data['url'] ) ? esc_url_raw( $_data['url'] ) : '';
	$post_id = isset( $_data['post_id'] ) ? intval( sanitize_text_field( $_data['post_id'] ) ) : '';

	// Both URL and post ID are required.
	if ( ! $url || ! $post_id ) {
		wp_send_json_error();
	}

	// Get list of social networks with API.
	$networks = socialsnap_get_social_share_networks_with_api();

	$result = array();

	// Go through all networks.
	foreach ( $networks as $network ) {

		$count 	= 0;
		$saved 	= 0;

		// Get current counts.
		if ( -1 == $post_id ) {
			$saved = intval( get_option( 'socialsnap_homepage_share_count_' . $network ) );
		} else {
			$saved = intval( get_post_meta( $post_id, 'ss_ss_share_count_' . $network, true ) );
		}

		// Get URLs to count in.
		$urls = array( $url );
		$urls = apply_filters( 'socialsnap_alternative_urls', $urls, $post_id );
		$urls = apply_filters( 'socialsnap_share_url_slashes_sanitize', $urls, $network );
		$urls = array_unique( $urls );

		// Get share count for each URL.
		if ( is_array( $urls ) && ! empty( $urls ) ) {
			foreach ( $urls as $alt_url ) {
				$count += intval( socialsnap_get_share_count_api( $network, $alt_url ) );
			}		
		}

		// Only update if new count is greater than old count.
		if ( $count > $saved ) {

			if ( -1 === $post_id ) {
				update_option( 'socialsnap_homepage_share_count_' . $network, intval( $count ) );
			} elseif ( $post_id > 0 ) {
				update_post_meta( $post_id, 'ss_ss_share_count_' . $network, intval( $count ) );
			}

		} else {
			$count = $saved;
		}

		$result[ $network ] = socialsnap_format_number( $count );
	}

	// Set expiration timestamp.
	if ( -1 === $post_id ) {
		update_option( 'socialsnap_homepage_share_count_timestamp', date( 'Y-m-d H:i:s' ) );
	} elseif ( $post_id > 0 ) {
		update_post_meta( $post_id, 'socialsnap_share_count_timestamp', date( 'Y-m-d H:i:s' ) );
	}

	wp_send_json_success( array( 'result' => $result ) );
}
