<?php

// get WP system loaded
require_once(dirname(__FILE__) . '/wp-load.php');

$post_id = $_GET['id'];

// get all the post meta
$post_meta = get_post_meta($post_id);

// warnings
$warnings = [];

if ($post_meta['Price'][0] == '') {
  $warnings['Price'] = 'not set';
}


// custom format to AbeBooks style

$xml  = <<<XML
<?xml version="1.0" encoding="ISO-8859-1"?>
<inventoryUpdateRequest version="1.0">
  <action name="bookupdate">
   <username>BOOKSELLER_USERNAME</username>
  <password>BOOKSELLER_PASSWORD</password>
 </action>
 <AbebookList>
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
     <quantity amount="1">1</quantity>
   </Abebook>
 </AbebookList>
</inventoryUpdateRequest>
XML;

$xml = str_replace(
  array('BOOKSELLER_USERNAME', 'BOOKSELLER_PASSWORD'), 
  array(getenv('BOOKSELLER_USERNAME'), getenv('BOOKSELLER_PASSWORD')), 
  $xml
);

// make XML object out of sample XML

$book = new SimpleXMLElement($xml);

// modify XML values as required

$book->AbebookList->Abebook->vendorBookID = $post_id;

$title = get_the_title($post_id);
$title = str_replace("&#8217;","'", $title);
$book->AbebookList->Abebook->title = $title;

$book->AbebookList->Abebook->author = $post_meta['Author'][0];
$book->AbebookList->Abebook->publisher = $post_meta['Publisher'][0];
$book->AbebookList->Abebook->subject = $post_meta['Subject'][0];
$book->AbebookList->Abebook->price = $post_meta['Price'][0];
$book->AbebookList->Abebook->dustJacket = $post_meta['Dust jacket condition'][0];

// update binding attribute and values

if ( $post_meta['Binding'][0] == 'Hardback' || $post_meta['Binding'][0] == 'Hardcover') {
  $book->AbebookList->Abebook->binding = 'Hardback';
} else if ( $post_meta['Binding'][0] == 'Paperback' || $post_meta['Binding'][0] == 'Softcover' ) {
  $book->AbebookList->Abebook->binding = 'Paperback';
  $book->AbebookList->Abebook->binding['type'] = 'soft';
} else {
  echo 'NO BINDING DEFINED FOR THIS BOOK!';
  throw new Exception('NO BINDING DEFINED!');
}

// flag first editions
if ($post_meta['Edition'][0] == 'First') {
  $book->AbebookList->Abebook->firstEdition = 'true';
} else {
  $book->AbebookList->Abebook->firstEdition = 'false';
}

$book->AbebookList->Abebook->signed = $post_meta['Signed'][0];
$book->AbebookList->Abebook->booksellerCatalogue = $post_meta['Subject'][0];

// compile a pretty description, including meta already known + actual post description

$content_post = get_post($post_id);
$content = $content_post->post_content;
$content = apply_filters('the_content', $content);
$content = str_replace(']]>', ']]&gt;', $content);
$content = str_replace('&', 'and', $content);
$content = strip_tags ($content);
$content = str_replace("&#8216;","'", $content);
$content = str_replace("&#8217;","'", $content);
$content = str_replace("&#8220;",'"', $content);
$content = str_replace("&#8221;",'"', $content);
$content = str_replace("#038;",'', $content);
$content = str_replace(":",' ', $content);
//$content = str_replace("+",' ', $content);
//$content = str_replace("-",' ', $content);

$book->AbebookList->Abebook->description = $content;

$book->AbebookList->Abebook->bookCondition = $post_meta['Condition'][0];
$book->AbebookList->Abebook->publishPlace = $post_meta['Published location'][0];
$book->AbebookList->Abebook->publishYear = $post_meta['Year'][0];
$book->AbebookList->Abebook->isbn = $post_meta['ISBN'][0];
$book->AbebookList->Abebook->size = $post_meta['Size'][0];
$book->AbebookList->Abebook->jacketCondition = $post_meta['Dust jacket condition'][0];
$book->AbebookList->Abebook->inscriptoin = $post_meta['Inscription'][0];


// TODO: Book Type (ie, ex library): http://www.abebooks.com/docs/seller-help/inventory-update-api-user-guide.pdf

// QTY always hardcoded to 1 currently

$book->AbebookList->Abebook->quantity = 1;


$book_to_export = $book->asXML();
$book_to_export = html_entity_decode($book_to_export);

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
    //alert("Success: " + r.responseText); 
  }; 

//  r.setRequestHeader('Content-type', 'text/xml');
  //r.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

  //xml_content = document.getElementById("bookxml").innerHTML;
  xml_content = document.getElementById("bookxml").value;

  r.send(xml_content);
}

</script>

<textarea style="width: 985px;height: 449px;" id="bookxml">
<?=$book_to_export;?>
</textarea>

<br />
<button onclick=exportBookToAbeBooks();>Export to AbeBooks.com</button>
<br />

<textarea id="results" style="width: 985px;height: 200px;">
...results will appear here...
</textarea>
</html>
