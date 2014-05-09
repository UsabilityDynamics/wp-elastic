<?php
/**
 * Class Customize_Editor_Control
 * Adds Editor functionality
 *
 * @author usabilitydynamics@UD
 * @see https://codex.wordpress.org/Theme_Customization_API
 * @version 0.1
 * @module UsabilityDynamics\AMD
 */
namespace UsabilityDynamics\wpElastic {
  
  if( !class_exists( 'UsabilityDynamics\wpElastic\Events' ) ) {
  
    /**
     *
     * @package UsabilityDynamics\wpElastic
     */
    class Events {

      /**
       * @param $id
       * @param $reassign
       */
      public function deleted_user( $id, $reassign ) {

        if( !wp_elastic()->get( 'options.sync_users' ) ) {
          return;
        }

      }

      /**
       * @param $user_id
       */
      public function user_update( $user_id ) {

        if( !Config::option( 'sync_users' ) ) {
          return;
        }

        if( $post == null || !in_array( $post->post_type, wp_elastic()->get( 'types' ) ) ) {
          return;
        }

      }

      /**
       * @param $meta_id
       * @param $object_id
       * @param $meta_key
       * @param $_meta_value
       */
      public function user_meta_change( $meta_id, $object_id, $meta_key, $_meta_value ) {

        if( !Config::option( 'sync_users' ) ) {
          return;
        }

        if( doing_filter( 'added_user_meta' ) ) {}
        if( doing_filter( 'updated_user_meta' ) ) {}
        if( doing_filter( 'deleted_user_meta' ) ) {}

      }

      /**
       * Index Terms
       *
       * @author potanin@UD
       *
       * @param $term_id
       * @param $tt_id
       * @param $taxonomy
       */
      public function edit_term( $term_id, $tt_id, $taxonomy ) {

        return;

      }

      /**
       * @param $post_id
       */
      public function save_post( $post_id ) {

        $post = is_object( $post_id ) ? $post_id : get_post( $post_id );

        return;

        if( $post == null || !in_array( $post->post_type, wp_elastic()->get( 'types' ) ) ) {
          return;
        }

        if( $post->post_status == 'trash' ) {
          Indexer::delete( $post );
        }

        if( $post->post_status == 'publish' ) {
          Indexer::addOrUpdate( $post );
        }

      }

      /**
       * @param $post_id
       */
      public function delete_post( $post_id ) {
        if( is_object( $post_id ) ) {
          $post = $post_id;
        } else {
          $post = get_post( $post_id );
        }

        if( $post == null || !in_array( $post->post_type, wp_elastic()->get( 'types' ) ) ) {
          return;
        }

        Indexer::delete( $post );
      }


    }
    
  }

}


      