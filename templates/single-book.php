<?php
/**
 * The template for displaying all single book posts
 *
 */

get_header();

?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">

			<?php

			if ( class_exists( 'WooCommerce' ) ) {
				wc_print_notices();
			}

			?>
			<hr>
			<?php
			while ( have_posts() ) : the_post();
				the_title();

				the_content();

			endwhile; // End of the loop.
			?>

			<form action="<?php echo get_permalink(); ?>" method="post">
				<input name="add-to-cart" type="hidden" value="<?php the_ID(); ?>" />
				<input name="quantity" type="number" value="1" min="1"  />
				<input name="submit" type="submit" value="Add to cart" />
			</form>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_sidebar();
get_footer();