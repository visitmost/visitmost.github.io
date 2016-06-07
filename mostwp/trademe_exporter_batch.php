<?php

// get WP system loaded
require_once(dirname(__FILE__) . '/wp-load.php');

// custom format to AbeBooks style
$csv_header[] = 'product_id_for_member';
$csv_header[] = 'sku';
$csv_header[] = 'photo_id_list';
$csv_header[] = 'update_active_listings';
$csv_header[] = 'stock_amount';
$csv_header[] = 'unlimited_stock';
$csv_header[] = 'category_id';
$csv_header[] = 'dvd_catalogue_id';
$csv_header[] = 'title';
$csv_header[] = 'subtitle';
$csv_header[] = 'body';
$csv_header[] = 'is_new';
$csv_header[] = 'attributes';
$csv_header[] = 'is_legal_notice_read';
$csv_header[] = 'start_price';
$csv_header[] = 'reserve_price';
$csv_header[] = 'buy_now_price';
$csv_header[] = 'is_sold_in_multiple_quantities';
$csv_header[] = 'is_shipping_price_per_quantity_sold';
$csv_header[] = 'fpo_amount';
$csv_header[] = 'fpo_duration';
$csv_header[] = 'fpo_to';
$csv_header[] = 'av_bidders_only';
$csv_header[] = 'auction_length';
$csv_header[] = 'auction_end_time';
$csv_header[] = 'delivery_pickup_allowed';
$csv_header[] = 'delivery_must_pickup';
$csv_header[] = 'delivery_is_free';
$csv_header[] = 'delivery_price';
$csv_header[] = 'payment_bank_deposit';
$csv_header[] = 'payment_credit_card';
$csv_header[] = 'payment_cash';
$csv_header[] = 'payment_safe_trader';
$csv_header[] = 'payment_other';
$csv_header[] = 'send_payment_instructions';
$csv_header[] = 'display_bold';
$csv_header[] = 'gallery';
$csv_header[] = 'gallery_plus';
$csv_header[] = 'feature';
$csv_header[] = 'super_feature';
$csv_header[] = 'donation_recipient';
$csv_header[] = 'folder';
$csv_header[] = 'exclude_shipping_promotion';
$csv_header[] = 'listing_footer_enabled';
$csv_header[] = 'length_cm';
$csv_header[] = 'width_cm';
$csv_header[] = 'height_cm';
$csv_header[] = 'weight_kg';

$csv_header = join(',', $csv_header);

echo $csv_header;

$blank_row = preg_replace("/[^,]+/", "", $csv_header);

$blank_row = explode(',', $blank_row);

// set defaults for TradeMe row
$blank_row[3] = 1; //update_active_listing
$blank_row[4] = 1; //stock amount
$blank_row[5] = 0; //unlimited_stock
$blank_row[11] = 0; //used item
$blank_row[20] = 7; // fixed price duration
$blank_row[21] = 'A'; // offer FPO to all watchers and bidders
$blank_row[22] = 1;// auth bidders only
$blank_row[23] = 7; // auction length
$blank_row[25] = 1; // pickup allowed
$blank_row[28] = '7.00=Untracked NZ wide;10.00=Tracked NZ wide'; // delivery options [ price = description ]
$blank_row[29] = 1; //allow bank transfer
$blank_row[30] = 1; //allow credit card
$blank_row[31] = 1; //allow cash

//TODO: allows length, width, height and weight (of shippable package) would be nice to include...

// query posts (everything with a price set)
$events_query = new WP_Query( 
  array(
    'post_type' => 'post',
    'posts_per_page' => -1,
    'meta_query' => array( 
      array( 
        'key' => 'Price' 
      ) 
    )
  ) 
);


while ( $events_query->have_posts() ) :
    $book_row = $blank_row;

    $events_query->the_post();

    $post_id = get_the_ID();

    $book_row[0] = $post_id;

    // get all the post meta
    $post_meta = get_post_meta($post_id);

    $year = $post_meta['Year'][0];

    //category: < 1950 is 1822, modern is 1823
    if ($year < 1950) {
      $book_row[6] = 1822; 
    } else {
      $book_row[6] = 1823; 
    }

    $warnings = [];

    if ($post_meta['Price'][0] == '') {
      $warnings['Price'] = 'not set';
    }

    $title = str_replace(',', ' ', get_the_title($post_id));
 
    $title = substr($title,0,50);
 
    $book_row[8] = $title; // append author, year to this

    $book->subject = $post_meta['Subject'][0];

    // start price
    $book_row[14] = $post_meta['Price'][0];
    
    // buy now price
    $book_row[16] = $post_meta['Price'][0];

    // fixed price offer
    $book_row[19] = $post_meta['Price'][0] - 1;

    

    $book->dustJacket = $post_meta['Dust jacket condition'][0];

    // update binding attribute and values
    if ( $post_meta['Binding'][0] == 'Hardback' || $post_meta['Binding'][0] == 'Hardcover') {
      $book->binding = 'Hardback';
    } else if ( $post_meta['Binding'][0] == 'Paperback' || $post_meta['Binding'][0] == 'Softcover' ) {
      $book->binding = 'Paperback';
      $book->binding['type'] = 'soft';
    } else {
      echo 'NO BINDING DEFINED FOR THIS BOOK!';
      throw new Exception('NO BINDING DEFINED!');
    }

    // flag first editions
    if ($post_meta['Edition'][0] == 'First') {
      $book->firstEdition = 'true';
    } else {
      $book->firstEdition = 'false';
    }

    $book->signed = $post_meta['Signed'][0];
    $book->booksellerCatalogue = $post_meta['Subject'][0];

    // compile a pretty description, including meta already known + actual post description
    $content_post = get_post($post_id);
    $content = $content_post->post_content;
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);
    $content = str_replace('&', 'and', $content);
    $content = strip_tags ($content);
    $content = str_replace("&#8216;","'", $content);
    $content = str_replace("&#8217;","'", $content);
    $content = str_replace("#8216;","'", $content);
    $content = str_replace("#8217;","'", $content);
    $content = str_replace("&#8220;",'"', $content);
    $content = str_replace("&#8221;",'"', $content);
    $content = str_replace("#8220;",'"', $content);
    $content = str_replace("#8221;",'"', $content);
    $content = str_replace("#038;",'', $content);
    $content = str_replace("#8211;",'', $content);
    $content = str_replace(":",' ', $content);
    $content = rtrim($content);

    $slug = $content_post->post_name;

    
    $content =  $post_meta['Author'][0] . '.  ' . $post_meta['Publisher'][0]  . '. '. $post_meta['Published location'][0]  . '. ' . $year . '. ' . $content;

    $content = $content . '. More images of this book may be available at http://visitmost.github.io/' . $slug;

    $book_row[10] = str_replace(',', ' ', $content);


    $book->bookCondition = $post_meta['Condition'][0];
    $book->isbn = $post_meta['ISBN'][0];
    $book->size = $post_meta['Size'][0];
    $book->jacketCondition = $post_meta['Dust jacket condition'][0];
    $book->inscription = $post_meta['Inscription'][0];

    if ($post_meta['Book type'][0] == 'Ex-Library') {
      $book->bookType = 'Ex-Library';
    } else {
      $book->bookType = '';
    }

    // QTY always hardcoded to 1 currently
    $book->quantity = 1;

    echo '<br />';
    echo join(',', $book_row);
endwhile;


$xml  .= <<<XML
 </AbebookList>
</inventoryUpdateRequest>
XML;

$compiled_book = new SimpleXMLElement($xml);


$book_to_export = $compiled_book->asXML();
$book_to_export = html_entity_decode($book_to_export);
$book_to_export = str_replace('null', '', $book_to_export);

$book_images = [];

$dom = new domDocument;
$dom->loadHTML($content_post->post_content);
$dom->preserveWhiteSpace = false;
$images = $dom->getElementsByTagName('img');

if ($send_images == True){
  include('Net/SFTP.php');

  $sftp = new Net_SFTP('ftp.abebooks.com');
  if (!$sftp->login(getenv('ABE_FTP_USERNAME'), getenv('ABE_FTP_PASSWORD'))) {
      exit('Login Failed');
  }
}

$x = 1;
foreach ($images as $image) {
  $image_url = $image->getAttribute('src');
  $book_images[] = $image_url;

  $remote_file = $post_id . '_' . $x . '.jpg';

  if ($send_images == True){
    $local_image_url = str_replace('http://localhost:8888', '', $image_url);

    if ($x < 6){
      $sftp->put($remote_file, '/Users/leon/visitmost.github.io/mostwp' . $local_image_url, NET_SFTP_LOCAL_FILE);
    }
  }

  $x += 1;
}

if ( ! add_post_meta( $post_id, 'Abe Images Updated', date('Y-m-d H:i:s'), true ) ) { 
   update_post_meta( $post_id, 'Abe Images Updated', date('Y-m-d H:i:s') );
}


?>

<html>
<head>
		<meta charset="ISO-8859-1" />
</head>

<?
if (count($warnings) > 0){
  print_r($warnings);
}
?>

<script>
function exportBookToAbeBooks() {
  document.getElementById("results").innerHTML = 'request sent, awaiting response...';

  var r = new XMLHttpRequest(); 

  r.open("POST", "https://inventoryupdate.abebooks.com:10027", true); 

  r.onreadystatechange = function () { 
    if (r.readyState != 4 || r.status != 200) return; 
    if (r.responseText.indexOf('<code>600</code>') > -1) {
      document.getElementById("results").style.backgroundColor = '#99ff66';
    } else {
      document.getElementById("results").style.backgroundColor = 'orange';
    }

    document.getElementById("results").innerHTML = r.responseText;
  } 

  xml_content = document.getElementById("bookxml").value;

  r.send(xml_content);
}

function transferImagesViaFTP() {
  window.location = window.location.href + '&transfer_files=TRUE';
}
</script>

<textarea style="width: 985px;height: 449px;" id="bookxml">
<?=str_replace("and's", "'s", $book_to_export);?>
</textarea>

<br />
<button onclick=exportBookToAbeBooks();>Export <?=$events_query->post_count;?> books to AbeBooks.com</button>

<button onclick=transferImagesViaFTP();>Transfer images via sFTP</button>
<br />

<textarea id="results" style="width: 985px;height: 200px;">
...results will appear here...
</textarea>

<br />

<? foreach($book_images as $book_image): ?>

<img src="<?=$book_image;?>" style="height:200px;"/>

<? endforeach; ?>

</html>

