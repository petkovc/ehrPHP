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
print_r($compo->version->data->content->description->items[1]->items[3]->value->value);

//$array = (array)$compo;
//echo $array['ChargeableRateInfo']['@surchargetotal'];

//print_r($xml);
//echo $compo->version->data->content->description->items[1]->items[2]->value->value;
  foreach ($xml as $trend)  
  {         
   echo $trend->items."\n";    
  } 
?>
<!DOCTYPE html>
<html lang="bg">
    <head>
        <!-- meta -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="logo.png">

        <title>Рецепта</title>
        <meta name="description" content="">

        <!-- CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <!-- print CSS -->
        <style type="text/css">
            @media print {
                #printbtn {
                    display: none;
                }
            }
        </style>
        <!-- icons -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">

    </head>

    <body>
	    <?php //include('top.php'); ?>
          <form class="container" method="post" action="">
            <input type="hidden" name="ehr_uid" value="<?=$_GET['ehr_uid']?>" />
 
 <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 pb-5 mt-3">
 			<div class="card border-info rounded-0" style="border-width: 2px;">
                            <div class="card-header p-0">
                                <div class="bg-info text-white text-center py-2">
                                    <h3><i class="fas fa-file-prescription"></i> Рецепта</h3>
                                    <p class="m-0">Рецепта за изписване на платени от НЗОК лекарствени средства</p>
                                </div>
                            </div>
                            <div class="card-body p-3">

                                
                                <div class="row">
                                    <div class="form-group col-lg-4 col-md-12">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-start">
                                            <div class="input-group-prepend">
                                                <label for="1" class="input-group-text">Тип рецепта:</label>
                                            </div>
                                            <select id="1" disabled 
                                                    class="DV_ORDINAL form-control" name="/description[at0001]/items[at0002]/items[at0005]/value">
                                                <option><?php echo $compo->version->data->content->description->items[0]->items[0]->value->value; ?></option>
                                                <option value="1">Еднократна</option>
                                                <option value="2">Хронична</option>
                                                <option value="3">Бяла</option>
                                                <option value="4">Зелена</option>
                                                <option value="5">Ветерани</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-lg-center justify-content-lg-end">
                                            <div class="input-group-prepend">
                                                <label for="2" class="input-group-text">Рецепта №</label>
                                            </div>
                                            <input id="2" type="number" min="1" max="999999" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[0]->items[1]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0008]/value" />
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-4 col-md-6 col-sm-12">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-md-end">
                                            <input id="datepicker" placeholder="Дата:" disabled
											value= "<?php echo $compo->version->data->content->description->items[0]->items[2]->value->value; ?>"
                                                   class="DV_DATE form-control" name="/description[at0001]/items[at0002]/items[at0009]/value" />
                                        </div>
                                    </div>
                                </div>
                                <hr style="background-color: #17A2B8; box-shadow: 0 0 1px grey;">

                                
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-start">
                                            <div class="input-group-prepend">
                                                <label for="4" class="input-group-text">УИН на лекаря:</label>
                                            </div>
                                            <input id="4" type="number" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[0]->items[3]->items[0]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0018]/value" />
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-md-end">
                                            <div class="input-group-prepend">
                                                <label for="5" class="input-group-text">Код Специалност:</label>
                                            </div>
                                            <input id="5" type="number" min="0" max="99" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[0]->items[3]->items[1]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0021]/value" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-lg-4">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-start">
                                            <div class="input-group-prepend">
                                                <label for="6" class="input-group-text">Рег Номер ЛЗ:</label>
                                            </div>
                                            <input id="6" type="number"  disabled
											value= "<?php echo $compo->version->data->content->description->items[0]->items[3]->items[2]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0019]/value" />
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <div class="input-group mb-1
                                             justify-content-md-center">
                                            <div class="ELEMENT input-group-prepend">
                                                <label for="7" class="input-group-text">Амбул лист №</label>
                                            </div>
                                            <input id="7" type="number" min="1" max="999999" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[0]->items[3]->items[3]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0020]/value" />
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-md-end">
                                            <div class="input-group-prepend">
                                                <label for="8" class="input-group-text">Рецепта №</label>
                                            </div>
                                            <input id="8" type="number" min="1" max="999999" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[0]->items[3]->items[4]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0017]/items[at0022]/value" />
                                        </div>
                                    </div>
                                </div>
                                <hr style="background-color: #17A2B8; box-shadow: 0 0 1px grey;">

                                
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-start">
                                            <div class="input-group-prepend">
                                                <label for="9" class="input-group-text">ЕГН:</label>
                                            </div>
                                            <input id="9" type="number" min="1000000000" max="9999999999" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[0]->items[4]->items[0]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0003]/items[at0010]/value" />
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-start">
                                            <div class="input-group-prepend">
                                                <label for="10" class="input-group-text">Рецептурна книжка:</label>
                                            </div>
                                            <input id="10" type="number" min="1" max="9999" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[0]->items[4]->items[1]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0002]/items[at0003]/items[at0011]/value" />
                                        </div>
                                    </div>
                                </div>
                                <hr style="background-color: #17A2B8; box-shadow: 0 0 1px grey;">

                                
                                <div class="row">
                                    <div class="form-group col-md-4 col-lg-3">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-start">
                                            <div class="input-group-prepend">
                                                <label for="11" class="input-group-text">Код НЗОК:</label>
                                            </div>
                                            <input id="11" type="text" maxlength="5" disabled
											value= "<?php echo $compo->version->data->content->description->items[1]->items[0]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0004]/items[at0012]/value" />
                                        </div>
                                    </div>

                                    <div class="form-group col-md-8 col-lg-6">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-md-center">
                                            <div class="input-group-prepend">
                                                <label for="12" class="input-group-text">Име лекарство:</label>
                                            </div>
                                            <input id="12" type="text" maxlength="20" disabled
                                                   class="DV_TEXT form-control" name="?" />
                                        </div>
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-start">
                                            <div class="input-group-mb-end">
                                                <label for="13" class="input-group-text">МКБ Код:</label>
                                            </div>
                                            <input id="13" type="text" maxlength="5" disabled
											value= "<?php echo $compo->version->data->content->description->items[1]->items[1]->value->value; ?>"
                                                   class="DV_TEXT form-control" name="/description[at0001]/items[at0004]/items[at0013]/value" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-lg-4">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-start">
                                            <div class="input-group-prepend">
                                                <label for="14" class="input-group-text">D.:</label>
                                            </div>
                                            <input id="14" type="number" min="1" max="999" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[1]->items[2]->value->magnitude; ?>"
                                                   class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0023]/value" />
                                        </div>
                                    </div>

                                    <div class="form-group col-lg-4">
                                        <div class="input-group mb-1
                                             justify-content-md-center">
                                            <div class="ELEMENT input-group-prepend">
                                                <label for="15" class="input-group-text">Мярка:</label>
                                            </div>
                                            <select id="15" disabled
                                                    class="DV_ORDINAL form-control" name="/description[at0001]/items[at0004]/items[at0025]/value">
                                                <option disabled value="<?php echo $compo->version->data->content->description->items[1]->items[3]->value->value; ?>" ><?php echo $compo->version->data->content->description->items[1]->items[3]->value->magnitude; ?></option>
                                                <option value="0">Pack</option>
                                                <option value="1">Ampolules</option>
                                                <option value="2">Syringes</option>
                                                <option value="3">Bottles</option>
                                                <option value="4">Tablets</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-lg-4">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-md-end">
                                            <div class="input-group-prepend">
                                                <label for="16" class="input-group-text">За дни:</label>
                                            </div>
                                            <input id="16" type="number" min="1" max="999" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[1]->items[4]->value->magnitude; ?>"
                                                   class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0024]/value" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-lg-4">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-start">
                                            <div class="input-group-prepend">
                                                <label for="17" class="input-group-text">S. | Пъти:</label>
                                            </div>
                                            <input id="17" type="number" min="1" max="99" step="1" disabled
											value= "<?php echo $compo->version->data->content->description->items[1]->items[5]->value->magnitude; ?>"
                                                   class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0032]/value" />
                                        </div>
                                    </div>

                                    <div class="form-group col-lg-4">
                                        <div class="input-group mb-1
                                             justify-content-md-center">
                                            <div class="ELEMENT input-group-prepend">
                                                <label for="18" class="input-group-text">По :</label>
                                            </div>
                                            <input id="18" type="number" min="1" max="999" disabled
											value= "<?php echo $compo->version->data->content->description->items[1]->items[6]->value->magnitude; ?>"
                                                   class="DV_COUNT form-control" name="/description[at0001]/items[at0004]/items[at0031]/value" />
                                        </div>
                                    </div>

                                    <div class="form-group col-lg-4">
                                        <div class="ELEMENT input-group mb-1
                                             justify-content-md-end">
                                            <div class="input-group-prepend">
                                                <label for="19" class="input-group-text">Честота:</label>
                                            </div>
                                            <select id="19" disabled
                                                    class="DV_ORDINAL form-control" name="/description[at0001]/items[at0004]/items[at0034]/value">
                                                <option value="">-</option>
                                                <option value="0">Daily</option>
                                                <option value="1">Weekly</option>
                                                <option value="2">Monthly</option>
                                                <option value="3">Quaterly</option>
                                                <option value="4">Half yearly</option>
                                                <option value="5">Yearly</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                              
                                <hr>

                                    </div>
                                </div>
                                <hr>

                                <div class="text-center">
                                    <input type="button" value="Назад" class="btn btn-default rounded-0 py-2 float-left">
                                    <input type="button" value="Печат" id="printbtn" onclick="window.print(); return false;" class="btn btn-info rounded-0 py-2">
                                    <input type="submit" value="ЗАПИШИ" name="submit" class="btn btn-success rounded-0 py-2 float-right">
                                </div>
                            </div>

                        </div>
                    </form>
                    <!--Form with header-->


                </div>
            </div>
        </div>


        <!-- JS -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>-->
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

        <!-- datepicker -->
        <script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
        <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" property="stylesheet" />
        <script>
                                        var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
                                        $('#datepicker').datepicker({
                                            uiLibrary: 'bootstrap4',
                                            format: 'dd.mm.yyyy' //bg date format
                                        });
        </script>

    </body>
</html>
