<?php

require(dirname(__FILE__). '/includes/vendor/autoload.php');

/*
 * Проверка является ли валидные данные для авторизации в гугл с json 
 *
 * @param $json_data - данные для авторизации в гугл таблицы
 *
 * @return boolean
 */
function wz_validate_google_json_auth($json_data) {
    $valid_keys = array(
        'type', 'project_id', 'private_key_id', 
        'private_key', 'client_email', 'client_id',
        'auth_uri', 'token_uri', 'auth_provider_x509_cert_url',
        'client_x509_cert_url'
    );
    
    foreach($valid_keys as $key) {
        if (!array_key_exists($key, $json_data)) {
            return false;
        }
    }
    return true;
}

/*
 * Создать экземпляр класса Google_Service_Sheets
 *
 * @param string $application_name - Название приложение(Можно назвать любое имя приложения)
 * @param array $json_auth_data - данные для доступа работы с google spreadsheet
 *
 * @return object Google_Service_Sheets
 */
function wz_google_create_service($application_name, $auth_data){
    $client = new \Google_Client();
    $client->setApplicationName($application_name);
    $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
    $client->setAccessType('offline');

    $client->setAuthConfig($auth_data);

    $service = new \Google_Service_Sheets($client);
    return $service;
}

/*
 * Получить количество заполненных строк в таблице
 *
 * @param object $service - экземпляр класса Google_Service_Sheets
 * @param string $spreadsheet_id - Номер документа (берется с адресной строки открытой гугл таблицы)
 * @param string $sheet_name - Название таблицы
 * @param string $range_columns - Список получения столбцов. пример $range_columns = 'A:H'
 *
 * @return int
 */
function wz_google_count_rows($service, $spreadsheet_id, $sheet_name, $range_columns) {
    $range = "'{$sheet_name}'!{$range_columns}";
    try{
        $result = $service->spreadsheets_values->get($spreadsheet_id, $range);
        $count_rows = $result->getValues() != null ? count($result->getValues()) : 0;
        return $count_rows;
    } catch (Exception $error) {
        return;
    }
}

/*
 * Получить данные с табицы
 *
 * @param object $service - экземпляр класса Google_Service_Sheets
 * @param string $spreadsheet_id - Номер документа (берется с адресной строки открытой гугл таблицы)
 * @param string $sheet_name - Название таблицы
 * @param string $range_columns - Список получения столбцов. пример $range_columns = 'A:H'
 *
 * @return array
 */
function wz_google_get_values($service, $spreadsheet_id, $sheet_name, $range_columns) {
    $range = "'{$sheet_name}'!{$range_columns}";
    $result = $service->spreadsheets_values->get($spreadsheet_id, $range);
    $values = $result->getValues();
    return $values;
}


/*
 * Вставить данные в строку
 *
 * @param object $service - экземпляр класса Google_Service_Sheets
 * @param string $spreadsheet_id - Номер документа (берется с адресной строки открытой гугл таблицы)
 * @param string $sheet_name - Название таблицы
 * @param string $update_range - Список вставки данных в столбцы. пример $update_columns = 'A10:H', где 10 является номер строки
 *
 * @return int возращает ответ сколько было обновлено строк
 */
function wz_google_insert_row($service, $spreadsheet_id, $sheet_name, $update_range, $values ) {
    $update_range = "'{$sheet_name}'!{$update_range}";
    
    $updateBody = new \Google_Service_Sheets_ValueRange([
        'majorDimension' => 'ROWS',
        'values' => ['values' => $values],
    ]);
    
    $result = $service->spreadsheets_values->append(
        $spreadsheet_id,
        $update_range,
        $updateBody,
        ['valueInputOption' => 'USER_ENTERED']
    );
    
    return $result->updates->updatedRows;
}