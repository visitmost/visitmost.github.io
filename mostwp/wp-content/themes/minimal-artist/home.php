<?php get_header(); ?>



<main id="main" role="main" class="main post-index<?php if ( is_active_sidebar( 'rightside' ) ): ?> hasside<?php endif; ?>">

	<?php

  // welcome to MOST

  $welcome_post_id = 758;
  $content_post = get_post($welcome_post_id);
  $welcome_post = $content_post->post_content;
  $welcome_post = apply_filters('the_content', $welcome_post);
  $welcome_post = str_replace(']]>', ']]&gt;', $welcome_post);

  echo $welcome_post;

 if (have_posts()) :

	if(is_archive()) : ?>
		<h1 class="title">
			<?php	if( is_author() ) : printf(get_the_author()); elseif (is_date()) : printf(get_the_date()); endif; ?>
		</h1>
	<?php endif; ?>

  <br />
  <hr />
  <br />

  <h2>Titles currently on display at the <a href="/antiquarian-book-gallery/">Antiquarian Book Gallery </a></h2>
  <br />

	<ul class="cf" role="list" aria-label="<?php _e( 'List of posts', 'minart' ); ?>">
	<?php
  
  // query books currently on display in the store

  query_posts( array ( 'category_name' => 'on-display-in-gallery', 'posts_per_page' => 3 ) );

	while (have_posts()) : the_post();

		get_template_part( 'content', get_post_format() );

	 endwhile;
	 ?>
	</ul>
	 <nav id="nav-main" class="navs" role="navigation" aria-label="<?php _e( 'Posts navigation', 'minart' ); ?>">
	 	<span class="nav-prev"><?php next_posts_link( __( 'Older', 'minart')); ?></span>
	 	<span class="nav-next"><?php previous_posts_link(__('Newer', 'minart')); ?> </span>
	 </nav>
	<?php
	 else: ?>
	<p><?php _e('Sorry, no posts found.', 'minart'); ?></p>
	<?php endif; ?>



</main> <!-- end main -->

<?php get_sidebar(); ?>


<?php get_footer(); ?>
