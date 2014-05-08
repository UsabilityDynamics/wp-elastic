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
 * @author potanin@UD
 * @method wp_elastic
 * @return null|\UsabilityDynamics\wpElastic\Bootstrap
 */
function wp_elastic( $key = null, $default = null ) {

  // Should be autoloaded by composer autoload.php if used as a dependency of a site setup..
  if( file_exists( dirname( __DIR__ ) . '/class-bootstrap.php' ) ) {
    require_once( dirname( __DIR__ ) . '/class-bootstrap.php' );
  }

  // Either initializes wpElastic or gets the existing instance.
  $_singleton = UsabilityDynamics\wpElastic\Bootstrap::get_instance();

  // Just in case.
  if( !method_exists( $_singleton, 'get' ) ) {
    return new WP_Error( __( 'Unable to initialize wp-elastic plugin, get() method does not exist.' ) );
  }

  // Return either a key lookup or singletons
  return $key ? $_singleton->get( $key, $default ) : $_singleton;

}
