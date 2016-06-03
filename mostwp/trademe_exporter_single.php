<?php

// get TM auth config
$TM_CONSUMER_KEY = getenv('TM_CONSUMER_KEY');
$TM_CONSUMER_SECRET = getenv('TM_CONSUMER_SECRET');

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


// custom format to TradeMe style


$xml  = <<<XML
<ListingRequest xmlns="http://api.trademe.co.nz/v1">
  <Category>3849</Category>
  <Title>Arty surprise</Title>
  <Description>
    <Paragraph>All true art lovers will buy this.</Paragraph>
  </Description>
  <StartPrice>7</StartPrice>
  <BuyNowPrice>9</BuyNowPrice>
  <Duration>Seven</Duration>
  <Pickup>Allow</Pickup>
  <IsBrandNew>true</IsBrandNew>
  <PhotoIds>
    <PhotoId>12345678</PhotoId>
  </PhotoIds>
  <ShippingOptions>
    <ShippingOption>
      <Type>Free</Type>
    </ShippingOption>
  </ShippingOptions>
  <PaymentMethods>
    <PaymentMethod>CreditCard</PaymentMethod>
    <PaymentMethod>Cash</PaymentMethod>
  </PaymentMethods>
</ListingRequest>
XML;

$xml = str_replace(
  array('BOOKSELLER_USERNAME', 'BOOKSELLER_PASSWORD'), 
  array(getenv('BOOKSELLER_USERNAME'), getenv('BOOKSELLER_PASSWORD')), 
  $xml
);

// make XML object out of sample XML

$book = new SimpleXMLElement($xml);

$book_to_export = $book->asXML();
//$book_to_export = html_entity_decode($book_to_export);
//$book_to_export = str_replace('null', '', $book_to_export);

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
function exportBookToTradeMe() {
  document.getElementById("results").innerHTML = 'request sent, awaiting response...';


</script>

<textarea style="width: 985px;height: 449px;" id="bookxml">
<?=$book_to_export;?>
</textarea>

<br />
<button onclick=exportBookToTradeMe();>Export to TradeMe.co.nz</button>
<br />

<textarea id="results" style="width: 985px;height: 200px;">
...results will appear here...
</textarea>
</html>
