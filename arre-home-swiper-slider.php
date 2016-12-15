<?php
/*
Plugin Name: Arre Swiper Slider
Plugin URI: https://idangero.us/swiper
Description: Slider swiper responsive sur la homepage
Version: 0.1
Author: Ticme.fr
Author URI: http://ticme.fr
License: GPL2
*/

// Exit if accessed directly. - Exit si on y accède directement au fichier du plugin courant
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detection des plugins actifs. Permet d'accéder à la méthod is_plugin_active.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
* Initialise les fonctionnalités liées au slider 
*/
add_action('init', 'arreswiperslider_init');
add_action('add_meta_boxes', 'arreswiperslider_metaboxes');
add_action('save_post', 'arreswiperslider_savepost', 10, 2);
add_action('manage_edit-arreswiperslider_columns', 'arreswiperslider_columnfilter');
add_action('manage_posts_custom_column', 'arreswiperslider_column');


function wp_arre_register_js() {
    //wp_enqueue_script( 'swiper-js', plugins_url().'/arre-home-swiper-slider/js/swiper.min.js', null,'3.4.0', true );
    wp_enqueue_script( 'jquery-swiper-js', plugins_url('/js/swiper.jquery.min.js',__FILE__), null,'3.4.0', true );
}

add_action('wp_enqueue_scripts', 'wp_arre_register_js',31);

function wp_arre_register_css() {
    wp_enqueue_style('swiper-css',  plugins_url('/css/swiper.min.css', __FILE__), null, '3.4.0', 'all');
    wp_enqueue_style('swiper-custom-css',  plugins_url('/css/custom.css', __FILE__), null, '3.4.0', 'all');
}

 add_action('wp_enqueue_scripts', 'wp_arre_register_css',31);

/**
* CACHE - le "Ajouter Media"
*/

function arreswiperslider_hide_addmedia_button_to_editor() {
    global $current_screen;
    if( $current_screen->post_type == 'arreswiperslider' ) {
        $css = '<style type="text/css">';
            $css .= '#insert-media-button, #mceu_15 { display: none; }';
            $css .= '.wpseo-metabox { display: none; }';
            $css .= '.content-score { display: none; }';
            $css .= '.keyword-score { display: none; }';
        $css .= '</style>';

        echo $css;
    }
}
add_action('admin_footer', 'arreswiperslider_hide_addmedia_button_to_editor');


/**
 * hide/cache certain meta boxes on the 'arrecamerslide' custom post type
 */
function hide_meta_boxes_arreswiperslider() {
    if(is_plugin_active('members/members.php')) {
        remove_meta_box('content-permissions-meta-box', 'arreswiperslider', 'normal');
        remove_meta_box('content-permissions-meta-box', 'arreswiperslider', 'side');
        remove_meta_box('content-permissions-meta-box', 'arreswiperslider', 'core');
        remove_meta_box('content-permissions-meta-box', 'arreswiperslider', 'advanced');
    }
    remove_meta_box( 'slugdiv','arreswiperslider','normal' ); // Slug Metabox  
    remove_meta_box('wpseo_meta', 'arreswiperslider', 'low');    
}
add_action('do_meta_boxes', 'hide_meta_boxes_arreswiperslider');

/* 
 * remove_nav_menu
 */
function my_custom_remove_nav_menu() {
    remove_meta_box( 'add-arreswiperslider' , 'nav-menus' , 'side' ); 
}
add_action('admin_head-nav-menus.php', 'my_custom_remove_nav_menu');

function arreswiperslider_init() {
    $labels = array(
        'name' => 'Slide',
        'singular_name' => 'Slide',
        'add_new' => 'Ajouter un Slide',
        'add_new_item' => 'Ajouter un nouveau Slide',
        'edit_item' => 'Editer un Slide',
        'new_item' => 'Nouveau Slide',
        'view_item' => 'Voir le Slide',
        'search_items' => 'Rechercher un Slide',
        'not_found' => 'Aucun Slide',
        'not_found_in_trash' => 'Aucun Slide dans la corbeille',
        'parent_item_colon' => '',
        'menu_name' => 'Sliders'
    );

    register_post_type('arreswiperslider', array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => false,
        'capability_type' => 'post',
        'menu_position' => 9,
        'exclude_from_search' => false, // the important line here!
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_icon' => 'dashicons-images-alt2',
        'supports' => array('title', 'editor', 'thumbnail')
    ));
      
    add_theme_support('post-thumbnails');
    //set_post_thumbnail_size( 1920, 720, true);
    add_image_size('slider', 1920, 720, true);
}

function arreswiperslider_columnfilter($columns) {
    $thumb = array('thumbnail' => 'Slide associé');
    $columns = array_slice($columns, 0, 1) + $thumb + array_slice($columns, 1, null);
    return $columns;
}

function arreswiperslider_column($column) {
    global $post;
    if ($column == 'thumbnail') {
        echo edit_post_link(get_the_post_thumbnail($post->ID, 'thumbnail'), null, null, $post->ID);
    }
}

/*
 * Permet de gérer la métabox
 */
function arreswiperslider_metaboxes() {
    add_meta_box('myswiperslider', 'Lien', 'arreswiperslider_metabox', 'arreswiperslider', 'normal', 'high');
}

/*
 * Metabox gérer le lien
 */
function arreswiperslider_metabox($object) {
    wp_nonce_field('arreswiperslider', 'arreswiperslider_nonce');
    ?>
    <div class="meta-box-item-title">
        <p>Désigne le lien "lire la suite" s'affichant au pied du slide, dans la zone de texte, <em>(si le slide doit rediriger vers un article particulier, sinon laissez ce champ vide et le lien "lire la suite" ne s'affichera pas)</em> </p>
    </div>
    <div class="meta-box-item-content">
        <input type="text" name="arreswiperslider_link" style="width:100%;" value="<?php echo esc_attr(get_post_meta($object->ID, '_linkswiperslider', true)); ?>">
    </div>
    <?php
}

/*
 * Permet de gérer l'enregistrement d'un slide
 */
function arreswiperslider_savepost($post_id, $post) {

    if (!isset($_POST['arreswiperslider_link']) || !wp_verify_nonce($_POST['arreswiperslider_nonce'], 'arreswiperslider')) {
        return $post_id;
    }

    $type = get_post_type_object($post->post_type);
    if (!current_user_can($type->cap->edit_post)) {
        return $post_id;
    }

    update_post_meta($post_id, '_linkswiperslider', $_POST['arreswiperslider_link']);
}

/*
 * Permet d'afficher le slider
 */
function arreswiperslider_show() {
    if (is_plugin_active('intuitive-custom-post-order/intuitive-custom-post-order.php')) {
        global $hicpo; // Call the class variable for the ICO plugin, so we can disable its overriding of the orderby parameter
        remove_filter('pre_get_posts', array($hicpo, 'hicpo_pre_get_posts'));
        $slides = new WP_Query(array('post_type' => 'arreswiperslider', 'orderby' => 'menu_order', 'order' => 'ASC', 'hide_empty' => 1, 'depth' => 1, 'posts_per_page' => -1));
    } else {
        $slides = new WP_Query(array('post_type' => 'arreswiperslider', 'orderby' => 'date', 'order' => 'ASC', 'hide_empty' => 1, 'depth' => 1, 'posts_per_page' => -1));
    }
    add_action('wp_footer', 'arreswiperslider_script', 30);
    ?>
    <?php if($slides->have_posts()): ?>
        <div class="swiper-container visible-md-block visible-lg-block">
            <div class="swiper-wrapper">                
                    <?php
                        while ($slides->have_posts()):
                        $slides->the_post();
                        global $post;
                        $large_image_url = wp_get_attachment_image_src(get_post_thumbnail_id($slides->ID), 'slider');
                    ?> 
                    
                        <?php if ($large_image_url[0] != ''): ?>
                            <div class="swiper-slide">
                                <?php $link = (!empty(get_post_meta($post->ID, '_linkswiperslider', true))) ? get_post_meta($post->ID, '_linkswiperslider', true) : '' ;?>
                                
                                    <img src="<?php echo $large_image_url[0]; ?>" alt="<?php the_title(); ?>" class="img-responsive">
                                    <div class="swiper-slide-caption">
                                        <h2><?php the_title(); ?></h2>
                                        <p><?php the_content() ?></p>
                                        <?php  if (!empty($link)): ?>
                                            <a href="<?php echo $link; ?>" title="<?php the_title(); ?>"class="btn btn-ellipse">Lire la suite</a>
                                        <?php endif; ?>
                                    </div>
                            </div>
                        <?php endif; ?>
                        
                    
                    <?php endwhile; ?>  
                </div>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination visible-md-block visible-lg-block"></div>
            <!-- Add Arrows -->
            <div class="swiper-button-next visible-md-block visible-lg-block"></div>
            <div class="swiper-button-prev visible-md-block visible-lg-block"></div>
        </div>
        <header class="header-page header-xs-sm-page visible-xs-block visible-sm-block">
            <h2>
                <?php echo bloginfo('name'); ?>
            </h2>
            <p>
                <?php echo bloginfo('description'); ?>
            </p>  
        </header>
    <?php else: ?>
        <header class="header-page visible-xs-block visible-sm-block">
            <h2>
                <?php echo bloginfo('name'); ?>
            </h2>
            <p>
                <?php echo bloginfo('description'); ?>
            </p>  
        </header>
    <?php endif; ?>
<?php } 
/*
 * Permet d'afficher des scripts
 */
function arreswiperslider_script() {    
    ?>        
        <script type='text/javascript'>
            $(document).ready(function(){
                var swiper = new Swiper('.swiper-container', {
                    pagination: '.swiper-pagination',
                    nextButton: '.swiper-button-next',
                    prevButton: '.swiper-button-prev',
                    paginationClickable: true,
                    spaceBetween: 0,
                    centeredSlides: true,
                    autoplay: 2500,
                    autoplayDisableOnInteraction: false,
                    effect: 'fade',
                    slidesPerView: 1,
                    loop: true
                });
            });
        </script>
<?php } ?>