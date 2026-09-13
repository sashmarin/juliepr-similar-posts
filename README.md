# Juliepr Similar Posts

A small dependency-free WordPress plugin that displays three random posts by default from the current post's primary top-level category.

The default heading is English (`Articles on a similar topic`) and has a bundled Russian translation (`Статьи на похожую тему`).

Each result displays its featured image when available and title in a responsive three-column layout.

## Uninstall cleanup

By default, uninstalling the plugin preserves the manually selected-posts data. To remove that post meta during uninstall, enable **Settings → Juliepr Similar Posts → Delete all Similar Posts data** before deleting the plugin.

## Use in the Block Editor

Add the **Juliepr Similar Posts** block to a post. It is dynamic: on the front end it calls the same renderer as the shortcode and template function. In the block settings sidebar, set **Number of posts** and optionally set **Minimum publication year** to show only posts published from that year onward.

## Use in a theme

Add this at the end of the single-post template, inside the main post loop:

```php
echo juliepr_similar_posts_render();
```

To render for an explicit post, change the heading, or limit results to a minimum publication year:

```php
echo juliepr_similar_posts_render(
	$post_id,
	array(
		'heading'  => 'More on this topic',
		'number'   => 6,
		'min_year' => 2020,
	)
);
```

## Use in content

Add the following shortcode to a post or a Shortcode block:

```text
[juliepr_similar_posts]
```

Optional `heading`, `number`, and `min_year` attributes are supported. The default for `number` is `3`; `min_year` includes posts published from 1 January of that year onward:

```text
[juliepr_similar_posts heading="More on this topic" number="6" min_year="2020"]
```

## Category selection

The plugin selects the deepest category assigned to the current post. If several categories have the same depth, it uses the first category returned by WordPress. It then queries against that category's top-level ancestor, allowing posts in related child categories to appear.

## Choose posts manually

At the bottom of a post's editor, use the **Similar posts** field to search published posts by title and add them to a list. When the list is populated, the plugin selects random published posts from it instead of the category. The current post is always excluded. The number of displayed posts defaults to three and can be changed in the block settings or with the `number` argument. Remove every post from the list to resume category-based selection.

The `juliepr_similar_posts_query_args` filter can adjust the underlying `WP_Query` arguments.

## Customize the markup

The default markup is produced by `juliepr_similar_posts_render_posts()`. To replace it without modifying the plugin, use the `juliepr_similar_posts_markup` filter. It receives the default markup, the completed `WP_Query`, display arguments, current post ID, and selected top-level category:

```php
add_filter(
	'juliepr_similar_posts_markup',
	function ( $markup, $query, $args, $post_id, $category ) {
		return '<div class="my-similar-posts">' . esc_html( $query->post_count ) . '</div>';
	},
	10,
	5
);
```
