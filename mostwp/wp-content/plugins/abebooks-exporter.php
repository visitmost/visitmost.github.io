<?php
/*
Plugin Name: AbeBooks Exporter
*/

add_action('publish_post', array('abe_exporter', 'export_single'));

function abe_exporter_export_single($post_ID) {
  // export that puppy out!
  $file = fopen("testexporting.txt","w");
  fwrite($file,"Hello World. Exporting!");
  fclose($file);

  echo 'some stuff happened!';
  return $post_ID;
}

// add link to posts admin for exporting to AbeBooks
function show_abe_exporter_on_posts_list($actions, $post) {
 $actions['abe_export'] = '<a href="/abe_exporter_single.php?id=' . $post->ID  .'" target="_blank">Export to AbeBooks</a>';

 return $actions;
}

add_filter('post_row_actions', 'show_abe_exporter_on_posts_list', 10, 2);

?>
