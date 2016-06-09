<?php

// get WP system loaded
require_once(dirname(__FILE__) . '/wp-load.php');

// custom format to AbeBooks style
$csv_header[] = '*Action(SiteID=US|Country=US|Currency=USD|Version=941)';
$csv_header[] = '*Category';
$csv_header[] = '*Title';
$csv_header[] = 'Subtitle';
$csv_header[] = '*Description';
$csv_header[] = '*ConditionID';
$csv_header[] = 'PicURL';
$csv_header[] = '*Quantity';
$csv_header[] = '*Format';
$csv_header[] = '*StartPrice';
$csv_header[] = 'BuyItNowPrice';
$csv_header[] = '*Duration';
$csv_header[] = 'ImmediatePayRequired';
$csv_header[] = '*Location';
$csv_header[] = 'GalleryType';
$csv_header[] = 'PayPalAccepted';
$csv_header[] = 'PayPalEmailAddress';
$csv_header[] = 'PaymentInstructions';
$csv_header[] = 'StoreCategory';
$csv_header[] = 'ShippingDiscountProfileID';
$csv_header[] = 'DomesticRateTable';
$csv_header[] = 'ShippingType';
$csv_header[] = 'ShippingService-1:Option';
$csv_header[] = 'ShippingService-1:Cost';
$csv_header[] = 'ShippingService-1:Priority';
$csv_header[] = 'ShippingService-1:ShippingSurcharge';
$csv_header[] = 'ShippingService-2:Option';
$csv_header[] = 'ShippingService-2:Cost';
$csv_header[] = 'ShippingService-2:Priority';
$csv_header[] = 'ShippingService-2:ShippingSurcharge';
$csv_header[] = 'DispatchTimeMax';
$csv_header[] = 'CustomLabel';
$csv_header[] = 'ReturnsAcceptedOption';
$csv_header[] = 'RefundOption';
$csv_header[] = 'ReturnsWithinOption';
$csv_header[] = 'ShippingCostPaidByOption';
$csv_header[] = 'AdditionalDetails';
$csv_header[] = 'ShippingProfileName';
$csv_header[] = 'ReturnProfileName';
$csv_header[] = 'PaymentProfileName';

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
    'order' => 'DESC',
    'category_name' => 'book-catalog',
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


    // start price
    $book_row[14] = $post_meta['Price'][0];
    
    // buy now price
    $book_row[16] = $post_meta['Price'][0];

    // fixed price offer
    $book_row[19] = $post_meta['Price'][0] - 1;

    // compile a pretty description, including meta already known + actual post description
    $content_post = get_post($post_id);
    $content = $content_post->post_content;

    $book_images = [];

    $dom = new domDocument;
    //$dom->loadHTML(htmlentities($content));
    $dom->loadHTML($content);
    $dom->preserveWhiteSpace = false;
    $images = $dom->getElementsByTagName('img');

    // if images are empty, then only featured exists, use that instead
    if ($images->length == 0) {
      $image_dom = new domDocument;
      $image_dom->loadHTML(get_the_post_thumbnail( $post_id, 'full' ));
      $images = $image_dom->getElementsByTagName('img');
        foreach ($images as $image) {
          $image_url = $image->getAttribute('src');

          $book_images[] = str_replace('http://localhost:8888', 'http://visitmost.github.io', $image_url);
      }
    } else {
      // TODO: limit images to 20
      foreach ($images as $image) {
        $image_url = $image->getAttribute('src');

        $book_images[] = str_replace('http://localhost:8888', 'http://visitmost.github.io', $image_url);
      }
    }

    $book_row[2] = join(';', $book_images);

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


    //$book->dustJacket = $post_meta['Dust jacket condition'][0];
    //$book->subject = $post_meta['Subject'][0];

    //// update binding attribute and values
    //if ( $post_meta['Binding'][0] == 'Hardback' || $post_meta['Binding'][0] == 'Hardcover') {
    //  $book->binding = 'Hardback';
    //} else if ( $post_meta['Binding'][0] == 'Paperback' || $post_meta['Binding'][0] == 'Softcover' ) {
    //  $book->binding = 'Paperback';
    //  $book->binding['type'] = 'soft';
    //} else {
    //  echo 'NO BINDING DEFINED FOR THIS BOOK!';
    //  throw new Exception('NO BINDING DEFINED!');
    //}

    //// flag first editions
    //if ($post_meta['Edition'][0] == 'First') {
    //  $book->firstEdition = 'true';
    //} else {
    //  $book->firstEdition = 'false';
    //}

    //$book->signed = $post_meta['Signed'][0];
    //$book->booksellerCatalogue = $post_meta['Subject'][0];
    //$book->bookCondition = $post_meta['Condition'][0];
    //$book->isbn = $post_meta['ISBN'][0];
    //$book->size = $post_meta['Size'][0];
    //$book->jacketCondition = $post_meta['Dust jacket condition'][0];
    //$book->inscription = $post_meta['Inscription'][0];
    //// QTY always hardcoded to 1 currently
    //$book->quantity = 1;

    //if ($post_meta['Book type'][0] == 'Ex-Library') {
    //  $book->bookType = 'Ex-Library';
    //} else {
    //  $book->bookType = '';
    //}



    echo '<br />';
    echo join(',', $book_row);
endwhile;




?>

