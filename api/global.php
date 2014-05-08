<?php
/**
 * Globally Included Functions
 *
 */

/**
 * wp_elastic handler
 *
 * @param null $key
 * @param null $default
 *
 * @return null|\wpElastic\Bootstrap
 */
function wp_elastic( $key = null, $default = null ) {
  global $wp_elastic;

  if( !$wp_elastic && file_exists( dirname( __DIR__ ) . '/lib/class-bootstrap.php' ) ) {
    require_once( dirname( __DIR__ ) . '/lib/class-bootstrap.php' );
    $wp_elastic = new wpElastic\Bootstrap();
  }

  if( method_exists( $wp_elastic, 'get' ) ) {
    return $key ? $wp_elastic->get( $key, $default ) : $wp_elastic;
  }

  return new WP_Error( __( 'Unable to initialize wp-elastic plugin.' ) );

}
