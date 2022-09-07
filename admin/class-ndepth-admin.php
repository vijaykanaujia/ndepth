<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       vijay.thoughtsole.in
 * @since      1.0.0
 *
 * @package    Ndepth
 * @subpackage Ndepth/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ndepth
 * @subpackage Ndepth/admin
 * @author     vijay kanaujia <vijaykanaujia3@gmail.com>
 */
class Ndepth_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ndepth_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ndepth_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ndepth-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style($this->plugin_name . "-select2", plugin_dir_url(__FILE__) . 'css/select2.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ndepth_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ndepth_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ndepth-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script($this->plugin_name . "-select2", plugin_dir_url(__FILE__) . 'js/select2.js', array(), $this->version, false);

	}
	
	/**
	 * Register the top menu for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_top_menu()
	{
		add_menu_page(
			esc_html__('Ndepth', 'ndepth'),
			esc_html__('Ndepth', 'ndepth'),
			'manage_options',
			plugin_dir_path(__FILE__) . 'partials/ndepth-admin-display.php',
			null,
			plugin_dir_url(__FILE__) . 'images/ndepth.png',
			3
		);
	}

	public function admin_sub_menu()
	{
		add_submenu_page(
			plugin_dir_path(__FILE__) . 'partials/ndepth-admin-display.php',
			esc_html__( 'Add Data', 'ndepth' ),
			esc_html__( 'Add Data', 'ndepth'),
			'manage_options',
			plugin_dir_path(__FILE__) . 'partials/ndepth-admin-add.php',
			null
		);
	}

	public function load_wp_media_files($page)
	{
		// change to the $page where you want to enqueue the script
		if ($page == 'ndepth/admin/partials/ndepth-admin-add.php') {
			// Enqueue WordPress media scripts
			wp_enqueue_media();
		}
	}

	public function ndepth_get_refresh_image()
	{
		if (isset($_GET['id'])) {
			$image = wp_get_attachment_image(filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT), 'medium', false, array('id' => 'ndepth-preview-image'));
			$data = array(
				'image'    => $image,
			);
			wp_send_json_success($data);
		} else {
			wp_send_json_error();
		}
	}

	//get all pages list for select2 library
	public function ndepth_get_parent_list(){
		global $wpdb;
		$table = $wpdb->prefix.'ndepth';
		$q = isset($_GET['q']) ? $_GET['q'] : '';
		$parents = $wpdb->get_results('SELECT * FROM '.$table.' WHERE `name` LIKE "%'.$q.'%"');
		// $pages = get_posts( $param );
		$list = [];
		foreach ( $parents as $page ) {
			$list[] = [
					'id' => $page->id,
					'text' => $page->name
				];
		}
		wp_send_json_success($list);
	}

}
