<?php
/**
 * Class CPT_Lowry_Webinars
 *
 * Creates the post type.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class CPT_Lowry_Webinars extends RBM_CPT {

	public $post_type = 'webinar';
	public $label_singular = null;
	public $label_plural = null;
	public $labels = array();
	public $icon = 'video-alt2';
	public $post_args = array(
		'hierarchical' => true,
		'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
		'has_archive' => true,
		'rewrite' => array(
			'slug' => 'webinar',
			'with_front' => false,
			'feeds' => false,
			'pages' => true
		),
		'menu_position' => 11,
		'capability_type' => 'webinar',
	);

	/**
	 * CPT_Lowry_Webinars constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// This allows us to Localize the Labels
		$this->label_singular = __( 'Webinar', 'lowry-cpt-webinars' );
		$this->label_plural = __( 'Webinars', 'lowry-cpt-webinars' );

		$this->labels = array(
			'menu_name' => __( 'Webinars', 'lowry-cpt-webinars' ),
			'all_items' => __( 'All Webinars', 'lowry-cpt-webinars' ),
		);

		parent::__construct();
		
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		
		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'admin_column_add' ) );
		
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'admin_column_display' ), 10, 2 );
		
	}
	
	/**
	 * Add Meta Box
	 * 
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		
		add_meta_box(
			'webinar-download-url',
			sprintf( _x( '%s Meta', 'Metabox Title', 'lowry-cpt-webinars' ), $this->label_singular ),
			array( $this, 'metabox_content' ),
			$this->post_type,
			'normal'
		);
		
	}
	
	/**
	 * Add Meta Field
	 * 
	 * @since 1.0.0
	 */
	public function metabox_content() {
		
		rbm_do_field_text(
			'webinar_url',
			_x( 'Webinar URL', 'Webinar URL Label', 'lowry-cpt-webinars' ),
			false,
			array(
				'description' => __( 'The URL to download this asset, or the landing page URL.', 'lowry-cpt-webinars' ),
			)
		);
		
		rbm_do_field_text(
			'webinar_embed_code',
			_x( 'Webinar Embed Code', 'Webinar Embed Code Label', 'lowry-cpt-webinars' ),
			false,
			array(
				'description' => __( 'For webinars, add the Webinar Embed code here (e.g iframe)', 'lowry-cpt-webinars' ),
			)
		);
		
		rbm_fh_init_field_group( 'default' );
		
	}
	
	/**
	 * Adds an Admin Column
	 * @param  array $columns Array of Admin Columns
	 * @return array Modified Admin Column Array
	 */
	public function admin_column_add( $columns ) {
		
		$columns['webinar_url'] = _x( 'Webinar URL', 'Webinar URL Admin Column Label', 'lowry-cpt-webinars' );
		
		return $columns;
		
	}
	
	/**
	 * Displays data within Admin Columns
	 * @param string $column  Admin Column ID
	 * @param integer $post_id Post ID
	 */
	public function admin_column_display( $column, $post_id ) {
		
		switch ( $column ) {
				
			case 'webinar_url' :
				echo rbm_field( $column, $post_id );
				break;
				
		}
		
	}
	
}