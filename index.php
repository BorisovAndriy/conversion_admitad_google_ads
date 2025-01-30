<?php

    // Вимикає всі типи повідомлень про помилки в PHP
    //error_reporting(0);

    // Забороняє відображення помилок у браузері
    ini_set('display_errors', 0);


    echo '<pre>Export started at: '. date("Y-m-d H:i:s").'<br>';

    /**
     * Підлючення основного файла конфігурації
    */
    $configs = [];
    $client_id = false;

    const CONFIGS_PATH = $_SERVER['DOCUMENT_ROOT'] . '/configs/';
    require_once (CONFIGS_PATH.'configs.php');

    /**
     * Автозавантаження розширень
     */
    require_once "Admitad/vendor/autoload.php";
    require_once 'system/vendor/autoload.php';
    $googleAccountKeyFilePath ='system/evident-alloy-377514-91abcd0f9793_ac_ba2.json';


    foreach ($configs as $config) {

        if (!file_exists(CONFIGS_PATH.$config)){
            throw new Exception('Error: Config file '. $config .' not find');
        }

        require_once (CONFIGS_PATH.$config);

        /**
         * Підлючення по Апі до Адмітаду
         */
        $api = new Admitad\Api\Api();
        $response = $api->authorizeByPassword($client_id, $client_secret, $scope, $user_name, $user_password);
        $api = new Admitad\Api\Api($response->getResult()['access_token']);

        /**
         * Завантаження данних по Апі з Адмітаду та підготовка данних
         */
        $data = $api->get('/statistics/actions/', array(
            //'date_start'=>'01.01.2023',
            'action_id_start' =>$action_start_id,
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
                'date_start'=> $action_started_at,
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
        $response = $service->spreadsheets_values->get($spreadsheet_id, $range);

        /**
         * Очистка попередніх результатів у Гугл таблиці
         */
        $rowcount = count($response->values) + 1;
        $range = 'conversion-import-template!A8:E'.$rowcount; // the range to clear, the 23th and 24th lines
        $clear = new Google_Service_Sheets_ClearValuesRequest();
        $service->spreadsheets_values->clear($spreadsheet_id, $range, $clear);

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
        $service->spreadsheets_values->update( $spreadsheet_id, $row_add, $body, $options );

        echo '<a href="https://docs.google.com/spreadsheets/d/'.$spreadsheet_id.'">Open Spreadsheet for '.$config.'</a><br>';
    }


    echo 'Export finished at: '. date("Y-m-d H:i:s").'<br>';
    die('');
