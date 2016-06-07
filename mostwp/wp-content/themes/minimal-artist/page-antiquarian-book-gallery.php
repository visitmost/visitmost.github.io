<?php

 get_header(); ?>

<main id="main" role="main" class="main cf<?php if ( is_active_sidebar( 'rightside' ) ): ?> hasside<?php endif; ?>" >

	<?php
		// Start the Loop.
		while (have_posts()) : the_post(); ?>

		<?php if ( is_front_page()) : ?>

			<h2 class="title page-title"><?php the_title(); ?></h2>

		<?php else: ?>

			<h1 class="title page-title"><?php the_title(); ?></h1>

		<?php endif; ?>

		<div class="pagebody cf"><?php the_content(); ?></div>

	<?php

		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ):
		comments_template();
		endif;

	endwhile;  ?>

    
  <br />
  <hr />
  <br />

  <h2>Items currently on display</h2>
  <br />

	<ul class="cf" role="list" aria-label="<?php _e( 'List of posts', 'minart' ); ?>">
	<?php
  
  // query books currently on display in the store

  query_posts( array ( 'category_name' => 'on-display-in-gallery', 'posts_per_page' => -1 ) );

	while (have_posts()) : the_post();

		get_template_part( 'content', get_post_format() );

	 endwhile;
	 ?>
	</ul>
	 <nav id="nav-main" class="navs" role="navigation" aria-label="<?php _e( 'Posts navigation', 'minart' ); ?>">
	 	<span class="nav-prev"><?php next_posts_link( __( 'Older', 'minart')); ?></span>
	 	<span class="nav-next"><?php previous_posts_link(__('Newer', 'minart')); ?> </span>
	 </nav>


</main><!-- #main -->

<?php get_sidebar(); ?>

<?php get_footer();
