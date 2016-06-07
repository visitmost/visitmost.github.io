<?php

// get WP system loaded
require_once(dirname(__FILE__) . '/wp-load.php');

// custom format to AbeBooks style
$xml  = <<<XML
<?xml version="1.0" encoding="ISO-8859-1"?>
<inventoryUpdateRequest version="1.0">
  <action name="bookupdate">
   <username>BOOKSELLER_USERNAME</username>
  <password>BOOKSELLER_PASSWORD</password>
 </action>
 <AbebookList>

XML;

$xml = str_replace(
  array('BOOKSELLER_USERNAME', 'BOOKSELLER_PASSWORD'), 
  array(getenv('BOOKSELLER_USERNAME'), getenv('BOOKSELLER_PASSWORD')), 
  $xml
);


$send_images = False;

if(isset($_GET['transfer_files'])){
  $send_images = True;
}

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
    $events_query->the_post();

//    echo get_the_title() . '<br/>';

    $post_id = get_the_ID();

    // get all the post meta
    $post_meta = get_post_meta($post_id);

    $warnings = [];

    if ($post_meta['Price'][0] == '') {
      $warnings['Price'] = 'not set';
    }


$book = <<<XML
  <Abebook>
    <transactionType>add</transactionType>
    <vendorBookID>999999</vendorBookID>
    <author>AUTHORAUTHORAUTHOR</author>
    <title>TITLETITITLETITLE</title>
    <publisher>PUBLISHERPUBLISHERPUBLISHER</publisher>
    <subject>SUBJECTSUBJECTSUBJECT</subject>
    <price currency="NZD">999999999999999.99</price>
    <dustJacket>false</dustJacket>
    <binding type="hard">BINDINGBINDINGBINDINGBINDING</binding>
    <firstEdition>FIRSTEDITIONFIRSTEDITIONFIRSTEDITION</firstEdition>
    <signed>false</signed>
    <booksellerCatalogue>CATEGORYCATEGORYCATEGORYCATEGORYCATEGORY</booksellerCatalogue>
    <description>DESCRIPTIONDESCRIPTIONDESCRIPTIONDESCRIPTIONDESCRIPTIONDESCRIPTION</description>
    <bookCondition>CONDITIONCONDITIONCONDITIONCONDITIONCONDITION</bookCondition>
    <publishPlace>PUBLISHPLACEPUBLISHPLACEPUBLISHPLACEPUBLISHPLACEPUBLISHPLACE</publishPlace>
    <publishYear>YEARYEARYEARYEARYEAR</publishYear>
    <bookType>BOOKTYPEBOOKTYPEBOOKTYPE</bookType>
    <quantity amount="1">1</quantity>
    <isbn>000000000000000000</isbn>
    <size>999999999999999999999</size>
    <jacketCondition>JACKETCONDITIONJACKETCONDITIONJACKETCONDITIONJACKETCONDITION</jacketCondition>
    <inscription>INSCRIPTIONINSCRIPTIONINSCRIPTIONINSCRIPTIONINSCRIPTION</inscription>
  </Abebook>
XML;


    // make XML object out of sample XML
    $book = new SimpleXMLElement($book);

    // modify XML values as required
    $book->vendorBookID = $post_id;

    $title = get_the_title($post_id);
    $title = str_replace("&#8217;","'", $title);
    $book->title = $title;
    $book->author = $post_meta['Author'][0];
    $book->publisher = $post_meta['Publisher'][0];
    $book->subject = $post_meta['Subject'][0];
    $book->price = $post_meta['Price'][0];
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

    //$slug = $content_post->post_name;

    $content = $content . ' More images of this book may be available on our website.';

    $book->description = $content;
    $book->bookCondition = $post_meta['Condition'][0];
    $book->publishPlace = $post_meta['Published location'][0];
    $book->publishYear = $post_meta['Year'][0];
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

    //echo $book->asXML();

    $single_book = $book->asXML();

    $xml .= '  ' . str_replace("<?xml version=\"1.0\"?>\n",'', $single_book);
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

