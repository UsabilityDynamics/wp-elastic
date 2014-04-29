<?php
/**
 * Plugin Name: WP-Elastic
 * Plugin URI: http://wordpress.org/extend/plugins/fantastic-elasticsearch/
 * Description: Improve wordpress search performance and accuracy by leveraging an ElasticSearch server.
 * Version: 2.4.0
 * Author: Usability Dynamics, Inc.
 * Author URI: http://www.linkedin.com/in/parisholley
 * Author Email: mail@parisholley.com
 * Network: true
 *
 **/

// Include bootstrap.
include_once( __DIR__ . '/lib/class-bootstrap.php' );

// Initialize.
function wp_elastic( $key = null ) {
  global $wp_elastic;

  if( !$wp_elastic ) {
    $wp_elastic = new wpElastic\Bootstrap();
  }

  return $key ? $wp_elastic->get( $key ) : $wp_elastic;

}

wp_elastic();