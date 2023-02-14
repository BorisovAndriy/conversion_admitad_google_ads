<?php
    echo 'Export started at: '. date("Y-m-d H:i:s").'<br>';

    /**
     * Підлючення основного файла конфігурації
    */
    require_once ('config.php');

    /**
     * Автозавантаження розширень
    */
    require_once "Admitad/vendor/autoload.php";
    require_once 'system/vendor/autoload.php';
    $googleAccountKeyFilePath ='system/evident-alloy-377514-91abcd0f9793_ac_ba2.json';

    /**
     * Підлючення по Апі до Адмітаду
    */
    $api = new Admitad\Api\Api();
    $response = $api->authorizeByPassword(CLIENT_ID, CLIENT_SECRET, SCOPE, USER_NAME, USER_PASSWORD);
    $api = new Admitad\Api\Api($response->getResult()['access_token']);

    /**
     * Завантаження данних по Апі з Адмітаду та підготовка данних
    */
    $data = $api->get('/statistics/actions/', array(
        //'date_start'=>'01.01.2023',
        'action_id_start' =>ACTION_START_ID,
        //'offset' => 0,
        'limit' => 1
    ))->getResult();

    $total_action_count = $data->_meta['count'];
    $start_action_id = $data->results[0]['id'];

    /**
     * Завантаження данних по Апі з Адмітаду та підготовка типізованого масиву данних
     */
    $new_data = array();
    for( $i = 0; $i < $total_action_count; $i+=500)
    {
        $data = $api->get('/statistics/actions/', array(
            'date_start'=> ACTION_STARTED_AT,
            //'action_id_start' =>ACTION_START_ID,
            'offset' => $i,
            'limit' =>500
        ))->getResult();

        foreach($data->results as $subres){
            if(!empty($subres['subid4'])){
                $new_data[$subres['subid4']]['google_click_id'] =$subres['subid4'];
                $new_data[$subres['subid4']]['con_time'] =$subres['action_date'];
                $new_data[$subres['subid4']]['con_value'] +=$subres['payment'];
                $new_data[$subres['subid4']]['currency'] =$subres['currency'];
                $new_data[$subres['subid4']]['click_country_code'] =$subres['click_country_code'];
                $new_data[$subres['subid4']]['advcampaign_name'] =$subres['advcampaign_name'];
            }
        }
    }

    /**
     * Підготовка, очистка та запис данних у Гугл таблицю
     * Документація 
     * https://developers.google.com/sheets/api/
     * https://developers.google.com/identity/protocols/googlescopes
     */


    putenv( 'GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath );

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();

    $client->addScope( 'https://www.googleapis.com/auth/spreadsheets' );
    $service = new Google_Service_Sheets( $client );

    $range = 'conversion-import-template';
    $response = $service->spreadsheets_values->get(SPREADSHEET_ID, $range);

    /**
     * Очистка попередніх результатів у Гугл таблиці
    */
    $rowcount = count($response->values) + 1;
    $range = 'conversion-import-template!A8:E'.$rowcount; // the range to clear, the 23th and 24th lines
    $clear = new Google_Service_Sheets_ClearValuesRequest();
    $service->spreadsheets_values->clear(SPREADSHEET_ID, $range, $clear);

    /**
     * Підготовка результуючого масиву
    */
    $values = array();
    foreach ($new_data as $product) {
        $values[] = array(
            $product['google_click_id'],
            'sale',
            $product['con_time'],
            '1',
            'UAH'
        );
    }

    /**
     * Запис результатів у Гугл таблицю
     *
     * https://developers.google.com/sheets/api/reference/rest/v4/ValueInputOption
     */
    $row_add = 'conversion-import-template!A8';
    $body    = new Google_Service_Sheets_ValueRange( [ 'values' => $values ] );
    $options = array( 'valueInputOption' => 'USER_ENTERED' );
    $service->spreadsheets_values->update( SPREADSHEET_ID, $row_add, $body, $options );

    echo 'Export finished at: '. date("Y-m-d H:i:s").'<br>';
    echo '<a href="https://docs.google.com/spreadsheets/d/'.SPREADSHEET_ID.'">Open Spreadsheet</a><br>';


    die();

