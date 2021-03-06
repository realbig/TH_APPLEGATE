<?php
/**
 * Adds theme functions.
 *
 * @since   0.1.0
 * @package Applegate
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

add_filter( 'template_include', '_applegate_save_current_template', 1000 );
add_filter( 'body_class', '_applegate_body_class' );
add_filter( 'embed_oembed_html', '_applegate_youtube_embed_related_videos', 10, 4 );
add_action( 'admin_menu', '_applegate_change_post_label' );
add_action( 'init', '_applegate_change_post_object' );
add_action( 'do_meta_boxes', '_applegate_change_featured_image_text' );
add_filter( 'admin_post_thumbnail_html', 'custom_admin_post_thumbnail_html' );

function _applegate_change_featured_image_text() {

	remove_meta_box( 'postimagediv', 'page', 'side' );
	add_meta_box( 'postimagediv', 'Banner Image', 'post_thumbnail_meta_box', 'page', 'side' );
}

function custom_admin_post_thumbnail_html( $content ) {

	global $current_screen;

	if ( $current_screen->post_type == 'page' ) {
		return $content = str_replace( __( 'Set featured image' ), __( 'Upload Banner Image' ), $content );
	} else {
		return $content;
	}
}

function _applegate_change_post_label() {

	global $menu, $submenu;

	$menu[5][0]                 = 'News';
	$menu[5][6]                 = 'dashicons-megaphone';
	$submenu['edit.php'][5][0]  = 'News';
	$submenu['edit.php'][10][0] = 'Add News';
	$submenu['edit.php'][16][0] = 'News Tags';
}

function _applegate_change_post_object() {

	global $wp_post_types;

	$labels                     = &$wp_post_types['post']->labels;
	$labels->name               = 'News';
	$labels->singular_name      = 'News';
	$labels->add_new            = 'Add News';
	$labels->add_new_item       = 'Add News';
	$labels->edit_item          = 'Edit News';
	$labels->new_item           = 'News';
	$labels->view_item          = 'View News';
	$labels->search_items       = 'Search News';
	$labels->not_found          = 'No News found';
	$labels->not_found_in_trash = 'No News found in Trash';
	$labels->all_items          = 'All News';
	$labels->menu_name          = 'News';
	$labels->name_admin_bar     = 'News';
}

function _applegate_save_current_template( $t ) {

	$GLOBALS['current_theme_template'] = str_replace( '.php', '', basename( $t ) );

	return $t;
}

function _applegate_body_class( $classes ) {

	if ( $template = applegate_get_current_template() ) {
		$classes[] = $template;
	}

	return $classes;
}

function applegate_get_current_template() {
	return isset( $GLOBALS['current_theme_template'] ) ? $GLOBALS['current_theme_template'] : false;
}

function get_buckets() {

	return $buckets = array(
		'products'                  => array(
			'title'   => 'Products',
			'content' => '"He\'s drowned with the rest on \'em, last night," said the old Manx sailor.',
			'icon'    => 'shopping-cart',
			'link'    => '/products/',
			'img'     => get_template_directory_uri() . '/assets/images/mystery-man.jpg',
		),
		'contractors-professionals' => array(
			'title'   => 'Contractors & Professionals',
			'content' => '"He\'s drowned with the rest on \'em, last night," said the old Manx sailor.',
			'icon'    => 'wrench',
			'link'    => '/contractors-professionals/',
			'img'     => get_template_directory_uri() . '/assets/images/mystery-man.jpg',
		),
		'architects-specifiers'     => array(
			'title'   => 'Architects & Specifiers',
			'content' => '"He\'s drowned with the rest on \'em, last night," said the old Manx sailor.',
			'icon'    => 'bar-chart',
			'link'    => '/architects-specifiers/',
			'img'     => get_template_directory_uri() . '/assets/images/mystery-man.jpg',
		),
		'building-officials'        => array(
			'title'   => 'Building Officials',
			'content' => '"He\'s drowned with the rest on \'em, last night," said the old Manx sailor.',
			'icon'    => 'building',
			'link'    => '/building-officials/',
			'img'     => get_template_directory_uri() . '/assets/images/mystery-man.jpg',
		),
		'home-owners'               => array(
			'title'   => 'Home Owners',
			'content' => '"He\'s drowned with the rest on \'em, last night," said the old Manx sailor.',
			'icon'    => 'home',
			'link'    => '/home-owners/',
			'img'     => get_template_directory_uri() . '/assets/images/mystery-man.jpg',
		),
	);
}

add_filter( 'the_content', 'tgm_io_shortcode_empty_paragraph_fix' );
/**
 * Filters the content to remove any extra paragraph or break tags
 * caused by shortcodes.
 *
 * @since 1.0.0
 *
 * @param string $content String of HTML content.
 *
 * @return string $content Amended string of HTML content.
 */
function tgm_io_shortcode_empty_paragraph_fix( $content ) {

	$array = array(
		'<p>['    => '[',
		']</p>'   => ']',
		']<br />' => ']'
	);

	return strtr( $content, $array );

}

function get_descendants( $parent_id, $post_type = 'post' ) {

	$descendants = array();

	$children = get_posts( array(
		'numberposts' => - 1,
		'post_status' => 'publish',
		'post_type'   => $post_type,
		'post_parent' => $parent_id,
		'orderby'     => 'menu_order',
		'order'       => 'ASC',
	) );

	foreach ( $children as $child ) {

		$descendants[ $child->ID ]['post'] = $child;

		$grandchildren = get_descendants( $child->ID, $post_type );

		if ( ! empty( $grandchildren ) ) {
			$descendants[ $child->ID ]['children'] = $grandchildren;
		}
	}

	return $descendants;
}

function get_menu_by_location( $location ) {

	if ( empty( $location ) ) {
		return false;
	}

	$locations = get_nav_menu_locations();
	if ( ! isset( $locations[ $location ] ) ) {
		return false;
	}

	$menu_obj = get_term( $locations[ $location ], 'nav_menu' );

	return $menu_obj;
}

function applegate_save_bucket( $bucket ) {

	if ( is_user_logged_in() ) {

		update_user_meta( get_current_user_id(), 'applegate_current_bucket', $bucket );

		return true;
	} elseif ( isset( $_SESSION ) ) {

		$_SESSION['applegate-bucket'] = $bucket;

		return true;
	}

	return false;
}

function applegate_get_bucket() {

	if ( is_user_logged_in() ) {
		return get_user_meta( get_current_user_id(), 'applegate_current_bucket', true );
	} elseif ( isset( $_SESSION['applegate-bucket'] ) ) {
		return $_SESSION['applegate-bucket'];
	}

	return false;
}

function applegate_post_meta() {

	if ( $categories = get_the_Category_list( ', ' ) ) {
		$categories = 'in ' . $categories;
	}
	?>
	<p class="post-meta">
		<span class="fa fa-pencil"></span> Posted on <?php the_date(); ?>
		by <?php the_author(); ?> <?php echo $categories; ?>
	</p>
	<?php
}

function _applegate_youtube_embed_related_videos( $cache ) {

	$search_regex = '/feature=oembed/';
	if ( preg_match( $search_regex, $cache ) ) {

		$cache = preg_replace( $search_regex, 'feature=oembed&rel=0', $cache );
	}

	return $cache;
}

function applegate_show_testimonial() {

	$testimonials = get_posts( array(
		'post_type'   => 'testimonial',
		'numberposts' => - 1,
	) );

	if ( ! empty( $testimonials ) ) {

		$testimonial = $testimonials[ array_rand( $testimonials ) ];
		?>
		<div <?php post_class( array(), $testimonial->ID ); ?>>

			<p class="testimonial-copy">
				"<?php echo $testimonial->post_content; ?>"
			</p>

			<p class="testimonial-title">– <?php echo get_the_title( $testimonial->ID ); ?></p>
		</div>
		<?php
	}
}

add_action( 'admin_bar_menu', '_applegate_bucket_wptoolbar', 999 );

function _applegate_bucket_wptoolbar( $wp_admin_bar ) {

	if ( is_admin() ) {
		return;
	}

	$bucket = applegate_get_bucket();

	$args = array(
		'id'    => 'applegate_bucket',
		'title' => 'Current Bucket: ' . ( $bucket ? get_buckets()[ $bucket ]['title'] : 'None' ),
	);
	$wp_admin_bar->add_node( $args );
}