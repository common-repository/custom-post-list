<?php
/**
* Plugin Name: Custom Post List
* Description: A simple plugin to display a custom post list with backend options.
* Version: 1.0.3
* License: GPL v2 or later
* Author: Satya Prakash
**/

if (!defined('ABSPATH')) {
  exit;
}

function custom_post_list_menu() {
  add_options_page(
    'Custom Post List Settings',
    'Custom Post List',
    'manage_options',
    'custom_post_list_settings',
    'custom_post_list_settings_page'
  );
}

add_action('admin_menu', 'custom_post_list_menu');

// Add backend options
function custom_post_list_settings_page() {
  ?>
  <div class="wrap">
    <h1>Custom Post List Settings</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('custom_post_list_settings_group');
      do_settings_sections('custom_post_list_settings');
      submit_button();
      ?>
    </form>
  </div>
  <?php
}

function custom_post_list_settings() {
  add_settings_section(
    'custom_post_list_settings_section',
    'Custom Post List Settings',
    'custom_post_list_settings_callback',
    'custom_post_list_settings'
  );

  add_settings_field(
    'custom_post_list_post_type',
    'Select Post Type',
    'custom_post_list_post_type_callback',
    'custom_post_list_settings',
    'custom_post_list_settings_section'
  );

  add_settings_field(
    'custom_post_list_show_date',
    'Display Date',
    'custom_post_list_show_date_callback',
    'custom_post_list_settings',
    'custom_post_list_settings_section'
  );

  add_settings_field(
    'custom_post_list_show_featured_image',
    'Display Featured Image',
    'custom_post_list_show_featured_image_callback',
    'custom_post_list_settings',
    'custom_post_list_settings_section'
  );

    add_settings_field(
    'custom_post_list_show_pagination',
    'Add Pagination',
    'custom_post_list_show_pagination_callback',
    'custom_post_list_settings',
    'custom_post_list_settings_section'
  );
 add_settings_field(
    'custom_post_list_display_author',
    'Display Author',
    'custom_post_list_display_author_callback',
    'custom_post_list_settings',
    'custom_post_list_settings_section'
  );  
  add_settings_field(
    'custom_post_list_show_no_of_posts',
    'Show No. of Blogs',
    'custom_post_list_show_no_of_posts_callback',
    'custom_post_list_settings',
    'custom_post_list_settings_section'
  );  

  register_setting('custom_post_list_settings_group', 'custom_post_list_post_type');
  register_setting('custom_post_list_settings_group', 'custom_post_list_show_date');
  register_setting('custom_post_list_settings_group', 'custom_post_list_show_featured_image');
  register_setting('custom_post_list_settings_group', 'custom_post_list_show_pagination');
  register_setting('custom_post_list_settings_group', 'custom_post_list_display_author');
  register_setting('custom_post_list_settings_group', 'custom_post_list_show_no_of_posts');
}

function custom_post_list_settings_callback() {
  echo '<p>' . esc_html__('Configure options for the Custom Post List Plugin.', 'custom-post-list') . '</p>';
}

function custom_post_list_post_type_callback() {
  $excluded_post_types = array('page', 'attachment');
  $post_types = get_post_types(['public' => true], 'objects');

  echo '<select name="custom_post_list_post_type">';
  foreach ($post_types as $post_type) {
    if (!in_array($post_type->name, $excluded_post_types)) {
      echo '<option value="' . esc_attr($post_type->name) . '" ' . selected(get_option('custom_post_list_post_type'), $post_type->name, false) . '>' . esc_html($post_type->label) . '</option>';
    }
  }
  echo '</select>';
}

function custom_post_list_show_date_callback() {
  echo '<input type="checkbox" name="custom_post_list_show_date" ' . checked(get_option('custom_post_list_show_date'), 'on', false) . '>';
}

function custom_post_list_show_featured_image_callback() {
  echo '<input type="checkbox" name="custom_post_list_show_featured_image" ' . checked(get_option('custom_post_list_show_featured_image'), 'on', false) . '>';
}

function custom_post_list_show_pagination_callback() {
  echo '<input type="checkbox" name="custom_post_list_show_pagination" ' . checked(get_option('custom_post_list_show_pagination'), 'on', false) . '>';
}

function custom_post_list_display_author_callback() {
  echo '<input type="checkbox" name="custom_post_list_display_author" ' . checked(get_option('custom_post_list_display_author'), 'on', false) . '>';
}

function custom_post_list_show_no_of_posts_callback() {
    $value = get_option('custom_post_list_show_no_of_posts', 2);
    echo '<input type="number" step="1" min="2" value="' . esc_attr($value) . '" class="small-text" name="custom_post_list_show_no_of_posts">';
}

add_action('admin_init', 'custom_post_list_settings');

function custom_post_list_shortcode($atts) {
  $atts = shortcode_atts(
    array(
      'post_type' => get_option('custom_post_list_post_type', 'post'),
      'show_date' => get_option('custom_post_list_show_date', 'off'),
      'show_featured_image' => get_option('custom_post_list_show_featured_image', 'off'),
      'show_pagination' => get_option('custom_post_list_show_pagination', 'off'),
      'display_author' => get_option('custom_post_list_display_author', 'off'),
      'posts_per_page' => get_option('custom_post_list_show_no_of_posts', 'off'),
    ),
    $atts,
    'custom_post_list'
  );

  $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

  $args = array(
    'post_type' => $atts['post_type'],
    'posts_per_page' => $atts['posts_per_page'],
    'paged' => $paged,
  );

  $query = new WP_Query($args);

    if ($query->have_posts()) {
    ob_start();
    ?>
    <div class="content-wrapper">
      <div class="blog-index">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
          <div class="post-item clearfix">
            <div class="post-body clearfix">
              <?php if ($atts['show_featured_image'] === 'on') : ?>
                 <?php $featured_img_url = esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>
                 <?php if($featured_img_url!=""): ?>
                  <div class="blog-featured-img">
                    <?php $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>
                    <img src="<?php echo esc_url($featured_img_url); ?>" alt="">
                </div>
              <?php endif; ?>
              <?php endif; ?>
            </div>
            <div class="post-list-content clearfix">
              <div class="post-heading">
                <h2>
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                  <div class="user-meta-section">
                     <?php if ($atts['show_date'] === 'on') : ?>
                    <span class="post-date"><?php echo esc_html(get_the_date()); ?></span>
                     <?php endif; ?>
                   <?php if ($atts['display_author'] === 'on') : ?>
                    <span class="post-author"><?php echo esc_html(get_the_author()); ?></span>
                    <?php endif; ?>
                  </div>
                <p><?php echo esc_html(get_the_excerpt()); ?></p>
              </div>
              <a href="<?php the_permalink(); ?>" class="more-link"><?php esc_html_e('Read More', 'custom-post-list'); ?></a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
    <!-- Pagination -->
    <?php if ($atts['show_pagination'] === 'on') : ?>
    <div class="blog-pagination">
      <?php
        echo wp_kses_post(paginate_links(array(
        'total' => $query->max_num_pages,
        'current' => max(1, get_query_var('paged')),
      )));
      ?>
    </div>
    <?php endif; ?>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
  } else {
    return '<p>' . esc_html__('No posts found.', 'custom-post-list') . '</p>';
  }
}

add_shortcode('custom_post_list', 'custom_post_list_shortcode');


function custom_post_list_enqueue_styles() {
  wp_enqueue_style('custom-post-list-styles', plugins_url('assets/css/custom-post-list-styles.css', __FILE__), array(), '1.0.0');
}

add_action('wp_enqueue_scripts', 'custom_post_list_enqueue_styles');

?>

