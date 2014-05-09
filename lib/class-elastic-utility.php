<?php
namespace UsabilityDynamics\wpElastic {

  if( !class_exists( 'UsabilityDynamics' ) ) {

    class Utility extends \UsabilityDynamics\Utility {

      /**
       * Load Schemas.
       *
       * @method load_schemas
       * @param null $path
       * @return array
       */
      static public function load_schemas( $path = null ) {

        $_result = array();

        foreach( (array) explode( ';', $path || '' ) as $_path ) {

          if( !$_path || !is_dir( $_path ) ) {
            $_result[] = new \WP_Error( __( 'Unable to load defaults, directory does not exist.' ) );
          }

          if ($handle = opendir( $path )) {

            while (false !== ($entry = readdir($handle))) {

              if( $entry === '.' || $entry === '..' ) {
                continue;
              }

              $_result[] = \UsabilityDynamics\wpElastic::define( str_replace( '.json', '', $entry ), file_get_contents( trailingslashit( $path  ) . $entry ) );

            }

            closedir( $handle );

          }

        }

        return $_result;

      }

      static public function indexName( $data ) {
        return self::create_slug( $data );
      }
    }

  }

}