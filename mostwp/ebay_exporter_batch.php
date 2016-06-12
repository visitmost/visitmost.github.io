<?php

// get WP system loaded
require_once(dirname(__FILE__) . '/wp-load.php');

// custom format to AbeBooks style
$csv_header[] = '*Action(SiteID=US|Country=US|Currency=USD|Version=941)';//0
$csv_header[] = '*Category';//1
$csv_header[] = '*Title';//2
$csv_header[] = '*Description';//3
$csv_header[] = 'PicURL';//4
$csv_header[] = '*Quantity';//5
$csv_header[] = '*Format';//6
$csv_header[] = '*StartPrice';//7
$csv_header[] = '*Duration';//8
$csv_header[] = 'ImmediatePayRequired';//9
$csv_header[] = '*Location';//10
$csv_header[] = 'PayPalAccepted';//11
$csv_header[] = 'PayPalEmailAddress';//12
$csv_header[] = 'ShippingType';//13
$csv_header[] = 'ShippingService-1:Option';//14
$csv_header[] = 'ShippingService-1:Cost';//15
$csv_header[] = 'DispatchTimeMax';//16
$csv_header[] = 'CustomLabel';//17
$csv_header[] = 'ReturnsAcceptedOption';//18
$csv_header[] = 'RefundOption';//19
$csv_header[] = 'ReturnsWithinOption';//20
$csv_header[] = 'ShippingCostPaidByOption';//21
$csv_header[] = 'AdditionalDetails';//22
$csv_header[] = 'ShippingProfileName';//23
$csv_header[] = 'ReturnProfileName';//24
$csv_header[] = 'PaymentProfileName';//25
//$csv_header[] = 'BuyItNowPrice';
//$csv_header[] = 'Subtitle';
//$csv_header[] = '*ConditionID';
//$csv_header[] = 'GalleryType';
//$csv_header[] = 'PaymentInstructions';
//$csv_header[] = 'StoreCategory';
//$csv_header[] = 'ShippingDiscountProfileID';
//$csv_header[] = 'DomesticRateTable';
//$csv_header[] = 'ShippingService-1:Priority';
//$csv_header[] = 'ShippingService-1:ShippingSurcharge';
//$csv_header[] = 'ShippingService-2:Option';
//$csv_header[] = 'ShippingService-2:Cost';
//$csv_header[] = 'ShippingService-2:Priority';
//$csv_header[] = 'ShippingService-2:ShippingSurcharge';

$csv_header = join(',', $csv_header);

echo $csv_header;

$blank_row = preg_replace("/[^,]+/", "", $csv_header);

$blank_row = explode(',', $blank_row);

// set defaults for TradeMe row
$blank_row[0] = 'VerifyAdd'; //VerifyAdd for test run, Add for realsies
$blank_row[1] = 29223; //category - 29223 = Antiques and Manuscripts, easy bucket to start with
$blank_row[5] = 1; // qty
$blank_row[6] = 'FixedPrice'; // FixedPrice or Auction
$blank_row[10] = 'New Zealand'; // location
$blank_row[11] = 1; // PayPal accepted
$blank_row[12] = 'leon@mostengineers.com'; // PayPal email
$blank_row[13] = 'Flat'; // shipping type Flat or Calculated
$blank_row[14] = 'Other'; // type of shipping - no NZ options
//$blank_row[14] = 'New Zealand Post International Air (Untracked)'; // shipping type Flat or Calculated
$blank_row[15] = 6.00; // cost of above shipping option
$blank_row[16] = 2; // max dispatch time (business days)
$blank_row[18] = 'ReturnsNotAccepted'; // ReturnsAccepted or ReturnsNotAccepted


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

    // get all the post meta
    $post_meta = get_post_meta($post_id);

    $title = str_replace(',', ' ', get_the_title($post_id));
    $title = substr($title,0,80); //max 80 chars for eBay
    $book_row[2] = $title; // TODO: append author, year to this if less than 80 chars...

    //$book_row[7] = $post_meta['ISBN'][0]; // ISBN
    $book_row[7] = $post_meta['Price'][0];// start price
    $book_row[8] = 10;// duration
    $book_row[9] = 0;// immediate payment required (Premier and Business accounts only)
    
    // compile a pretty description, including meta already known + actual post description
    $content_post = get_post($post_id);
    $content = $content_post->post_content;

    $book_images = [];

    $dom = new domDocument;
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

    $book_row[4] = join('|', $book_images);

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

    $book_row[3] = str_replace(',', ' ', $content);

    echo '<br />';
    echo join(',', $book_row);
endwhile;

?>

