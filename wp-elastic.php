<?php
/**
 * Plugin Name: WP-Elastic
 * Plugin URI: http://wordpress.org/extend/plugins/wp-elastic/
 * Description: Improve wordpress search performance and accuracy by leveraging an ElasticSearch server.
 * Version: 2.4.0
 * Author: Usability Dynamics, Inc.
 * Author URI: http://www.usabilitydynamics.com/
 * Author Email: info@usabilitydynamics.com
 * Network: true
 *
 **/

// Include bootstrap.
include_once( __DIR__ . '/lib/class-bootstrap.php' );

/**
 * wp_elastic handler
 *
 * @param null $key
 * @return null|\wpElastic\Bootstrap
 */
function wp_elastic( $key = null, $default = null ) {
  global $wp_elastic;

  if( !$wp_elastic ) {
    new wpElastic\Bootstrap();
  }

  return $key ? $wp_elastic->get( $key, $default ) : $wp_elastic;

}

// Initialize.
wp_elastic();