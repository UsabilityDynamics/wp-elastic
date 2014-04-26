<?php
/**
 * Bootstrap WordPress Integration
 *
 */
namespace elasticsearch {

  if( !class_exists( 'elasticsearch\Bootstrap' ) ) {

    /**
     * Class Bootstrap
     *
     * @package elasticsearch
     */
    class Bootstrap {

      /**
       * Veneer core version.
       *
       * @static
       * @property $version
       * @type {Object}
       */
      public static $version = '2.2.1';

      /**
       * Textdomain String
       *
       * @public
       * @property text_domain
       * @var string
       */
      public static $text_domain = 'elasticsearch';

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
       * Absolute Path to Current Directory.
       *
       * @public
       * @static
       * @property $path
       * @type {Object}
       */
      public static $path = __DIR__;

      /**
       * Constructor.
       *
       * @method __construct
       */
      public function __construct() {

        // Initialize as WordPress Plugin.
        if( file_exists( dirname( self::$path ) . '/elasticsearch.php' ) ) {
          include_once( dirname( self::$path ) . '/elasticsearch.php' );
        }

      }

    }
  }

}