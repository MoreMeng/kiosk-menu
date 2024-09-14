<?php
session_start();
error_reporting( 0 );
mb_internal_encoding( 'UTF-8' );

// if ( !$_SESSION['token'] ) {
//     exit( 'Error: token expired' );
// }

// set content return type
// header('Content-Type: application/json');

// Setting up some server access controls to allow people to get information
header( "Access-Control-Allow-Origin: <origin>" );
header( 'Access-Control-Allow-Methods:  POST, GET' );
header( 'Content-Type: application/json; charset=utf-8' );

require realpath( '../../dv-config.php' );
require DEV_PATH . '/classes/db.class.v2.php';
require DEV_PATH . '/functions/global.php';

require DEV_PATH . '/functions/edc-function.php';

// require '../lib/variables.php';

$GET_DEV = ( empty( $_GET['dev'] ) ) ? null : $_GET['dev'];
$GET_ID  = ( isset( $_GET['id'] ) ) ? $_GET['id'] : null;

// $json_data['data'] = [];

switch ( $GET_DEV ) {

    case 'kiosk':

        $source = curl_powerdrills( API_SERVER_HOSXP . '/kiosk-menu/' . $GET_ID );

        if ( $source !== false && !empty( $source ) && isJson( $source ) ) {
            $json_data = json_decode( $source );
        } else {
            http_response_code( 200 );
            $json_data['RESULT'] = false;
        }
        break;

    default:
        http_response_code( 400 );
        $json_data = [
            'error'       => true,
            'title'       => 'BAD REQUEST',
            'description' => 'ไม่ได้ระบุหมายเหตุ'
        ];
        break;
}

echo json_encode( $json_data, JSON_UNESCAPED_UNICODE );

// 200 OK
// 201 CREATED  -  [POST,PUT]
// 204 NO CONTENT  -  [DELETE,PUT]
// 400 BAD REQUEST
// 401 UNAUTHORIZED
// 403 FORBIDDEN
// 404 NO FOUND
// 405 METHOD NOT ALLOWED
// 409 CONFLICT
// 500 INTERNAL SERVER ERROR