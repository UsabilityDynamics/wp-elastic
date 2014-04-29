<?php
 /**
  *
  */
 ?>

<section data-requires="wp-elastic.admin" class="wrap view-model">
  <h2 data-bind="text: title"></h2>

  <div data-requires="wp-elastic.mapping" class="section wp-elastic-mapping view-model">
    <h3>Mapping</h3>
  </div>

  <div data-requires="wp-elastic.settings" class="section wp-elastic-settings view-model">
    <h3>Settings</h3>
  </div>

</section>

<script id="wp-elastic-settings-script">

  // Declare dependencies.
  if( 'function' === typeof require ) {
    require( [ 'udx.utility', 'knockout' ], bindView );
  }

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

<style id="wp-elastic-settings-style">
  section.wrap h2 { color: blue; }
</style>


