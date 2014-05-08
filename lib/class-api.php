<?php
namespace UsabilityDynamics\wpElastic {

  if( !class_exists( 'UsabilityDynamics\wpElastic\API' ) ) {

    class API {

      static public function postSettings( $data ) {

      }

      static public function getStatus( $data ) {

      }

      static public function getSettings( $data ) {

        return wp_send_json(array(
          'ok' => true,
          'message' => __( 'Returning wpElastic settings.', wp_elastic()->get( 'domain' ) ),
          'settings' => wp_elastic()->get()
        ));

      }

      static public function reIndex( $data ) {


      }

    }

  }

}