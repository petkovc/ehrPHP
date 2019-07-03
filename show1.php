<?php
error_reporting(E_ERROR | E_PARSE);
include ('connection.php');

$compo = $ehrserver->get_composition($_GET['uid'], 'json');

$xml = new DOMDocument;
$xml->loadXML($compo); // with load() doesnt work should be loadXML!

$xsl = new DOMDocument;
$xsl->substituteEntities = true;
$xsl->load('openEHR_RMtoHTML.xsl');

$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl);

$html = $proc->transformToXML($xml);

//print_r($compo->version->data->content->description->items[0]->items[0]->value->value);
print_r($compo->version->data->content->description->items[1]->items[3]);

//$array = (array)$compo;
//echo $array['ChargeableRateInfo']['@surchargetotal'];

//print_r($xml);
//echo $compo->version->data->content->description->items[1]->items[2]->value->value;
  foreach ($xml as $trend)  
  {         
   echo $trend->items."\n";    
  } 
?>

