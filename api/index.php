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

// สำหรับ POST request ให้ตรวจสอบจาก URL path
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($GET_DEV)) {
    $request_uri = $_SERVER['REQUEST_URI'];
    if (strpos($request_uri, '/api/update-order') !== false) {
        $GET_DEV = 'update-order';
    }
}

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

    case 'update-order':

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $json_data = [
                'status' => 'error',
                'message' => 'Only POST method allowed'
            ];
            break;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['updates']) || !is_array($input['updates'])) {
            http_response_code(400);
            $json_data = [
                'status' => 'error',
                'message' => 'Invalid input data'
            ];
            break;
        }

        $updates = $input['updates'];

        try {
            // สร้าง SQL queries แบบ VALUES clause (PostgreSQL specific)
            $sqlQueries = [];

            if (count($updates) > 0) {
                $valuesList = [];
                foreach ($updates as $update) {
                    if (isset($update['opd_kios_dep_menu_id']) && isset($update['order_no'])) {
                        $id = intval($update['opd_kios_dep_menu_id']);
                        $orderNo = intval($update['order_no']);
                        $valuesList[] = "({$id}, {$orderNo})";
                    }
                }

                if (!empty($valuesList)) {
                    $valuesUpdateQuery = "UPDATE opd_kios_dep_menu \n" .
                                       "SET order_no = new_values.new_order\n" .
                                       "FROM (VALUES \n" .
                                       "    " . implode(",\n    ", $valuesList) . "\n" .
                                       ") AS new_values(id, new_order)\n" .
                                       "WHERE opd_kios_dep_menu.opd_kios_dep_menu_id = new_values.id;";

                    $sqlQueries[] = $valuesUpdateQuery;
                }
            }

            // จำลองความล่าช้า
            usleep(300000); // 0.3 วินาที

            // Log การเปลี่ยนแปลงพร้อม SQL queries
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'updates' => $updates,
                'sql_queries' => $sqlQueries
            ];

            file_put_contents(
                __DIR__ . '/order_updates.log',
                json_encode($logData, JSON_PRETTY_PRINT) . "\n",
                FILE_APPEND | LOCK_EX
            );

            $json_data = [
                'status' => 'success',
                'updated' => count($updates),
                'message' => 'SQL queries generated successfully',
                'sql_queries' => $sqlQueries
            ];

        } catch (Exception $e) {
            http_response_code(500);
            $json_data = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
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