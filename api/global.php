<?php
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

  if( !$wp_elastic ) {
    require_once( dirname( __DIR__ ) . '/lib/class-bootstrap.php' );
    $wp_elastic = new wpElastic\Bootstrap();
  }

  return $key ? $wp_elastic->get( $key, $default ) : $wp_elastic;

}
