<?php
 /**
  *
  */
 ?>

<div data-requires="wp-elastic.admin" class="wrap view-model">
  <h2 data-bind="text: title"></h2>

  <div data-requires="wp-elastic.mapping" class="section wp-elastic-mapping view-model">
    <h3>Mapping</h3>
  </div>

  <div data-requires="wp-elastic.settings" class="section wp-elastic-settings view-model">
    <h3>Settings</h3>
  </div>

</div>

<script type="text/javascript">

  // Configure udx.require.js
  require.config({
    baseUrl: '/wp-content/plugins/wp-elastic/static/scripts/',
    config: {
      ajaxurl: ajaxurl,
      view: pagenow,
      adminpage: adminpage
    }
  });

  // Declare dependencies.
  require( [ 'udx.utility', 'knockout' ], bindView );

  /**
   * Initialize View.
   *
   * @param utility
   * @param ko
   */
  function bindView( utility, ko ) {
    console.debug( 'bindView', ko.version, utility.extend );
  }

</script>

