<?php
namespace wpElastic {

  use \UsabilityDynamics\Settings;

  if( !class_exists( 'wpElastic\Bootstrap' ) ) {

    if( !class_exists( 'wpElastic\Utility' ) ) {
      require_once( 'class-utility.php' );
    }

    if( !class_exists( 'wpElastic\Settings' ) ) {
      require_once( 'class-settings.php' );
    }

    /**
     * @property string locale
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
       * -
       *
       * @property $basename
       * @type {Object}
       */
      public $basename = 'wp-elastic';

      /**
       * -
       *
       * @property $basename
       * @type {Object}
       */
      public $file = null;

      /**
       * -
       *
       * @property $basename
       * @type {Object}
       */
      public $path = null;

      /**
       * -
       *
       * @property $basename
       * @type {Object}
       */
      public $url = null;

      /**
       * -
       *
       * @property $basename
       * @type {Object}
       */
      public $relative = null;

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

        try {

          // Set Essentials.
          $this->file         = wp_normalize_path( dirname( __DIR__ ) . '/wp-elastic.php' );
          $this->basename     = plugin_basename( dirname( __DIR__ ) . '/wp-elastic.php' );
          $this->path         = plugin_dir_path( dirname( __DIR__ ) . '/wp-elastic.php' );
          $this->url          = plugin_dir_url( dirname( __DIR__ ) . '/wp-elastic.php' );
          $this->relative     = str_replace( trailingslashit( WP_PLUGIN_DIR ), '', $this->file );

          // Initialize Settings and set defaults.
          $this->_settings = new Settings( array(
            'store' => 'options',
            'key'   => 'wp-elastic'
          ));

          // Set Computed Options.
          $this->set( get_file_data( ( dirname( __DIR__ ) . '/wp-elastic.php' ), array(
            'name' => 'Plugin Name',
            'uri' => 'Plugin URI',
            'description' => 'Description',
            'version' => 'Version',
            'locale' => 'Text Domain'
          )));

          $this->checkDependencies();

        } catch( Exception $e ) {
          _doing_it_wrong( 'wpElastic\Bootstrap::__construct', $e->getMessage(), '1.0.1' );
          return $this;
        }

        // Core Actions.
        add_action( 'admin_init',                 array( $this, 'admin_init' ), 20 );
        add_action( 'admin_menu',                 array( $this, 'admin_menu' ), 20 );
        add_action( 'network_admin_menu',         array( $this, 'admin_menu' ), 20 );
        add_action( 'admin_enqueue_scripts',      array( $this, 'admin_scripts' ), 20 );
        add_action( 'wp_before_admin_bar_render', array( $this, 'toolbar' ), 10 );

        // AJAX Actions.
        add_action( 'wp_ajax_/elastic/status',    array( $this, 'api_router' ), 100 );
        add_action( 'wp_ajax_/elastic/mapping',   array( $this, 'api_router' ), 100 );
        add_action( 'wp_ajax_/elastic/settings',  array( $this, 'api_router' ), 100 );
        add_action( 'wp_ajax_/elastic/service',   array( $this, 'api_router' ), 100 );
        add_action( 'wp_ajax_/elastic/search',    array( $this, 'api_router' ), 100 );

        // Customizer Actions.
        add_action( 'customize_preview_init',     array( $this, 'customize_preview_init' ), 10 );

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
        register_uninstall_hook( dirname( __DIR__ ) . '/wp-elastic.php', array( 'wpElastic', 'uninstall' ) );
        register_activation_hook( dirname( __DIR__ ) . '/wp-elastic.php', array( $this, 'activate' ) );
        register_deactivation_hook( dirname( __DIR__ ) . '/wp-elastic.php', array( $this, 'deactivate' ) );

      }

      /**
       * Check Dependency Versions.
       *
       * @throws Exception
       */
      private function checkDependencies() {

        // Check UsabilityDynamics\Settings version.
        if( version_compare( '0.2.1', Settings::$version ) >= 1 ) {
          throw new Exception( __( sprintf( 'Settings library version is invalid, wpElastic requires 0.2.1 or higher, while %s is available.', Settings::$version ),  $this->get( 'locale' ) ) );
        };

        // wp_die( '<h1>' . __( 'wpElastic Critical Failure', $this->get( 'locale' ) ) . '</h1><p>' . $e->getMessage() . '</p>' );

      }

      /**
       * AJAX Handler.
       *
       */
      public function api_router() {

        $method   = $_SERVER[ 'REQUEST_METHOD' ];
        $action   = $_GET[ 'action' ];
        $payload  = $_POST[ 'data' ];

        nocache_headers();

        // Get Settings.
        if( $method === 'GET'   && $action === '/elastic/settings' ) {

          return wp_send_json(array(
            'ok' => true,
            'message' => __( 'Returning wpElastic settings.', $this->get( 'locale' ) ),
            'settings' => $this->get()
          ));

        }

        // Update Settings.
        if( $method === 'POST'  && $action === '/elastic/settings' ) {

          // Set Updated.
          $this->set( $payload );

          // Commit Settings.
          $this->_settings->commit();

          return wp_send_json(array(
            'ok' => true,
            'message' => __( 'Returning wpElastic settings.', $this->get( 'locale' ) ),
            'settings' => $this->get()
          ));

        }

        // Update Settings.
        if( $method === 'DELETE'  && $action === '/elastic/settings' ) {

          // Commit Settings.
          $this->_settings->flush();

          return wp_send_json(array(
            'ok' => true,
            'message' => __( 'Successfully flushed wpElastic settings.', $this->get( 'locale' ) ),
            'settings' => $this->get()
          ));

        }

        // Get Status.
        if( $method === 'GET'   && $action === '/elastic/status' ) {

          return wp_send_json(array(
            'ok' => true,
            'message' => __( 'The wpElastic service is enabled.', $this->get( 'locale' ) )
          ));

        }

        // Get Service Information.
        if( $method === 'GET'   && $action === '/elastic/service' ) {

          return wp_send_json(array(
            'ok' => true,
            'message' => __( 'The wpElastic service is enabled.', $this->get( 'locale' ) )
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
       * Set Defaults on Activation.
       *
       * @author potanin@UD
       * @method activate
       */
      public function activate() {

        $defaults = json_decode( file_get_contents( $this->path . 'static/schemas/wp-elastic.defaults.json' ));

        // Set Defaults.
        if( !$this->get( '_installed' ) ) {
          $this->set( $defaults );
        }

        $this->set( '_installed', true );
        $this->set( '_status', 'active' );

        // Save Settings on activation.
        $this->_settings->commit();

      }

      /**
       * Set Inactive Statuf Flag on Deactivation.
       *
       * @author potanin@UD
       * @method deactivate
       */
      public function deactivate() {

        $this->set( '_status', 'inactive' );

        $this->_settings->commit();

      }

      /**
       * Uninstall Plugin.
       *
       * Must be static.
       *
       */
      static public function uninstall() {

        // $this->set( '_status', 'uninstalled' );
        // $this->_settings->commit();

      }

      /**
       * @param $links
       *
       * @return array
       */
      public function action_links( $links ) {
        $links[] = '<a href="options-general.php?page=elastic_search"><b>Settings</b></a>';
        $links[] = '<a target="_blank" href="https://github.com/UsabilityDynamics/wp-elastic/wiki"><b>Documentation</b></a>';
        return $links;
      }

      /**
       *
       */
      public function admin_init() {
        global $wp_plugin_paths;

        // get_plugin_files( $this->relative );
        // deactivate_plugins($file, true);

      }

      /**
       * Shows Veneer Status (in dev)
       *
       * @method toolbar
       * @for Boostrap
       */
      public function toolbar() {
        global $wp_admin_bar;

        if( !$this->get( 'supports.toolbar.enabled' ) ) {
          return;
        }

        $wp_admin_bar->add_menu( array(
          'id'    => 'wp-elastic',
          'parent'    => 'top-secondary',
          'meta'  => array(
            'html'     => '<div class="wp-elastic-toolbar-info"></div>',
            'target'   => '',
            'onclick'  => '',
            'title'    => __( 'wpElastic', $this->get( 'locale' ) ),
            'tabindex' => 10,
            'class'    => 'wp-elastic-toolbar'
          ),
          'title' => __( 'wpElastic', $this->get( 'locale' ) ),
          'href'  => network_admin_url( 'admin.php?page=wp-elastic' )
        ));

        $wp_admin_bar->add_menu( array(
          'parent' => 'wp-elastic',
          'id'     => 'wp-elastic-pagespeed',
          'meta'   => array(),
          'title'  => 'PageSpeed',
          'href'   => network_admin_url( 'admin.php?page=wp-elastic#panel=cdn' )
        ));

        $wp_admin_bar->add_menu( array(
          'parent' => 'wp-elastic',
          'id'     => 'wp-elastic-cloudfront',
          'meta'   => array(),
          'title'  => 'CloudFront',
          'href'   => network_admin_url( 'admin.php?page=wp-elastic#panel=cdn' )
        ));

        $wp_admin_bar->add_menu( array(
          'parent' => 'wp-elastic',
          'id'     => 'wp-elastic-varnish',
          'meta'   => array(),
          'title'  => 'Varnish',
          'href'   => network_admin_url( 'admin.php?page=wp-elastic#panel=cdn' )
        ));

      }

      /**
       *
       */
      public function admin_menu() {
        global $menu, $submenu;

        // Site Only.
        if( current_filter() === 'admin_menu' ) {
          $this->_pages[ 'services' ] = add_options_page(   __( 'wpElastic', $this->get( 'locale' ) ),  __( 'wpElastic', $this->get( 'locale' ) ), 'manage_options', 'wp-elastic-service', array( $this, 'admin_template' ) );
          $this->_pages[ 'tools' ]    = add_dashboard_page( __( 'wpElastic', $this->get( 'locale' ) ),  __( 'wpElastic', $this->get( 'locale' ) ),  'manage_options', 'wp-elastic-tools',   array( $this, 'admin_template' ) );
        }

        // Network Only.
        if( current_filter() === 'network_admin_menu' ) {
          $this->_pages[ 'services' ] = add_options_page( __( 'wpElastic', $this->get( 'locale' ) ), __( 'wpElastic', $this->get( 'locale' ) ), 'manage_options', 'wp-elastic-service', array( $this, 'admin_template' ) );
          $this->_pages[ 'reports' ]  = add_submenu_page( 'index.php', __( 'Reports', $this->get( 'locale' ) ), __( 'Reports', $this->get( 'locale' ) ), 'manage_options', 'wp-elastic-reports', array( $this, 'admin_template' ) );
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
        wp_register_script( 'udx-requires',         '//cdn.udx.io/udx.requires.js', array(), $this->get( 'version' ), false );
        wp_register_script( 'wp-elastic.admin',     $this->url . '/static/scripts/wp-elastic.admin.js',     array( 'udx-requires' ),  $this->get( 'version' ), true );
        wp_register_script( 'wp-elastic.mapping',   $this->url . '/static/scripts/wp-elastic.mapping.js',   array( 'udx-requires' ),  $this->get( 'version' ), true );
        wp_register_script( 'wp-elastic.settings',  $this->url . '/static/scripts/wp-elastic.settings.js',  array( 'udx-requires' ),  $this->get( 'version' ), true );

        // Include udx.requires on all wp-elastic pages.
        if( in_array( get_current_screen()->id, $this->_pages ) ) {
          wp_enqueue_script( 'udx-requires' );
          wp_enqueue_style( 'wp-elastic', $this->url . '/static/styles/wp-elastic.css', array(), $this->get( 'version' ), 'all' );
          add_action( 'admin_print_footer_scripts', array( $this, 'admin_script_debug' ), 100 );
        }

        // Global Toolbar.
        wp_enqueue_style( 'wp-elastic-toolbar',     $this->url . '/static/styles/wp-elastic.toolbar.css', array(), $this->get( 'version' ), 'all' );

      }

      /**
       * Local Development
       *
       */
      static function admin_script_debug() {

        if( defined( 'WP_ELASTIC_BASEURL' ) && WP_ELASTIC_BASEURL ) {
          echo '<script>"function" === typeof require ? require.config({ "baseUrl": "' . WP_ELASTIC_BASEURL . '"}) : console.error( "wp-elastic", "udx.require.js not found" );</script>';
        }

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