<?php
/*
Plugin Name: TradeMe Exporter
*/

add_action('publish_post', array('trademe_exporter', 'export_single'));

function trademe_exporter_export_single($post_ID) {
  // export that puppy out!
  $file = fopen("testexporting.txt","w");
  fwrite($file,"Hello World. Exporting!");
  fclose($file);

  echo 'some stuff happened!';
  return $post_ID;
}

// add link to posts admin for exporting to AbeBooks
function show_trademe_exporter_on_posts_list($actions, $post) {
 $actions['trademe_export'] = '<a href="/trademe_exporter_single.php?id=' . $post->ID  .'" target="_blank">Export to TradeMe</a>';

 return $actions;
}

add_filter('post_row_actions', 'show_trademe_exporter_on_posts_list', 10, 2);

?>
