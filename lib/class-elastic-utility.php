<?php
namespace UsabilityDynamics\wpElastic {

  if( !class_exists( 'UsabilityDynamics' ) ) {

    class Utility extends \UsabilityDynamics\Utility {

      static public function load_default_schemas( $path = null ) {

        if( !$path || !is_dir( $path ) ) {
          return new \WP_Error( __( 'Unable to load defaults, directory does not exist.' ) );
        }

        if ($handle = opendir( $path )) {

          while (false !== ($entry = readdir($handle))) {

            if( $entry === '.' || $entry === '..' ) {
              continue;
            }

            \UsabilityDynamics\wpElastic::define( str_replace( '.json', '', $entry ), file_get_contents( trailingslashit( $path  ) . $entry ) );

          }

          closedir($handle);

        }


      }

      static public function indexName( $data ) {
        return self::create_slug( $data );
      }
    }

  }

}