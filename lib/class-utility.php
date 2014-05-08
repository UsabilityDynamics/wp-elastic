<?php
namespace UsabilityDynamics\wpElastic {

  if( !class_exists( 'UsabilityDynamics\wpElastic' ) ) {

    class Utility extends \UsabilityDynamics\Utility {

      static public function indexName( $data ) {
        return self::create_slug( $data );
      }
    }

  }

}