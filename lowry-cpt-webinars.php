<?php
/**
 * Plugin Name: CPT Webinars
 * Plugin URI: https://github.com/lowrysolutions/cpt-webinars
 * Description: Creates the "Webinars" Custom Post Type
 * Version: 1.0.2
 * Text Domain: lowry-cpt-webinars
 * Author: Eric Defore
 * Author URI: http://realbigmarketing.com/
 * Contributors: d4mation
 * GitHub Plugin URI: lowrysolutions/cpt-webinars
 * GitHub Branch: master
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Lowry_CPT_Webinars' ) ) {

	/**
	 * Main Lowry_CPT_Webinars class
	 *
	 * @since	  1.0.0
	 */
	class Lowry_CPT_Webinars {
		
		/**
		 * @var			Lowry_CPT_Webinars $plugin_data Holds Plugin Header Info
		 * @since		1.0.0
		 */
		public $plugin_data;
		
		/**
		 * @var			Lowry_CPT_Webinars $admin_errors Stores all our Admin Errors to fire at once
		 * @since		1.0.0
		 */
		private $admin_errors;
		
		/**
		 * @var			Lowry_CPT_Webinars $cpt Holds the CPT
		 * @since		1.0.0
		 */
		public $cpt;

		/**
		 * Get active instance
		 *
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  object self::$instance The one true Lowry_CPT_Webinars
		 */
		public static function instance() {
			
			static $instance = null;
			
			if ( null === $instance ) {
				$instance = new static();
			}
			
			return $instance;

		}
		
		protected function __construct() {
			
			$this->setup_constants();
			$this->load_textdomain();
			
			if ( version_compare( get_bloginfo( 'version' ), '4.4' ) < 0 ) {
				
				$this->admin_errors[] = sprintf( _x( '%s requires v%s of %s or higher to be installed!', 'Outdated Dependency Error', 'lowry-cpt-webinars' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', '4.4', '<a href="' . admin_url( 'update-core.php' ) . '"><strong>WordPress</strong></a>' );
				
				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}
				
				return false;
				
			}
			
			if ( ! class_exists( 'RBM_CPTS' ) ||
				! class_exists( 'RBM_FieldHelpers' ) ) {
				
				$this->admin_errors[] = sprintf( _x( 'To use the %s Plugin, both %s and %s must be active as either a Plugin or a Must Use Plugin!', 'Missing Dependency Error', 'lowry-cpt-webinars' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', '<a href="//github.com/realbig/rbm-field-helpers/" target="_blank">' . __( 'RBM Field Helpers', 'lowry-cpt-webinars' ) . '</a>', '<a href="//github.com/realbig/rbm-cpts/" target="_blank">' . __( 'RBM Custom Post Types', 'lowry-cpt-webinars' ) . '</a>' );
				
				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}
				
				return false;
				
			}
			
			$this->require_necessities();
			
			// Register our CSS/JS for the whole plugin
			add_action( 'init', array( $this, 'register_scripts' ) );
			
		}

		/**
		 * Setup plugin constants
		 *
		 * @access	  private
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function setup_constants() {
			
			// WP Loads things so weird. I really want this function.
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			
			// Only call this once, accessible always
			$this->plugin_data = get_plugin_data( __FILE__ );

			if ( ! defined( 'Lowry_CPT_Webinars_VER' ) ) {
				// Plugin version
				define( 'Lowry_CPT_Webinars_VER', $this->plugin_data['Version'] );
			}

			if ( ! defined( 'Lowry_CPT_Webinars_DIR' ) ) {
				// Plugin path
				define( 'Lowry_CPT_Webinars_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'Lowry_CPT_Webinars_URL' ) ) {
				// Plugin URL
				define( 'Lowry_CPT_Webinars_URL', plugin_dir_url( __FILE__ ) );
			}
			
			if ( ! defined( 'Lowry_CPT_Webinars_FILE' ) ) {
				// Plugin File
				define( 'Lowry_CPT_Webinars_FILE', __FILE__ );
			}

		}

		/**
		 * Internationalization
		 *
		 * @access	  private 
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function load_textdomain() {

			// Set filter for language directory
			$lang_dir = Lowry_CPT_Webinars_DIR . '/languages/';
			$lang_dir = apply_filters( 'lowry_cpt_webinars_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'lowry-cpt-webinars' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'lowry-cpt-webinars', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/lowry-cpt-webinars/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/lowry-cpt-webinars/ folder
				// This way translations can be overridden via the Theme/Child Theme
				load_textdomain( 'lowry-cpt-webinars', $mofile_global );
			}
			else if ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/lowry-cpt-webinars/languages/ folder
				load_textdomain( 'lowry-cpt-webinars', $mofile_local );
			}
			else {
				// Load the default language files
				load_plugin_textdomain( 'lowry-cpt-webinars', false, $lang_dir );
			}

		}
		
		/**
		 * Include different aspects of the Plugin
		 * 
		 * @access	  private
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function require_necessities() {
			
			require_once Lowry_CPT_Webinars_DIR . 'core/cpt/class-lowry-cpt-webinars.php';
			$this->cpt = new CPT_Lowry_Webinars();
			
		}
		
		/**
		 * Show admin errors.
		 * 
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  HTML
		 */
		public function admin_errors() {
			?>
			<div class="error">
				<?php foreach ( $this->admin_errors as $notice ) : ?>
					<p>
						<?php echo $notice; ?>
					</p>
				<?php endforeach; ?>
			</div>
			<?php
		}
		
		/**
		 * Register our CSS/JS to use later
		 * 
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  void
		 */
		public function register_scripts() {
			
			wp_register_style(
				'lowry-cpt-webinars',
				Lowry_CPT_Webinars_URL . 'assets/css/style.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : Lowry_CPT_Webinars_VER
			);
			
			wp_register_script(
				'lowry-cpt-webinars',
				Lowry_CPT_Webinars_URL . 'assets/js/script.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : Lowry_CPT_Webinars_VER,
				true
			);
			
			wp_localize_script( 
				'lowry-cpt-webinars',
				'lowryCPTWebinars',
				apply_filters( 'lowry_cpt_webinars_localize_script', array() )
			);
			
			wp_register_style(
				'lowry-cpt-webinars-admin',
				Lowry_CPT_Webinars_URL . 'assets/css/admin.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : Lowry_CPT_Webinars_VER
			);
			
			wp_register_script(
				'lowry-cpt-webinars-admin',
				Lowry_CPT_Webinars_URL . 'assets/js/admin.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : Lowry_CPT_Webinars_VER,
				true
			);
			
			wp_localize_script( 
				'lowry-cpt-webinars-admin',
				'lowryCPTWebinars',
				apply_filters( 'lowry_cpt_webinars_localize_admin_script', array() )
			);
			
		}
		
	}
	
} // End Class Exists Check

/**
 * The main function responsible for returning the one true Lowry_CPT_Webinars
 * instance to functions everywhere
 *
 * @since	  1.0.0
 * @return	  \Lowry_CPT_Webinars The one true Lowry_CPT_Webinars
 */
add_action( 'plugins_loaded', 'lowry_cpt_webinars_load', 999 );
function lowry_cpt_webinars_load() {

	require_once __DIR__ . '/core/lowry-cpt-webinars-functions.php';
	LOWRYCPTWEBINARS();

}
