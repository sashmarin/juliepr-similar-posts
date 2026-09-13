# Project guidance

Juliepr Similar Posts is intentionally small and dependency-free.

- Keep the public API prefixed with `juliepr_similar_posts_`.
- Do not add an admin page, database table, build step, or third-party library unless explicitly requested.
- Preserve both display methods: the `[juliepr_similar_posts]` shortcode and `juliepr_similar_posts_render()` for theme templates.
- The query must exclude the current post, include published posts only, and return no more than three random results from the chosen top-level category.
- Keep version values synchronized in the PHP header and `readme.txt` when releasing.
- Run PHP linting and test shortcode/template output in a real WordPress installation before release.
