<?php
namespace wpElastic {

  use \UsabilityDynamics\Settings;

  if( !class_exists( 'wpElastic\Bootstrap' ) ) {

    /**
     * @property string domain
     * @property string version
     */
    class Bootstrap {

      /**
       * Singleton Instance Reference.
       *
       * @public
       * @static
       * @property $instance
       * @type {Object}
       */
      public static $instance = false;

      /**
       * Settings Instance.
       *
       * @property $_settings
       * @type {Object}
       */
      public $basename = 'wp-elastic';

      /**
       * Settings Instance.
       *
       * @property $_settings
       * @type {Object}
       */
      private $_settings;

      /**
       * Pages.
       *
       * @property $_pages
       * @type {Object}
       */
      private $_pages = array();

      /**
       *
       */
      function __construct() {
        global $wp_elastic;

        $wp_elastic = self::$instance = &$this;

        // Set Essentials.
        $this->basename     = plugin_basename( dirname( __DIR__ ) . '/wp-elastic.php' );
        $this->path         = plugin_dir_path( dirname( __DIR__ ) . '/wp-elastic.php' );
        $this->url          = plugin_dir_url( dirname( __DIR__ ) . '/wp-elastic.php' );

        // Initialize Settings and set defaults.
        $this->_settings    = new Settings( array(
          'store' => 'options',
          'key'   => 'wp-elastic',
          'data'  => json_decode( file_get_contents( $this->path . 'static/schemas/wp-elastic.defaults.json' ))
        ));

        // Set Computed Options.
        $this->set( get_file_data( ( dirname( __DIR__ ) . '/wp-elastic.php' ), array(
          'name' => 'Plugin Name',
          'uri' => 'Plugin URI',
          'description' => 'Description',
          'version' => 'Version',
          'domain' => 'Text Domain'
        )));

        // Core Actions.
        add_action( 'admin_init',                 array( $this, 'admin_init' ), 20 );
        add_action( 'admin_menu',                 array( $this, 'admin_menu' ), 20 );
        add_action( 'network_admin_menu',         array( $this, 'admin_menu' ), 20 );
        add_action( 'admin_enqueue_scripts',      array( $this, 'admin_scripts' ), 20 );

        // AJAX Actions.
        add_action( 'wp_ajax_/elastic/status',    array( $this, 'ajax_handler' ), 20 );
        add_action( 'wp_ajax_/elastic/settings',  array( $this, 'ajax_handler' ), 20 );

        // Customizer Actions.
        add_action( 'customize_preview_init',     array( $this, 'customize_preview_init' ), 20 );

        // Synchroniation Filters.
        add_action( 'deleted_user',               array( $this, 'deleted_user' ) );
        add_action( 'profile_update',             array( $this, 'user_update' ) );
        add_action( 'user_register',              array( $this, 'user_update' ) );

        add_action( 'added_user_meta',            array( $this, 'user_meta_change' ) );
        add_action( 'updated_user_meta',          array( $this, 'user_meta_change' ) );
        add_action( 'deleted_user_meta',          array( $this, 'user_meta_change' ) );

        add_action( 'save_post',                  array( $this, 'save_post' ) );
        add_action( 'delete_post',                array( $this, 'delete_post' ) );
        add_action( 'trash_post',                 array( $this, 'delete_post' ) );
        add_action( 'trash_post',                 array( $this, 'delete_post' ) );
        add_action( 'edit_term',                  array( $this, 'edit_term' ), 10, 3 );

        // Utility Actions.
        add_filter( 'plugin_action_links_' . $this->basename, array( 'wpElastic\Bootstrap', 'action_links' ), -10 );

        // Upgrade Control.
        register_activation_hook( dirname( __DIR__ ) . '/wp-elastic.php',   array( 'wpElastic', 'activate' ) );
        register_deactivation_hook( dirname( __DIR__ ) . '/wp-elastic.php', array( 'wpElastic', 'deactivate' ) );

      }

      /**
       * AJAX Handler.
       *
       */
      public function ajax_handler() {

        $method   = $_SERVER[ 'REQUEST_METHOD' ];
        $action   = $_GET[ 'action' ];

        nocache_headers();

        // Get Settings.
        if( $method === 'GET' && $action === '/elastic/settings' ) {

          return wp_send_json(array(
            'ok' => true,
            'message' => __( 'Returning wpElastic settings.', $this->get( 'domain' ) ),
            'settings' => $this->get()
          ));

        }

        // Get Status.
        if( $method === 'GET' && $action === '/elastic/status' ) {

          return wp_send_json(array(
            'ok' => true,
            'message' => __( 'The wpElastic service is enabled.', $this->get( 'domain' ) )
          ));

        }

      }

      /**
       * Customizer.
       *
       */
      public function customize_live_preview() {
        wp_enqueue_script( 'wp-elastic.customizer', $this->url . 'static/scripts/wp-elastic.customizer.js', array( 'jquery', 'customize-preview' ), $this->get( 'version' ), true );
        wp_localize_script( 'wp-elastic.customizer', 'wp_elastic_customizer', $this->get() );
      }

      /**
       *
       */
      static function activate() {

      }

      /**
       *
       */
      static function deactivate() {

      }

      /**
       * @param $links
       *
       * @return array
       */
      static function action_links( $links ) {
        $links[] = '<a href="options-general.php?page=elastic_search"><b>Settings</b></a>';
        $links[] = '<a target="_blank" href="https://github.com/UsabilityDynamics/wp-elastic/wiki"><b>Documentation</b></a>';
        return $links;
      }

      /**
       *
       */
      static function admin_init() {
      }

      /**
       *
       */
      public function admin_menu() {
        global $menu, $submenu;

        // Site Only.
        if( current_filter() === 'admin_menu' ) {
          $this->_pages[ 'services' ] = add_options_page(   __( 'Services', $this->get( 'domain' ) ), __( 'Services', $this->get( 'domain' ) ), 'manage_options', 'wp-elastic-service', array( $this, 'admin_template' ) );
          $this->_pages[ 'tools' ]    = add_dashboard_page( __( 'Elastic', $this->get( 'domain' ) ),  __( 'Elastic', $this->get( 'domain' ) ),  'manage_options', 'wp-elastic-tools',   array( $this, 'admin_template' ) );
        }

        // Network Only.
        if( current_filter() === 'network_admin_menu' ) {
          $this->_pages[ 'services' ] = add_options_page( __( 'Services', $this->get( 'domain' ) ), __( 'Services', $this->get( 'domain' ) ), 'manage_options', 'wp-elastic-service', array( $this, 'admin_template' ) );
          $this->_pages[ 'reports' ]  = add_submenu_page( 'index.php', __( 'Reports', $this->get( 'domain' ) ), __( 'Reports', $this->get( 'domain' ) ), 'manage_options', 'wp-elastic-reports', array( $this, 'admin_template' ) );
        }

      }

      /**
       * Load Admin Template.
       *
       */
      public function admin_template() {

        $_path = $this->path . 'static/views/' . str_replace( array( 'dashboard_page_', 'plugins_page_', 'settings_page_', 'tools_page_'  ), '', get_current_screen()->id ) . '.php';

        if( file_exists( $_path ) ) {
          include( $_path );
        }

      }

      /**
       *
       * @action admin_enqueue_scripts
       */
      public function admin_scripts() {

        // Register Libraies and Styles..
        wp_register_script( 'udx-requires',         '//cdn.udx.io/udx.requires.js', array(), $this->get( 'version' ), false  );
        wp_enqueue_style( 'wp-elastic', $this->url . '/static/styles/wp-elastic.css', array(), $this->get( 'version' ), 'all' );

        //wp_register_script( 'wp-elastic.admin',     $this->url . '/static/scripts/wp-elastic.admin.js',     array( 'udx-requires' ),  $this->get( 'version' ), true );
        //wp_register_script( 'wp-elastic.mapping',   $this->url . '/static/scripts/wp-elastic.mapping.js',   array( 'udx-requires' ),  $this->get( 'version' ), true );
        //wp_register_script( 'wp-elastic.settings',  $this->url . '/static/scripts/wp-elastic.settings.js',  array( 'udx-requires' ),  $this->get( 'version' ), true );

        if( in_array( get_current_screen()->id, $this->_pages ) ) {
          wp_enqueue_script( 'udx-requires' );
        }

        // wp_localize_script( 'udx-requires', 'wpElastic', $this->get() );

      }

      /**
       * @param $id
       * @param $reassign
       */
      static function deleted_user( $id, $reassign ) {

        if( !Config::option( 'sync_users' ) ) {
          return;
        }

      }

      /**
       * @param $user_id
       */
      static function user_update( $user_id ) {

        if( !Config::option( 'sync_users' ) ) {
          return;
        }

        if( $post == null || !in_array( $post->post_type, Config::types() ) ) {
          return;
        }

      }

      /**
       * @param $meta_id
       * @param $object_id
       * @param $meta_key
       * @param $_meta_value
       */
      static function user_meta_change( $meta_id, $object_id, $meta_key, $_meta_value ) {

        if( !Config::option( 'sync_users' ) ) {
          return;
        }

        if( doing_filter( 'added_user_meta' ) ) {}
        if( doing_filter( 'updated_user_meta' ) ) {}
        if( doing_filter( 'deleted_user_meta' ) ) {}

      }

      /**
       * Index Terms
       *
       * @author potanin@UD
       *
       * @param $term_id
       * @param $tt_id
       * @param $taxonomy
       */
      static function edit_term( $term_id, $tt_id, $taxonomy ) {

        return;

      }

      /**
       * @param $post_id
       */
      static function save_post( $post_id ) {

        $post = is_object( $post_id ) ? $post_id : get_post( $post_id );

        if( $post == null || !in_array( $post->post_type, Config::types() ) ) {
          return;
        }

        if( $post->post_status == 'trash' ) {
          Indexer::delete( $post );
        }

        if( $post->post_status == 'publish' ) {
          Indexer::addOrUpdate( $post );
        }

      }

      /**
       * @param $post_id
       */
      static function delete_post( $post_id ) {
        if( is_object( $post_id ) ) {
          $post = $post_id;
        } else {
          $post = get_post( $post_id );
        }

        if( $post == null || !in_array( $post->post_type, Config::types() ) ) {
          return;
        }

        Indexer::delete( $post );
      }

      /**
       * Determine if instance already exists and Return Theme Instance
       *
       */
      public static function get_instance( $args = array() ) {
        return null === self::$instance ? self::$instance = new self() : self::$instance;
      }

      /**
       * @param null $key
       * @param null $value
       *
       * @return \UsabilityDynamics\Settings
       */
      public function set( $key = null, $value = null ) {
        return $this->_settings->set( $key, $value );
      }

      /**
       * @param null $key
       * @param null $default
       *
       * @return \UsabilityDynamics\type
       */
      public function get( $key = null, $default = null ) {
        return $this->_settings->get( $key, $default );
      }

  }
  }

}