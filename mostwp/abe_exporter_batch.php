<?php
//header("Content-Type: text/plain; charset=utf-8");

// get WP system loaded
require_once(dirname(__FILE__) . '/wp-load.php');

// head of AbeBooks XML schema 
$xml  = '<?xml version="1.0" encoding="ISO-8859-1"?>
<inventoryUpdateRequest version="1.0">
  <action name="bookupdate">
   <username>BOOKSELLER_USERNAME</username>
  <password>BOOKSELLER_PASSWORD</password>
 </action>
 <AbebookList>
'; // space left at end to keep alignment

// replace sensitive information from env vars
$xml = str_replace(
  array('BOOKSELLER_USERNAME', 'BOOKSELLER_PASSWORD'), 
  array(getenv('BOOKSELLER_USERNAME'), getenv('BOOKSELLER_PASSWORD')), 
  $xml
);

// template chunk to be replaced by book contents
// indented to keep xml alignment
$book_template = '
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
';

// get all published posts which have a price set
// TODO: filter out sold items
$exportable_posts_query = new WP_Query( 
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

// loop through exportable posts
while ( $exportable_posts_query->have_posts() ) :
    $exportable_posts_query->the_post();

    $post_id = get_the_ID();

    // get all the post meta (author, price, etc)
    $post_meta = get_post_meta($post_id);

    // new xml object based on template
    $book = new SimpleXMLElement($book_template);

    // TODO: check htmlentities() or using text outside textarea for better
    //       dealing with encoding issues

    // modify XML values as required
    $book->vendorBookID = $post_id;

    $book->title = get_the_title($post_id);

    //$title = utf_encode($title);

    $book->author = $post_meta['Author'][0];
    $book->publisher = str_replace('&', 'and', $post_meta['Publisher'][0]);
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
    $book->firstEdition = 'false';
    if ($post_meta['Edition'][0] == 'First') {
      $book->firstEdition = 'true';
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

    // remove trailing whitespace, including newlines
    $content = rtrim($content);

    $slug = $content_post->post_name;

    // links are not allowed on abes
    //$content = $content . ' More images of this book may be available at: http://visitmost.github.io/' . $slug;
    $content = $content . ' More images of this book may be available on our homepage';

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

    // each book is unique, so no combined listings for same copy
    $book->quantity = 1;

    // convert book node back to string representation of XML
    $single_book = $book->asXML();

    // remove XML header from individual book nodes and add to tree's string
    $xml .= '  ' . str_replace("<?xml version=\"1.0\"?>\n",'', $single_book);
endwhile;

// trailing XML footer
$xml  .= ' </AbebookList>
</inventoryUpdateRequest>
';

// convert compiled string back to XML 
$compiled_book = new SimpleXMLElement($xml);

// once more (needed?) XML back to string
$book_to_export = $compiled_book->asXML();
$book_to_export = html_entity_decode($book_to_export);
$book_to_export = str_replace('null', '', $book_to_export);
?>
<html>
<head>
    <meta charset="UTF-8" />
</head>

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
</script>

<textarea style="width: 985px;height: 449px;" id="bookxml">
<?=str_replace("and's", "'s", $book_to_export);?>
</textarea>

<br />

<button onclick=exportBookToAbeBooks();>Export <?=$exportable_posts_query->post_count;?> books to AbeBooks.com</button>

<br />

<textarea id="results" style="width: 985px;height: 200px;">
...results will appear here...
</textarea>

<br />
</html>

