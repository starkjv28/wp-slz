<?php
/**
 * Add a new column to admin columns with share counts.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.1.5
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2019, Social Snap LLC
*/
class SocialSnap_Post_List_Table {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.1.5
	 */
	public function __construct() {

		// Add column.
        add_filter( 'manage_post_posts_columns', array( $this, 'add_column' ) );
        add_filter( 'manage_page_posts_columns', array( $this, 'add_column' ) );

		// Print content to column.
        add_action( 'manage_posts_custom_column',      array( $this, 'print_content' ), 10, 2 );
    	add_action( 'manage_page_posts_custom_column', array( $this, 'print_content' ), 10, 2 );

		// Set sortable.
    	add_filter( 'manage_edit-post_sortable_columns', array( $this, 'sortable' ) );
    	add_filter( 'manage_edit-page_sortable_columns', array( $this, 'sortable' ) );

        add_action( 'pre_get_posts', array( $this, 'orderby' ) );
	}

	/**
	 * Add custom column to post list admin page.
	 *
	 * @since  1.1.5
	 * @param  array $defaults The default columns registered with WordPress.
	 * @return array           The array modified with our new column.
	 */
	public function add_column( $defaults ) {
		$defaults['ss_social_shares'] = 'Shares';
		return $defaults;
	}

	/**
	 * Print content to custom column.
	 *
	 * @since  1.1.5
	 * @param  string $column_name Column to be modified.
	 * @param  int    $post_ID     Post ID.
	 * @return void
	 */
	public function print_content( $column_name, $post_ID ) {

		if ( $column_name !== 'ss_social_shares' ) {
			return;
		}

		// Get the share count, format it, echo it to the screen.
		$count = get_post_meta( $post_ID , 'ss_total_share_count' , true );
		if ( ! empty( $count ) ) {
			echo socialsnap_format_number( $count );
			return;
		}

		echo 0;
	}

	/**
	 * Sortable column.
	 *
	 * @since  1.1.5
	 * @param  array The array of registered columns.
	 * @return array The array modified columns.
	 */
    public function sortable( $columns ) {
    	$columns['ss_social_shares'] = 'Shares';
    	return $columns;
    }

	/**
	* Sort the column by share count.
	*
	* @since  1.1.5.
	* @param  object $query The WordPress query object.
	* @return void
	*
	*/
	public function orderby( $query ) {

		if ( ! is_admin() ) {
	 		return;
	 	}

		if ( 'Shares' !== $query->get( 'orderby' ) ) {
			return;
		}

		$query->set( 'meta_key', 'ss_total_share_count' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
new SocialSnap_Post_List_Table();
