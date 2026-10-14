<?php
  function elysian_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
  }
  add_action('after_setup_theme', 'elysian_theme_setup');

  add_image_size( 'full-width-image', 1440, 720, true );
  add_image_size( 'image-160-160', 160, 160, true );
