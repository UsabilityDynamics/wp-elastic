<?php
namespace elasticsearch;

add_action( 'nhp-opts-options-validate-elasticsearch', function ( $new, $current ) {
  global $NHP_Options;

  if( $new[ 'server_url' ] == $current[ 'server_url' ] && $new[ 'server_auth' ] == $current[ 'server_auth' ] && $new[ 'server_index' ] == $current[ 'server_index' ] ) {
    return;
  }

  if( $new[ 'server_url' ] ) {

    $_server_url = trailingslashit( $new[ 'server_url' ] );

    if( $new[ 'server_auth' ] ) {
      $_server_url = $new[ 'server_auth' ] . '@' . $_server_url;
    }

    $client = new \Elastica\Client( array(
      'url' => $_server_url
    ) );

    try {
      $index  = $client->getIndex( $new[ 'server_index' ] );
      $status = $index->getStatus()->getResponse()->getData();
    } catch( \Exception $ex ) {

    }

    if( !( isset( $status[ 'ok' ] ) && $status[ 'ok' ] ) ) {
      $field          = $NHP_Options->sections[ 'server' ][ 'fields' ][ 'server_url' ];
      $field[ 'msg' ] = 'Unable to connect to the ElasticSearch server.';

      $NHP_Options->errors[ ] = $field;

      set_transient( 'nhp-opts-errors-elasticsearch', $NHP_Options->errors, 1000 );
    }
  }

}, 10, 2 );

$sections[ 'server' ] = array(
  'icon'   => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_280_settings.png',
  'title'  => 'Server Settings',
  'fields' => array(
    'server_url'           => array(
      'id'       => 'server_url',
      'type'     => 'text',
      'title'    => 'Server URL',
      'sub_desc' => 'If your search provider has given you a connection URL, use that instead of filling out server information.',
      'desc'     => 'URL or hostname of server.'
    ),
    'server_auth'          => array(
      'id'    => 'server_auth',
      'type'  => 'text',
      'title' => 'Basic Authentication',
      'desc'  => 'For Basic Authentication enter username:password.'
    ),
    'server_index'         => array(
      'id'    => 'server_index',
      'type'  => 'text',
      'title' => 'Index Name',
      'desc'  => 'If not specified, site domain will be used.'
    ),
    'server_options'       => array(
      'id'       => 'server_options',
      'type'     => 'multi_checkbox',
      'title'    => 'Request Batching',
      'sub_desc' => 'Enable to utilize /_bulk, /_mget and /_msearch to batch requests when possible.',
      'options'  => array(
        "use_bulk"      => "Batch document creation, updating and deletion using /_bulk.",
        "use_mget"      => "Batch document retrieval using /_mget.",
        "use_msearch"   => "Batch search requests /_msearch.",
        "use_full_path" => "Include _index and _type in request paths instead of root paths.",
      )
    ),
    'server_timeout_read'  => array(
      'id'       => 'server_timeout_read',
      'type'     => 'text',
      'title'    => 'Read Timeout',
      'validate' => 'numeric',
      'std'      => 1,
      'desc'     => 'Number of seconds (minimum of 1)',
      'sub_desc' => 'The maximum time (in seconds) that <strong>read</strong> requests should wait for server response. If the call times out, wordpress will fallback to standard search.'
    ),
    'server_timeout_write' => array(
      'id'       => 'server_timeout_write',
      'type'     => 'text',
      'title'    => 'Write Timeout',
      'validate' => 'numeric',
      'std'      => 300,
      'desc'     => 'Number of seconds (minimum of 1)',
      'sub_desc' => 'The maximum time (in seconds) that <strong>write</strong> requests should wait for server response. This should be set long enough to index your entire site.'
    ),
    /*
    'server_headers' => array(
      'id' => 'server_headers',
      'type' => 'text',
      'title' => 'Custom Headers',
      'sub_desc' => 'Add custom request headers such as authentication.',
    ),
    */
  )
);

