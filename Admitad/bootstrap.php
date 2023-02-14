<?php
require "vendor/autoload.php";
$api = new Admitad\Api\Api();
$response = $api->authorizeByPassword('3370869ddb9dc6eac78c3ae823f417', '5b2003c78f865a6264c1e6b153697e', 'statistics ', 'BorisovAndriy2', 'Borisov1412');
$result = $response->getResult(); // or $response->getArrayResult();
//echo($result['access_token']);
//$api->get($path, $params);
//$api->post($path, $params);
$file = 'action_id.txt';



// Open the file to get existing content
$current = file_get_contents($file);
if($current!='')
{
	$action_id_start = (int)$current;
}
else
{
	//з якої почати
	$action_id_start = 919564704;
}
// Append a new person to the file
//$current .= "John Smith\n";
// Write the contents back to the file
//file_put_contents($file, $current);





$api = new Admitad\Api\Api($result['access_token']);


//for example
$data = $api->get('/statistics/actions/', array(
    //'date_start'=>'01.01.2023',
	'action_id_start' =>$action_id_start,
    //'offset' => 0,
	'limit' => 1

	
))->getResult();

$count_action_all = $data->_meta['count'];
$new_id = $data->results[0]['id'];
$new_id = $new_id+1;
file_put_contents($file, $new_id);

$new_data = array();
for($i=0;$i<$count_action_all;$i+=500)
{
$data = $api->get('/statistics/actions/', array(
    'date_start'=>'01.01.2023',
	//'action_id_start' =>$action_id_start,
    'offset' => $i,
	'limit' =>500	
	))->getResult();
	
	
foreach($data->results as $subres)
      {
	if($subres['subid4']!='')
	{
	$new_data[$subres['subid4']]['google_click_id'] =$subres['subid4'];
	$new_data[$subres['subid4']]['con_time'] =$subres['action_date'];
	$new_data[$subres['subid4']]['con_value'] +=$subres['payment'];
	$new_data[$subres['subid4']]['currency'] =$subres['currency'];
	$new_data[$subres['subid4']]['click_country_code'] =$subres['click_country_code'];
	$new_data[$subres['subid4']]['advcampaign_name'] =$subres['advcampaign_name'];
	//$new_data[$subres['subid4']]['count_conv'] +=1;
	
	}
	
   }

}



			require_once '../system/vendor/autoload.php';

						// Путь к файлу ключа сервисного аккаунта
                       $googleAccountKeyFilePath ='../system/evident-alloy-377514-91abcd0f9793.json';
                       putenv( 'GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath );

						// Документация https://developers.google.com/sheets/api/
                       $client = new Google_Client();
                       $client->useApplicationDefaultCredentials();

						// Области, к которым будет доступ
						// https://developers.google.com/identity/protocols/googlescopes
                       $client->addScope( 'https://www.googleapis.com/auth/spreadsheets' );

                       $service = new Google_Service_Sheets( $client );

					// ID таблицы
                       $spreadsheetId = '1pnIWftebkdk_9uXNosQcWzRO9Nr3GgidHnuMim9G8rk';
                       $range = 'conversion-import-template';
                       $response = $service->spreadsheets_values->get($spreadsheetId, $range);


					   
					   
					   
					   

                           $rowcount = count($response->values) + 1;
						   //Очистка
							$range = 'conversion-import-template!A8:E'.$rowcount; // the range to clear, the 23th and 24th lines
							$clear = new Google_Service_Sheets_ClearValuesRequest();
							$service->spreadsheets_values->clear($spreadsheetId, $range, $clear);



                           $rowadd = 'conversion-import-template!A8';

                       $values = array();

                       $tmpcount=$rowcount;

                       foreach ($new_data as $product) {


                        switch($product['currency'])
                        {
                            case 'PLN':
                            	$summa = $product['con_value']*8.48;
                            	break;
                            case 'USD':
                            	$summa = $product['con_value']*36.67;
                            	break;
                            case 'INR':
                            	$summa = $product['con_value']*0.45;
                            	break; 	
                            case 'UAH':
                            	$summa = $product['con_value'];
                            	break; 	
                            case 'EUR':
                            	$summa = $product['con_value']*39.93;
                            	break; 
                            case 'CZK':
                            	$summa = $product['con_value']*1.68;
                            	break; 
                            case 'SGD':
                            	$summa = $product['con_value']*27.94;
                            	break; 
                            case 'AED':
                            	$summa = $product['con_value']*9.98;
                            	break;
                            case 'KZT':
                            	$summa = $product['con_value']*0.08;
                            	break;
                            case 'GBP':
                            	$summa = $product['con_value']*45.19;
                            	break;                            	
                            case 'BRL':
                            	$summa = $product['con_value']*0.45;
                            	break; 
                            case 'BYN':
                            	$summa = $product['con_value']*14.56;
                            	break;
                            case 'DKK':
                            	$summa = $product['con_value']*5.37;
                            	break;
                            case 'TRY':
                            	$summa = $product['con_value']*1.95;
                            	break;                            	
                            case 'SAR':
                            	$summa = $product['con_value']*9.77;
                            	break;                             	
                             case 'BGN':
                            	$summa = $product['con_value']*20.41;
                            	break;                           	
                             case 'IDR':
                            	$summa = $product['con_value']*0.0025;
                            	break;                              
                             case 'SEK':
                            	$summa = $product['con_value']*3.52;
                            	break;                              	
                            	
                            
                            /*	
                            case 'PLN':
                            	$summa = $product['con_value']*9;
                            	break;
                            case 'USD':
                            	$summa = $product['con_value']*35;
                            	break;
                            case 'INR':
                            	$summa = $product['con_value']*0.45;
                            	break; 	
                            case 'UAH':
                            	$summa = $product['con_value'];
                            	break; 	
                            case 'EUR':
                            	$summa = $product['con_value']*40;
                            	break; 
                            	
                            */
                            	
                           default:
                               	$summa = $product['con_value'];
                        }



                           $values[] = array(
							   $product['google_click_id'],
                               'sale',
							   $product['con_time'],
                               $summa,
							   'UAH'

                           );
                           $tmpcount++;
                       }







                       $body    = new Google_Service_Sheets_ValueRange( [ 'values' => $values ] );

// valueInputOption - определяет способ интерпретации входных данных
// https://developers.google.com/sheets/api/reference/rest/v4/ValueInputOption
// RAW | USER_ENTERED
                       $options = array( 'valueInputOption' => 'USER_ENTERED' );

                       $service->spreadsheets_values->update( $spreadsheetId, $rowadd, $body, $options );

echo '<pre>';
var_dump($service->spreadsheets_values->update( $spreadsheetId, $rowadd, $body, $options ));
die();



echo('GOTOVO - o4isty Google Tabli4ku');
