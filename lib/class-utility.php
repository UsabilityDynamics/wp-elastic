<?php
namespace wpElastic {

  if( !class_exists( 'wpElastic\Utility' ) ) {

    class Utility extends \UsabilityDynamics\Utility {

      static public function indexName( $data ) {
        return self::create_slug( $data );
      }
    }

  }

}