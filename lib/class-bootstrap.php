<?php
namespace wpElastic {

  /**
   * @property string text_domain
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
    public static $version = null;

    /**
     * Settings Instance.
     *
     * @property $_settings
     * @type {Object}
     */
    public static $text_domain = 'wp-elastic';

    /**
     * Settings Instance.
     *
     * @property $_settings
     * @type {Object}
     */
    private $_settings;

    /**
     *
     */
    function __construct() {
      global $wp_elastic;

      $wp_elastic = self::$instance = &$this;

      // self::$version = '33';
      // self::$text_domain = '33';

      $this->_settings = new \UsabilityDynamics\Settings( array(
        'store' => 'options',
        'key'   => 'wp-elastic',
      ));

      // ElasticSearch Service Settings.
      $this->set( 'documents', array(
        "active" => true,
        "host"   => "localhost",
        "port"   => 9200,
        "token"  => null,
      ));

      $this->basename = plugin_basename( dirname( __DIR__ ) . '/wp-elastic.php' );

      add_filter( 'plugin_action_links_' . $this->basename, array( 'wpElastic\Bootstrap', 'action_links' ), -10 );
      add_action( 'admin_init',             array( 'wpElastic\Bootstrap', 'admin_init' ), 20 );
      add_action( 'admin_enqueue_scripts',  array( 'wpElastic\Bootstrap', 'admin_scripts' ), 20 );

      add_action( 'deleted_user',           array( $this, 'deleted_user' ) );
      add_action( 'profile_update',         array( $this, 'user_update' ) );
      add_action( 'user_register',          array( $this, 'user_update' ) );

      add_action( 'added_user_meta',        array( $this, 'user_meta_change' ) );
      add_action( 'updated_user_meta',      array( $this, 'user_meta_change' ) );
      add_action( 'deleted_user_meta',      array( $this, 'user_meta_change' ) );

      add_action( 'save_post',              array( $this, 'save_post' ) );
      add_action( 'delete_post',            array( $this, 'delete_post' ) );
      add_action( 'trash_post',             array( $this, 'delete_post' ) );
      add_action( 'trash_post',             array( $this, 'delete_post' ) );
      add_action( 'edit_term',              array( $this, 'edit_term' ), 10, 3 );

    }

    static function action_links( $links ) {
      $links[] = '<a href="options-general.php?page=elastic_search"><b>Settings</b></a>';
      $links[] = '<a target="_blank" href="https://github.com/UsabilityDynamics/wp-elastic/wiki"><b>Documentation</b></a>';
      return $links;
    }

    static function admin_init() {
    }

    public function _admin_menu() {
      global $menu, $submenu;

      // Site Only.
      if( current_filter() === 'admin_menu' ) {

        // Remove Native Site Sections.
        // remove_submenu_page( 'index.php', 'my-sites.php' );

        // Add Network Administration.
        add_options_page( __( 'Services', self::$text_domain ), __( 'Services', self::$text_domain ), 'manage_network', 'network-policy', array( $this, 'site_settings' ) );
        add_options_page( __( 'CDN', self::$text_domain ), __( 'CDN', self::$text_domain ), 'manage_network', 'network-policy', array( $this, 'site_settings' ) );

      }

      // Network Only.
      if( current_filter() === 'network_admin_menu' ) {
        // Remove Native Network Settings.
        // remove_menu_page( 'sites.php' );
      }

      // Add Network Administration to Network and Site.
      add_submenu_page( 'settings.php', __( 'API Settings', self::$text_domain ), __( 'API Settings', self::$text_domain ), 'manage_network', 'network-dns', array( $this, 'network_settings' ) );
      add_submenu_page( 'index.php', __( 'Reports', self::$text_domain ), __( 'Reports', self::$text_domain ), 'manage_network', 'network-policy', array( $this, 'network_settings' ) );

    }

    /**
     *
     * @action admin_enqueue_scripts
     */
    static function admin_scripts() {
      wp_register_style( 'custom_wp_admin_css', plugins_url( '/wp/css/admin.css', __FILE__ ) );
      wp_enqueue_style( 'custom_wp_admin_css' );

    }

    static function deleted_user( $id, $reassign ) {

      if( !Config::option( 'sync_users' ) ) {
        return;
      }

    }

    static function user_update( $user_id ) {

      if( !Config::option( 'sync_users' ) ) {
        return;
      }

      if( $post == null || !in_array( $post->post_type, Config::types() ) ) {
        return;
      }

    }

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
     * Get Setting.
     *
     *    // Get Setting
     *    Veneer::get( 'my_key' )
     *
     * @method get
     *
     * @for Flawless
     * @author potanin@UD
     * @since 0.1.1
     */
    public static function get( $key = null, $default = null ) {
      return self::$instance->_settings ? self::$instance->_settings->get( $key, $default ) : null;
    }

    /**
     * Set Setting.
     *
     * @usage
     *
     *    // Set Setting
     *    Veneer::set( 'my_key', 'my-value' )
     *
     * @method get
     * @for Flawless
     *
     * @author potanin@UD
     * @since 0.1.1
     */
    public static function set( $key, $value = null ) {
      return self::$instance->_settings ? self::$instance->_settings->set( $key, $value ) : null;
    }

  }

}