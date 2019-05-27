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

  $flines = file('./nhif_for_parse.xml');
  $doc = "";
  for ($i = 0; $i < sizeof($flines); $i++)
  {
     $doc .= $flines[$i];
  }

  // pepare data for mapping
  $now = new DateTime('NOW');
  $iso8601date = $now->format('c'); // ISO8601 formated datetime



  $mappings = array(
    '[[CONTRIBUTION:::UUID]]' => generate_uuid(),
    '[[COMMITTER_ID:::UUID]]' => generate_uuid(),
    '[[COMMITTER_NAME:::STRING]]' => 'SystemFMI-A',
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
	'[[EGN:::DV_TEXT]]' => $params['/description[at0001]/items[at0003]/items[at0010]/value'],
	'[[prescriptionBookletNo:::DV_TEXT]]' => $params['/description[at0001]/items[at0003]/items[at0011]/value'],
	'[[drugNHIFCode:::DV_TEXT]]' => $params['/description[at0001]/items[at0004]/items[at0012]/value'],
	'[[codeICD:::DV_TEXT]]' => $params['/description[at0001]/items[at0004]/items[at0013]/value'],
	'[[prescribedQuantity:::DV_COUNT]]' => $params['/description[at0001]/items[at0004]/items[at0014]/value'],
	'[[numberOfDays:::DV_COUNT]]' => $params['/description[at0001]/items[at0004]/items[at0015]/value'],
//	'[[Allow susbstitution?:::DV_BOOLEAN]]' => $params['/description[at0001]/items[at0004]/items[at0016]/value'],
	'[[Allow susbstitution?:::DV_BOOLEAN]]' => 0,
	
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
  $res = $ehrserver->commit_composition($doc, $params['ehr_uid'], 'System-A', 'PHP.TEST');

  print_r($res);
  echo $res->type;
  /*
  (
      [type] => AA
      [message] => Versions successfully committed to EHR e5661ca0-7e1b-4e10-abac-74011a446e2b
      [versions] => SimpleXMLElement Object
          (
              [version] => SimpleXMLElement Object
                  (
                      [uid] => e4a86540-48cd-4718-9bd0-0f429dfae6dc::PHP.TEST::1
                      [precedingVersionUid] => SimpleXMLElement Object
                          (
                          )
                      [lifecycleState] => 532
                      [commitAudit] => SimpleXMLElement Object
                          (
                              [timeCommitted] => 2018-10-16 06:35:03
                              [committer] => SimpleXMLElement Object
                                  (
                                      [namespace] => local
                                      [type] => PERSON
                                      [value] => SimpleXMLElement Object
                                          (
                                          )

                                      [name] => System-A
                                  )

                              [systemId] => CABOLABS_EHR
                              [changeType] => CREATION
                          )

                      [data] => SimpleXMLElement Object
                          (
                              [uid] => b7b74192-fff0-45bb-8198-15041f1e644c
                              [category] => event
                              [startTime] => 2018-10-16 06:35:02
                              [subjectId] => 6538b9f1-b9b9-449c-9e74-844e6d938715
                              [ehrUid] => e5661ca0-7e1b-4e10-abac-74011a446e2b
                              [templateId] => nhifmedication.en.v1
                              [archetypeId] => openEHR-EHR-COMPOSITION.documento_actividad_fisica.v1
                              [lastVersion] => true
                              [organizationUid] => 79a0f5fa-a3b1-4498-9caa-9f8dd1a3392e
                              [parent] => e4a86540-48cd-4718-9bd0-0f429dfae6dc::PHP.TEST::1
                          )
                  )
          )
  )
  */

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
       <h1>nhif</h1>
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
            </div>
            <div class="CLUSTER">
              <label>Patient</label>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">EGN</label>
                <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0003]/items[at0010]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">prescriptionBookletNo</label>
                <input type="text" class="DV_TEXT form-control" name="/description[at0001]/items[at0003]/items[at0011]/value" />
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
                <input type="number" class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0014]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">numberOfDays</label>
                <input type="number" class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0015]/value" />
              </div>
              <div class="ELEMENT form-group row">
                <label class="col-md-2 col-form-label">Allow susbstitution?</label>
                <input type="checkbox" name="/description[at0001]/items[at0004]/items[at0016]/value" class="DV_BOOLEAN" />
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
