<?php
/**
 * Plugin Name: WP-Elastic
 * Plugin URI: http://wordpress.org/extend/plugins/wp-elastic/
 * Description: Improve wordpress search performance and accuracy by leveraging an ElasticSearch server.
 * Version: 2.4.0
 * Text Domain: wp-elastic
 * Author: Usability Dynamics, Inc.
 * Author URI: http://www.usabilitydynamics.com/
 * Author Email: info@usabilitydynamics.com
 * Network: true
 *
 **/

// Include global API methods.
include_once( __DIR__ . '/api/global.php' );

// Initialize.
wp_elastic();