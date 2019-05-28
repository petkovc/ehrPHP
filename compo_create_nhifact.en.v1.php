<?php

include ('connection.php');

/*
when passing the template uid as param
$template = $ehrserver->get_template($_GET['uid']);
$template = preg_replace("/\r|\n/", "", $template); // removes new lines to remove the multiline string problem in JS
*/

function generate_uuid()
{
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	);
}

// doc create
if(isset($_POST['submit']))
{
  // get params map without the arrayfication of PHP when it finds [] in the param name
  $params = array();

  $qs = file_get_contents ('php://input');
  $paramsx = explode ('&', $qs);

  foreach ($paramsx as $p)
  {
    $nv = explode ('=', $p, 2);
    $name = urldecode ($nv[0]);
    $value = urldecode ($nv[1]);
    $params[htmlspecialchars ($name)] = htmlspecialchars ($value);
  }

  //print_r($params);

  // document with tags
  //$doc = readfile('./nhif_for_parse.xml');

  $flines = file('./nhifact.xml');
  $doc = "";
  for ($i = 0; $i < sizeof($flines); $i++)
  {
     $doc .= $flines[$i];
  }

  // pepare data for mapping
  $now = new DateTime('NOW');
  $iso8601date = $now->format('c'); // ISO8601 formated datetime

  $allowSub=$params['/description[at0001]/items[at0004]/items[at0016]/value']=='on'? '1' : '0';

  $mappings = array(
    '[[CONTRIBUTION:::UUID]]' => generate_uuid(),
    '[[COMMITTER_ID:::UUID]]' => generate_uuid(),
    '[[COMMITTER_NAME:::STRING]]' => 'Petko Kovachev',
    '[[TIME_COMMITTED:::DATETIME]]' => $iso8601date,
    '[[VERSION_ID:::VERSION_ID]]' => generate_uuid() .'::PHP.TEST::1',
    '[[COMPOSER_ID:::UUID]]' => generate_uuid(),
    '[[COMPOSER_NAME:::STRING]]' => 'Dr. Petko Kovachev',
    '[[COMPOSITION_DATE:::DATETIME]]' => $iso8601date,
    '[[COMPOSITION_SETTING_VALUE:::STRING]]' => 'FMI Hospital',
    '[[COMPOSITION_SETTING_CODE:::STRING]]' => '229',

    '[[OBS_ORIGIN:::DATETIME]]' => $iso8601date,
    '[[EVN_TIME:::DATETIME]]' => $iso8601date,

    '[[typeOfPrescription:::DV_ORDINAL]]' => $params['/description[at0001]/items[at0002]/items[at0005]/value'],
	'[[prescriptionNo:::DV_TEXT]]' => $params['/description[at0001]/items[at0002]/items[at0008]/value'],
	'[[prescriptionDate:::DV_DATE]]' => $params['/description[at0001]/items[at0002]/items[at0009]/value'],
	'[[doctorUin:::DV_TEXT]]' => $params['/description[at0001]/items[at0002]/items[at0017]/items[at0018]/value'],
	'[[doctorSpecialty:::DV_TEXT]]' => $params['/description[at0001]/items[at0002]/items[at0017]/items[at0021]/value'],
	'[[heRegistrationNo:::DV_TEXT]]' => $params['/description[at0001]/items[at0002]/items[at0017]/items[at0019]/value'],
	'[[ambulatorySheetNo:::DV_TEXT]]' => $params['/description[at0001]/items[at0002]/items[at0017]/items[at0020]/value'],
	'[[ambSheetPrescriptionNo:::DV_TEXT]]' => $params['/description[at0001]/items[at0002]/items[at0017]/items[at0022]/value'],
	'[[EGN:::DV_TEXT]]' => $params['/description[at0001]/items[at0002]/items[at0003]/items[at0010]/value'],
	'[[prescriptionBookletNo:::DV_TEXT]]' => $params['/description[at0001]/items[at0002]/items[at0003]/items[at0011]/value'],
	'[[drugNHIFCode:::DV_TEXT]]' => $params['/description[at0001]/items[at0004]/items[at0012]/value'],
	'[[codeICD:::DV_TEXT]]' => $params['/description[at0001]/items[at0004]/items[at0013]/value'],
	'[[prescribedQuantity:::DV_COUNT]]' => $params['/description[at0001]/items[at0004]/items[at0023]/value'],
	'[[prescribedMeasure:::DV_ORDINAL]]' => $params['/description[at0001]/items[at0004]/items[at0025]/value'],
	'[[numberOfDays:::DV_COUNT]]' => $params['/description[at0001]/items[at0004]/items[at0024]/value'],
	'[[signatureRepeats:::DV_COUNT]]' => $params['/description[at0001]/items[at0004]/items[at0032]/value'],
	'[[signatureQuantity:::DV_COUNT]]' => $params['/description[at0001]/items[at0004]/items[at0031]/value'],
	'[[signatureOccurrence:::DV_ORDINAL]]' => $params['/description[at0001]/items[at0004]/items[at0034]/value'],
//	'[[Allow susbstitution?:::DV_BOOLEAN]]' => $params['/description[at0001]/items[at0004]/items[at0016]/value'],
	'[[Allow susbstitution?:::DV_BOOLEAN]]' => $allowSub,
	
    '[[Duracion:::DV_DURATION_VALUE]]' => $duration,
   '[[Intensidad:::CODEDTEXT_VALUE]]' => $names[$params['/data[at0001]/events[at0002]/data[at0003]/items[at0010]/value/defining_code']],
    '[[Intensidad:::CODEDTEXT_CODE]]' => $params['/data[at0001]/events[at0002]/data[at0003]/items[at0010]/value/defining_code']
  );

  //print_r($mappings);

  foreach ($mappings as $tag => $value)
  {
    $doc = str_replace($tag, $value, $doc);
  }

  // commit to EHRServer
  $res = $ehrserver->commit_composition($doc, $params['ehr_uid'], 'System-FMI', 'PHP.TEST');

  print_r($res);
  echo $res->type;
 
  if ($res->type == 'AA') // OK!
  {
    header("Location: ehr_show.php?uid=".$params['ehr_uid']);
  }
  else
  {echo $doc;
    echo 'Ocurrió un error en el commit';

  }

  die();
}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Create Doc</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">

    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">

    <!-- parseXML doesnt wirk with slim -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <!--<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>

    <style>
      #templates tbody tr {
        cursor: pointer;
      }
    </style>
  </head>

  <body>
    <?php include('top.php'); ?>

    <div class="container-fluid">
      <div class="row">
        <?php include('menu.php'); ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Create Doc</h1>
          </div>

          <!-- generated using CoT UI generator http://server001.cloudehrserver.com/cot/opt/html_form_generator -->
          <form class="container" method="post" action="">
            <input type="hidden" name="ehr_uid" value="<?=$_GET['ehr_uid']?>" />
     <div class="container">
      <h1>nhifact.en.v1</h1>
      <div class="COMPOSITION">
        <label>Nhif comp</label>
        <div class="ACTION">
          <label>Nhif medication</label>
          <div class="ITEM_TREE">
            <label>Tree</label>
            <div class="CLUSTER">
              <label>PrescriptionHeader</label>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">typeOfPrescription</label>
                <select name="/description[at0001]/items[at0002]/items[at0005]/value" class="DV_ORDINAL form-control">
                  <option value=""></option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                </select>
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">prescriptionNo</label>
                <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0008]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">prescriptionDate</label>
                <input type="date" name="/description[at0001]/items[at0002]/items[at0009]/value" class="DV_DATE form-control" />
              </div>
              <div class="CLUSTER">
                <label>Sender</label>
                <div class="ELEMENT form-group row">
                  <label class="col-md-2 col-form-label">doctorUin</label>
                  <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0018]/value" />
                </div>
                <div class="ELEMENT form-group row">
                  <label class="col-md-2 col-form-label">doctorSpecialty</label>
                  <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0021]/value" />
                </div>
                <div class="ELEMENT form-group row">
                  <label class="col-md-2 col-form-label">heRegistrationNo</label>
                  <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0019]/value" />
                </div>
                <div class="ELEMENT form-group row">
                  <label class="col-md-2 col-form-label">ambulatorySheetNo</label>
                  <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0020]/value" />
                </div>
                <div class="ELEMENT form-group row">
                  <label class="col-md-2 col-form-label">ambSheetPrescriptionNo</label>
                  <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0022]/value" />
                </div>
              </div>
              <div class="CLUSTER">
                <label>Patient</label>
                <div class="ELEMENT form-group row">
                  <label class="col-md-2 col-form-label">EGN</label>
                  <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0003]/items[at0010]/value" />
                </div>
                <div class="ELEMENT form-group row">
                  <label class="col-md-2 col-form-label">prescriptionBookletNo</label>
                  <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0003]/items[at0011]/value" />
                </div>
              </div>
            </div>
            <div class="CLUSTER">
              <label>PrescriptionDrug</label>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">drugNHIFCode</label>
                <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0004]/items[at0012]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">codeICD</label>
                <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0004]/items[at0013]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">prescribedQuantity</label>
                <input type="number" class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0023]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">prescribedMeasure</label>
                <select name="/description[at0001]/items[at0004]/items[at0025]/value" class="DV_ORDINAL form-control">
                  <option value=""></option>
                  <option value="0">Pack</option>
                  <option value="1">Ampolules</option>
                  <option value="2">Syringes</option>
                  <option value="3">Bottles</option>
                  <option value="4">Tablets</option>
                </select>
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">numberOfDays</label>
                <input type="number" class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0024]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">signatureRepeats</label>
                <input type="number" class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0032]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">signatureQuantity</label>
                <input type="number" class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0031]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">signatureOccurrence</label>
                <select name="/description[at0001]/items[at0004]/items[at0034]/value" class="DV_ORDINAL form-control">
                  <option value=""></option>
                  <option value="0">Daily</option>
                  <option value="1">Weekly</option>
                  <option value="2">Monthly</option>
                  <option value="3">Quaterly</option>
                  <option value="4">Half yearly</option>
                  <option value="5">Yearly</option>
                </select>
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">Allow susbstitution?</label>
                <input type="checkbox" name="/description[at0001]/items[at0004]/items[at0016]/value" class="DV_BOOLEAN" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>             <button type="submit" name="submit" class="btn btn-primary btn-block">Create</button>
          </form>

        </main>
      </div>
    </div>

    <!-- Icons -->
    <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
    <script>
      feather.replace();

      /*
      $(document).ready(function($) {

        // The issue with JS rendering is the access to the terminoogies
        // We need to parse first into an structure with an API then generate the UID
        // And maybe do the parser on PHP to help JS, but not now.
        var xml = '<?=$template?>';
        xmlDoc = $.parseXML(xml);
        $xmlDoc = $(xmlDoc);

        //console.log($xmlDoc);
        //console.log($xmlDoc.find('definition'));
        //console.log($xmlDoc.find('definition').children());

        var definition = $xmlDoc.find('definition').children();
      });

      var render_obj = function(obj)
      {
        // TDB
        for (i=0; i<obj.length; i++)
        {
          console.log(obj[i], obj[i].children);
        }
      };

      var render_attr = function(attr)
      {
        // TBD
      }
      */
    </script>
  </body>
</html>
