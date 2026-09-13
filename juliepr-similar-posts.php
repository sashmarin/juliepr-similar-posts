<?php
/**
 * Plugin Name:       Juliepr Similar Posts
 * Plugin URI:        https://github.com/sashmarin/juliepr-similar-posts
 * Description:       Displays three random posts from the current post's primary top-level category.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Sergei Ashmarin
 * Author URI:        https://github.com/sashmarin
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       juliepr-similar-posts
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JULIEPR_SIMILAR_POSTS_VERSION', '1.0.0' );

/**
 * Loads plugin translations.
 *
 * @return void
 */
function juliepr_similar_posts_load_textdomain() {
	load_plugin_textdomain( 'juliepr-similar-posts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'juliepr_similar_posts_load_textdomain', 0 );

/**
 * Returns the option name used for the uninstall cleanup preference.
 *
 * @return string Option name.
 */
function juliepr_similar_posts_get_delete_data_on_uninstall_option_name() {
	return 'juliepr_similar_posts_delete_data_on_uninstall';
}

/**
 * Sanitizes the uninstall cleanup preference.
 *
 * @param mixed $value Submitted option value.
 * @return bool Whether plugin post meta should be removed on uninstall.
 */
function juliepr_similar_posts_sanitize_delete_data_on_uninstall( $value ) {
	return (bool) $value;
}

/**
 * Registers the plugin settings.
 *
 * @return void
 */
function juliepr_similar_posts_register_settings() {
	register_setting(
		'juliepr_similar_posts_settings',
		juliepr_similar_posts_get_delete_data_on_uninstall_option_name(),
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'juliepr_similar_posts_sanitize_delete_data_on_uninstall',
			'default'           => false,
		)
	);
}
add_action( 'admin_init', 'juliepr_similar_posts_register_settings' );

/**
 * Adds the plugin settings page under Settings.
 *
 * @return void
 */
function juliepr_similar_posts_add_settings_page() {
	add_options_page(
		__( 'Juliepr Similar Posts', 'juliepr-similar-posts' ),
		__( 'Similar Posts', 'juliepr-similar-posts' ),
		'manage_options',
		'juliepr-similar-posts',
		'juliepr_similar_posts_render_settings_page'
	);
}
add_action( 'admin_menu', 'juliepr_similar_posts_add_settings_page' );

/**
 * Renders the plugin settings page.
 *
 * @return void
 */
function juliepr_similar_posts_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$option_name = juliepr_similar_posts_get_delete_data_on_uninstall_option_name();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Juliepr Similar Posts', 'juliepr-similar-posts' ); ?></h1>
		<?php settings_errors(); ?>
		<form action="options.php" method="post">
			<?php settings_fields( 'juliepr_similar_posts_settings' ); ?>
			<label>
				<input name="<?php echo esc_attr( $option_name ); ?>" type="hidden" value="0" />
				<input name="<?php echo esc_attr( $option_name ); ?>" type="checkbox" value="1" <?php checked( (bool) get_option( $option_name, false ) ); ?> />
				<?php esc_html_e( 'Delete all Similar Posts data (selected-posts post meta) when the plugin is uninstalled.', 'juliepr-similar-posts' ); ?>
			</label>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Registers the front-end stylesheet.
 *
 * @return void
 */
function juliepr_similar_posts_register_styles() {
	wp_enqueue_style(
		'juliepr-similar-posts',
		plugin_dir_url( __FILE__ ) . 'assets/css/similar-posts.css',
		array(),
		JULIEPR_SIMILAR_POSTS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'juliepr_similar_posts_register_styles' );

/**
 * Registers the Similar Posts block and its editor script.
 *
 * @return void
 */
function juliepr_similar_posts_register_block() {
	wp_register_script(
		'juliepr-similar-posts-block-editor',
		plugin_dir_url( __FILE__ ) . 'block/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
		JULIEPR_SIMILAR_POSTS_VERSION,
		true
	);

	register_block_type(
		__DIR__ . '/block',
		array(
			'render_callback' => 'juliepr_similar_posts_render_block',
		)
	);
}
add_action( 'init', 'juliepr_similar_posts_register_block' );

/**
 * Returns the meta key used for manually selected similar posts.
 *
 * @return string Meta key.
 */
function juliepr_similar_posts_get_selected_post_ids_meta_key() {
	return '_juliepr_similar_posts_selected_post_ids';
}

/**
 * Adds the manually selected-posts field to the post editor.
 *
 * @return void
 */
function juliepr_similar_posts_add_selected_posts_meta_box() {
	add_meta_box(
		'juliepr-similar-posts-selected-posts',
		__( 'Similar posts', 'juliepr-similar-posts' ),
		'juliepr_similar_posts_render_selected_posts_meta_box',
		'post',
		'normal',
		'low'
	);
}
add_action( 'add_meta_boxes_post', 'juliepr_similar_posts_add_selected_posts_meta_box' );

/**
 * Loads the searchable selected-posts control in the post editor.
 *
 * @param string $hook_suffix Current admin page.
 * @return void
 */
function juliepr_similar_posts_enqueue_selected_posts_editor_assets( $hook_suffix ) {
	global $post;

	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'post' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script(
		'juliepr-similar-posts-selected-posts-editor',
		plugin_dir_url( __FILE__ ) . 'assets/js/selected-posts-editor.js',
		array(),
		JULIEPR_SIMILAR_POSTS_VERSION,
		true
	);
	wp_enqueue_style(
		'juliepr-similar-posts-selected-posts-editor',
		plugin_dir_url( __FILE__ ) . 'assets/css/selected-posts-editor.css',
		array(),
		JULIEPR_SIMILAR_POSTS_VERSION
	);

	wp_localize_script(
		'juliepr-similar-posts-selected-posts-editor',
		'julieprSimilarPostsSelectedPosts',
		array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'juliepr_similar_posts_search_selected_posts' ),
			'postId'        => $post instanceof WP_Post ? $post->ID : 0,
			'minimumLength' => 2,
			'noResults'     => __( 'No published posts found.', 'juliepr-similar-posts' ),
			'loading'       => __( 'Searching…', 'juliepr-similar-posts' ),
			'remove'        => __( 'Remove', 'juliepr-similar-posts' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'juliepr_similar_posts_enqueue_selected_posts_editor_assets' );

/**
 * Renders the manually selected-posts field.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function juliepr_similar_posts_render_selected_posts_meta_box( $post ) {
	$post_ids = juliepr_similar_posts_parse_selected_post_ids(
		(string) get_post_meta( $post->ID, juliepr_similar_posts_get_selected_post_ids_meta_key(), true )
	);
	$selected_posts = array();

	if ( ! empty( $post_ids ) ) {
		$selected_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'post__in'       => $post_ids,
				'post__not_in'   => array( $post->ID ),
				'orderby'        => 'post__in',
				'posts_per_page' => -1,
			)
		);
	}

	wp_nonce_field( 'juliepr_similar_posts_save_selected_posts', 'juliepr_similar_posts_selected_posts_nonce' );
	?>
	<p>
		<label for="juliepr-similar-posts-search-posts">
			<?php esc_html_e( 'Search posts by title', 'juliepr-similar-posts' ); ?>
		</label>
	</p>
	<input class="widefat" id="juliepr-similar-posts-search-posts" type="search" autocomplete="off" placeholder="<?php esc_attr_e( 'Start typing a post title…', 'juliepr-similar-posts' ); ?>" />
	<div id="juliepr-similar-posts-search-results" class="juliepr-similar-posts-search-results" role="status" aria-live="polite"></div>
	<ul id="juliepr-similar-posts-selected-posts" class="juliepr-similar-posts-selected-posts">
		<?php foreach ( $selected_posts as $selected_post ) : ?>
			<li data-post-id="<?php echo esc_attr( $selected_post->ID ); ?>">
				<span><?php echo esc_html( get_the_title( $selected_post ) ); ?></span>
				<button type="button" class="button-link-delete" aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s', 'juliepr-similar-posts' ), get_the_title( $selected_post ) ) ); ?>"><?php esc_html_e( 'Remove', 'juliepr-similar-posts' ); ?></button>
			</li>
		<?php endforeach; ?>
	</ul>
	<input id="juliepr-similar-posts-selected-post-ids" name="juliepr_similar_posts_selected_post_ids" type="hidden" value="<?php echo esc_attr( implode( ' ', wp_list_pluck( $selected_posts, 'ID' ) ) ); ?>" />
	<p class="description">
		<?php esc_html_e( 'Choose posts from the search results. Random published posts are shown only from this list; remove all posts to use the category.', 'juliepr-similar-posts' ); ?>
	</p>
	<?php
}

/**
 * Validates and normalizes a whitespace-separated list of post IDs.
 *
 * @param string $value Submitted field value.
 * @return int[]|null Normalized IDs, or null when the format is invalid.
 */
function juliepr_similar_posts_parse_selected_post_ids( $value ) {
	$value = trim( $value );

	if ( '' === $value ) {
		return array();
	}

	if ( ! preg_match( '/^[0-9]+(?:\\s+[0-9]+)*$/', $value ) ) {
		return null;
	}

	$post_ids = array_map( 'absint', preg_split( '/\\s+/', $value ) );

	if ( in_array( 0, $post_ids, true ) ) {
		return null;
	}

	$post_ids = array_unique( $post_ids );

	return array_values( $post_ids );
}

/**
 * Saves the manually selected similar-post IDs.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function juliepr_similar_posts_save_selected_post_ids( $post_id ) {
	if (
		! isset( $_POST['juliepr_similar_posts_selected_posts_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['juliepr_similar_posts_selected_posts_nonce'] ) ), 'juliepr_similar_posts_save_selected_posts' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		wp_is_post_revision( $post_id ) ||
		! current_user_can( 'edit_post', $post_id ) ||
		'post' !== get_post_type( $post_id )
	) {
		return;
	}

	$value    = isset( $_POST['juliepr_similar_posts_selected_post_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['juliepr_similar_posts_selected_post_ids'] ) ) : '';
	$post_ids = juliepr_similar_posts_parse_selected_post_ids( $value );

	if ( null === $post_ids ) {
		return;
	}

	$meta_key = juliepr_similar_posts_get_selected_post_ids_meta_key();

	if ( empty( $post_ids ) ) {
		delete_post_meta( $post_id, $meta_key );
		return;
	}

	update_post_meta( $post_id, $meta_key, implode( ' ', $post_ids ) );
}
add_action( 'save_post_post', 'juliepr_similar_posts_save_selected_post_ids' );

/**
 * Searches published posts for the selected-posts editor control.
 *
 * @return void
 */
function juliepr_similar_posts_search_selected_posts() {
	check_ajax_referer( 'juliepr_similar_posts_search_selected_posts', 'nonce' );

	$post_id = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0;

	if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'You cannot search posts for this post.', 'juliepr-similar-posts' ) ), 403 );
	}

	$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

	if ( '' === $search ) {
		wp_send_json_success( array() );
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'post__not_in'        => array( $post_id ),
			's'                   => $search,
			'search_columns'      => array( 'post_title' ),
			'posts_per_page'      => 10,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		)
	);
	$posts = array();

	foreach ( $query->posts as $search_result ) {
		$posts[] = array(
			'id'    => $search_result->ID,
			'title' => get_the_title( $search_result ),
		);
	}

	wp_send_json_success( $posts );
}
add_action( 'wp_ajax_juliepr_similar_posts_search_selected_posts', 'juliepr_similar_posts_search_selected_posts' );

/**
 * Finds the deepest assigned category and returns its top-level ancestor.
 *
 * When several categories are assigned, the deepest one is used. Categories
 * at the same depth retain the order returned by WordPress, so the first one
 * wins the tie.
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null Top-level category, or null when none is assigned.
 */
function juliepr_similar_posts_get_primary_top_level_category( $post_id ) {
	$categories = get_the_category( $post_id );

	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return null;
	}

	$category  = $categories[0];
	$max_depth = -1;

	foreach ( $categories as $candidate ) {
		$depth = count( get_ancestors( $candidate->term_id, 'category', 'taxonomy' ) );

		if ( $depth > $max_depth ) {
			$category  = $candidate;
			$max_depth = $depth;
		}
	}

	if ( $category->parent ) {
		$ancestors = get_ancestors( $category->term_id, 'category', 'taxonomy' );

		if ( ! empty( $ancestors ) ) {
			$category = get_term( end( $ancestors ), 'category' );
		}
	}

	return ( $category && ! is_wp_error( $category ) ) ? $category : null;
}

/**
 * Renders related posts for a post.
 *
 * Use this function in a theme template, for example after the post content:
 * juliepr_similar_posts_render();
 *
 * @param int   $post_id        Post ID. Defaults to the current post.
 * @param array $args           Optional display arguments.
 * @return string Markup, or an empty string when there are no matching posts.
 */
function juliepr_similar_posts_render( $post_id = 0, $args = array() ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();

	if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'heading'  => __( 'Articles on a similar topic', 'juliepr-similar-posts' ),
			'number'   => 3,
			'min_year' => 0,
		)
	);

	$selected_post_ids = juliepr_similar_posts_parse_selected_post_ids(
		(string) get_post_meta( $post_id, juliepr_similar_posts_get_selected_post_ids_meta_key(), true )
	);
	$category = null;

	if ( empty( $selected_post_ids ) ) {
		$category = juliepr_similar_posts_get_primary_top_level_category( $post_id );
	}

	if ( ! $selected_post_ids && ! $category ) {
		return '';
	}

	$query_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, absint( $args['number'] ) ),
		'post__not_in'        => array( $post_id ),
		'orderby'             => 'rand',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( $selected_post_ids ) {
		$query_args['post__in'] = $selected_post_ids;
	} else {
		$query_args['tax_query'] = array(
			array(
				'taxonomy'         => 'category',
				'field'            => 'term_id',
				'terms'            => array( $category->term_id ),
				'include_children' => true,
			),
		);
	}

	$min_year = absint( $args['min_year'] );

	if ( $min_year ) {
		$query_args['date_query'] = array(
			array(
				'after'     => array(
					'year'  => $min_year,
					'month' => 1,
					'day'   => 1,
				),
				'inclusive' => true,
			),
		);
	}

	/**
	 * Filters the query for similar posts.
	 *
	 * @param array   $query_args Query arguments.
	 * @param int     $post_id    Current post ID.
	 * @param WP_Term|null $category Chosen top-level category, or null for a manual list.
	 */
	$query_args = apply_filters( 'juliepr_similar_posts_query_args', $query_args, $post_id, $category );
	$query      = new WP_Query( $query_args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	$markup = juliepr_similar_posts_render_posts( $query, $args );
	wp_reset_postdata();

	/**
	 * Filters the similar-posts markup.
	 *
	 * Return custom markup here to replace the plugin's default presentation.
	 *
	 * @param string   $markup   Similar-posts markup.
	 * @param WP_Query $query    Query containing the similar posts.
	 * @param array    $args     Display arguments.
	 * @param int      $post_id  Current post ID.
	 * @param WP_Term|null $category Chosen top-level category, or null for a manual list.
	 */
	return apply_filters( 'juliepr_similar_posts_markup', $markup, $query, $args, $post_id, $category );
}

/**
 * Renders the default markup for a similar-posts query.
 *
 * @param WP_Query $query Query containing the similar posts.
 * @param array    $args  Display arguments.
 * @return string Similar-posts markup.
 */
function juliepr_similar_posts_render_posts( $query, $args ) {
	ob_start();
	?>
	<section class="juliepr-similar-posts" aria-label="<?php echo esc_attr( $args['heading'] ); ?>">
		<h2 class="juliepr-similar-posts__heading"><?php echo esc_html( $args['heading'] ); ?></h2>
		<div class="juliepr-similar-posts__grid">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<article <?php post_class( 'juliepr-similar-posts__item' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="juliepr-similar-posts__image-link" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
							<?php the_post_thumbnail( 'medium_large', array( 'class' => 'juliepr-similar-posts__image' ) ); ?>
						</a>
					<?php endif; ?>
					<h3 class="juliepr-similar-posts__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				</article>
				<?php
			}
			?>
		</div>
	</section>
	<?php

	return ob_get_clean();
}

/**
 * Renders the Similar Posts block.
 *
 * @param array $attributes Block attributes.
 * @return string Markup, or an empty string when there are no matching posts.
 */
function juliepr_similar_posts_render_block( $attributes ) {
	return juliepr_similar_posts_render(
		0,
		array(
			'min_year' => isset( $attributes['minYear'] ) ? $attributes['minYear'] : 0,
			'number'   => isset( $attributes['number'] ) ? $attributes['number'] : 3,
		)
	);
}

/**
 * Shortcode callback for [juliepr_similar_posts].
 *
 * @param array $atts Shortcode attributes.
 * @return string Similar-posts markup.
 */
function juliepr_similar_posts_shortcode( $atts ) {
	return juliepr_similar_posts_render( 0, $atts );
}
add_shortcode( 'juliepr_similar_posts', 'juliepr_similar_posts_shortcode' );
