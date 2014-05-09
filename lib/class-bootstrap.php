<?php
namespace UsabilityDynamics\wpElastic {

  use UsabilityDynamics;

  if( !class_exists( 'UsabilityDynamics\wpElastic\Bootstrap' ) ) {

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
      public static $instance = null;

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

        // Set singleton instance.
        self::$instance = &$this;

        try {

          if( !class_exists( 'wpElastic\Utility' ) ) {
            require_once( 'class-utility.php' );
          }

          if( !class_exists( 'wpElastic\Settings' ) ) {
            require_once( 'class-settings.php' );
          }

          // Set Essentials.
          $this->file         = wp_normalize_path( dirname( __DIR__ ) . '/wp-elastic.php' );
          $this->basename     = plugin_basename( dirname( __DIR__ ) . '/wp-elastic.php' );
          $this->path         = plugin_dir_path( dirname( __DIR__ ) . '/wp-elastic.php' );
          $this->url          = plugins_url( '', dirname( __DIR__ ) );
          $this->relative     = str_replace( trailingslashit( WP_PLUGIN_DIR ), '', $this->file );

          // Initialize Settings and set defaults.
          $this->_settings = new Settings( array(
            'store' => 'site_meta',
            'key'   => 'wp-elastic'
          ));

          // Initialize Settings and set defaults.
          $this->_transient = new Settings( array(
            'store' => 'site_transient',
            'expiration' => 60,
            'key'   => 'wp-elastic',
          ));

          // Set Computed Options.
          $this->set( get_file_data( ( dirname( __DIR__ ) . '/wp-elastic.php' ), array(
            'name' => 'Plugin Name',
            'uri' => 'Plugin URI',
            'description' => 'Description',
            'version' => 'Version',
            'locale' => 'Text Domain'
          )));

          // Define runtime directory paths.
          $this->set( '__dir', array(
            'cache'   => defined( 'WP_ELASTIC_CACHE_DIR' )  ? WP_ELASTIC_CACHE_DIR    : dirname( __DIR__ ) . '/static/cache',
            'schemas' => defined( 'WP_ELASTIC_SCHEMAS_DIR' ) ? WP_ELASTIC_SCHEMAS_DIR : dirname( __DIR__ ) . '/static/schemas',
            'scripts' => dirname( __DIR__ ) . '/static/scripts',
            'styles'  => dirname( __DIR__ ) . '/static/styles',
            'views'   => dirname( __DIR__ ) . '/static/views'
          ));

          // @note Temporary until options UI is ready.
          $this->set( 'options', array(
            'load_default_schemas' => true
          ));

          $this->checkDependencies();

        } catch( Exception $e ) {
          _doing_it_wrong( 'wpElastic\Bootstrap::__construct', $e->getMessage(), '1.0.1' );
          return new \WP_Error( $e->getMessage() );
        }

        // Upgrade Control.
        register_uninstall_hook(    dirname( __DIR__ ) . '/wp-elastic.php',   array( 'wpElastic', 'uninstall' ) );
        register_activation_hook(   dirname( __DIR__ ) . '/wp-elastic.php',   array( 'wpElastic', 'activate' ) );
        register_deactivation_hook( dirname( __DIR__ ) . '/wp-elastic.php',   array( 'wpElastic', 'deactivate' ) );

        // Core Actions.
        add_action( 'init',                           array( $this, 'init' ), 20 );
        add_action( 'admin_init',                     array( $this, 'admin_init' ), 20 );
        add_action( 'admin_menu',                     array( $this, 'admin_menu' ), 20 );
        add_action( 'network_admin_menu',             array( $this, 'admin_menu' ), 20 );
        add_action( 'wp_before_admin_bar_render',     array( $this, 'toolbar' ), 10 );
        add_action( 'admin_enqueue_scripts',          array( $this, 'enqueue_scripts' ), 20 );
        add_action( 'wp_enqueue_scripts',             array( $this, 'enqueue_scripts' ), 20 );

        // AJAX Actions.
        add_action( 'wp_ajax_/wp-elastic/status',     array( $this, 'api_router' ), 100 );
        add_action( 'wp_ajax_/wp-elastic/mapping',    array( $this, 'api_router' ), 100 );
        add_action( 'wp_ajax_/wp-elastic/settings',   array( $this, 'api_router' ), 100 );
        add_action( 'wp_ajax_/wp-elastic/service',    array( $this, 'api_router' ), 100 );
        add_action( 'wp_ajax_/wp-elastic/search',     array( $this, 'api_router' ), 100 );

        // Customizer Actions.
        add_action( 'customize_preview_init',         array( $this, 'customize_preview_init' ), 10 );

        // Utility Actions.
        add_filter( 'plugin_action_links_' . $this->basename, array( 'UsabilityDynamics\wpElastic\Bootstrap', 'action_links' ), -10 );

        // Synchroniation Filters.
        add_action( 'deleted_user',                   array( $this, 'deleted_user' ) );
        add_action( 'profile_update',                 array( $this, 'user_update' ) );
        add_action( 'user_register',                  array( $this, 'user_update' ) );
        add_action( 'added_user_meta',                array( $this, 'user_meta_change' ) );
        add_action( 'updated_user_meta',              array( $this, 'user_meta_change' ) );
        add_action( 'deleted_user_meta',              array( $this, 'user_meta_change' ) );
        add_action( 'save_post',                      array( $this, 'save_post' ) );
        add_action( 'delete_post',                    array( $this, 'delete_post' ) );
        add_action( 'trash_post',                     array( $this, 'delete_post' ) );
        add_action( 'trash_post',                     array( $this, 'delete_post' ) );
        add_action( 'edit_term',                      array( $this, 'edit_term' ), 10, 3 );

        // Constant Overrides.
        $this->set( 'service.url',          defined( 'WP_ELASTIC_SERVICE_URL' )   ? WP_ELASTIC_SERVICE_URL    : $this->get( 'service.url' ) );
        $this->set( 'service.index',        defined( 'WP_ELASTIC_SERVICE_INDEX' ) ? WP_ELASTIC_SERVICE_INDEX  : $this->get( 'service.index' ) );
        $this->set( 'service.secret_key',   defined( 'WP_ELASTIC_SECRET_KEY' )    ? WP_ELASTIC_SECRET_KEY     : $this->get( 'service.secret_key' ) );
        $this->set( 'service.public_key',   defined( 'WP_ELASTIC_PUBLIC_KEY' )    ? WP_ELASTIC_PUBLIC_KEY     : $this->get( 'service.public_key' ) );
        $this->set( 'api.access_token',     defined( 'WP_ELASTIC_ACCESS_TOKEN' )  ? WP_ELASTIC_ACCESS_TOKEN   : $this->get( 'api.access_token' ) );

      }

      /**
       * Intialize Models
       *
       * @author potanin@UD
       * @method init
       */
      public function init() {

        if( $this->get( 'options.load_default_schemas' ) ) {
          Utility::load_default_schemas( $this->get( '__dir.schemas' ) );
        }
        // self::activate();
        // self::getSchema( 'sadf' );

        return;

        Service::push( 'asdf' );
        Service::push( array( 'sdaf' => 'asdfs' ) );
        Service::push( 'asdf' );

        die( '<pre>' . print_r( Service::getQueue(), true ) . '</pre>' );
        // die( '<pre>' . print_r( $this->get(), true ) . '</pre>' );
        // $this->push = Service;

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
       * @todo Use lib-api or lib-rest to declare.
       *
       */
      public function api_router() {

        $method   = $_SERVER[ 'REQUEST_METHOD' ];
        $action   = $_GET[ 'action' ];
        $payload  = isset( $_POST[ 'data' ] ) ? $_POST[ 'data' ] : array();

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
        // wp_enqueue_script( 'wp-elastic.customizer', $this->url . 'static/scripts/wp-elastic.customizer.js', array( 'jquery', 'customize-preview' ), $this->get( 'version' ), true );
        // wp_localize_script( 'wp-elastic.customizer', 'wp_elastic_customizer', $this->get() );
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

        // wp_cache_add( 'key1', 'blah', 'balls' );
        // global $wp_object_cache;
        // die( '<pre>' . print_r( $wp_object_cache, true ) . '</pre>' );

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
          // return;
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
      public function enqueue_scripts() {

        // Register Libraies.
        wp_register_script( 'udx-requires',         '//cdn.udx.io/udx.requires.js', array(), $this->get( 'version' ), false );
        wp_register_script( 'wp-elastic.admin',     plugins_url( '/static/scripts/wp-elastic.admin.js', dirname( __DIR__ ) ),     array( 'udx-requires' ),  $this->get( 'version' ), true );
        wp_register_script( 'wp-elastic.mapping',   plugins_url( '/static/scripts/wp-elastic.mapping.js', dirname( __DIR__ ) ),   array( 'udx-requires' ),  $this->get( 'version' ), true );
        wp_register_script( 'wp-elastic.settings',  plugins_url( '/static/scripts/wp-elastic.settings.js', dirname( __DIR__ ) ),  array( 'udx-requires' ),  $this->get( 'version' ), true );

        // Register Styles.
        wp_register_style( 'wp-elastic.toolbar',    plugins_url( '/static/styles/wp-elastic.toolbar.css', dirname( __DIR__ ) ),   array(), $this->get( 'version' ), 'all' );
        wp_register_style( 'wp-elastic',            plugins_url( '/static/styles/wp-elastic.css', dirname( __DIR__ ) ),           array(), $this->get( 'version' ), 'all' );

        // Include udx.requires on all wp-elastic pages.
        if( current_filter() === 'admin_enqueue_scripts' &&  in_array( get_current_screen()->id, $this->_pages ) ) {
          wp_enqueue_script( 'udx-requires' );
          wp_enqueue_style( 'wp-elastic' );
          add_action( 'admin_print_footer_scripts', array( $this, 'admin_script_debug' ), 100 );
        }

        // Global Toolbar.
        if( is_admin_bar_showing() ) {
          wp_enqueue_style( 'wp-elastic.toolbar' );
        }

        // Frontend Scripts.
        if( current_filter() === 'wp_enqueue_scripts' && is_admin_bar_showing() ) {}

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
      public function deleted_user( $id, $reassign ) {

        if( !Config::option( 'sync_users' ) ) {
          return;
        }

      }

      /**
       * @param $user_id
       */
      public function user_update( $user_id ) {

        if( !Config::option( 'sync_users' ) ) {
          return;
        }

        if( $post == null || !in_array( $post->post_type, $this->get( 'types' ) ) ) {
          return;
        }

      }

      /**
       * @param $meta_id
       * @param $object_id
       * @param $meta_key
       * @param $_meta_value
       */
      public function user_meta_change( $meta_id, $object_id, $meta_key, $_meta_value ) {

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
      public function edit_term( $term_id, $tt_id, $taxonomy ) {

        return;

      }

      /**
       * @param $post_id
       */
      public function save_post( $post_id ) {

        $post = is_object( $post_id ) ? $post_id : get_post( $post_id );

        return;

        if( $post == null || !in_array( $post->post_type, $this->get( 'types' ) ) ) {
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
      public function delete_post( $post_id ) {
        if( is_object( $post_id ) ) {
          $post = $post_id;
        } else {
          $post = get_post( $post_id );
        }

        if( $post == null || !in_array( $post->post_type, $this->get( 'types' ) ) ) {
          return;
        }

        Indexer::delete( $post );
      }

      /**
       * Set Defaults on Activation.
       *
       * @author potanin@UD
       * @method activate
       */
      static public function activate() {

        // Initialize bootstrap.
        $instance = new Bootstrap;

        // $defaults = json_decode( file_get_contents( $this->path . 'static/schemas/wp-elastic.defaults.json' ));

        // Set Defaults.
        if( !$instance->get( '_installed' ) ) {
          $instance->set( array() );
        }

        $instance->set( '_installed',   true );
        $instance->set( '_status',      'active' );
        $instance->set( '_activated',   time() );

        if( !is_dir( dirname( __DIR__ ) . '/static/cache' ) ) {
          wp_mkdir_p( dirname( __DIR__ ) . '/static/cache' );
        }

        // Save Settings on activation.
        $instance->_settings->commit();

      }

      /**
       * Set Inactive Statuf Flag on Deactivation.
       *
       * @author potanin@UD
       * @method deactivate
       */
      static public function deactivate() {

        $instance = new wpElatic;

        $instance->set( '_status', 'inactive' );

        $instance->_settings->commit();

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
       * Determine if instance already exists and Return Theme Instance
       *
       */
      public static function get_instance( $args = array() ) {
        return ( null === self::$instance ) ? new Bootstrap() : self::$instance;
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