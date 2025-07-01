<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


$route['api/token'] = 'Token/get';
$route['api/session'] = 'Sessionid/get';
// Item
$route['api/item'] = 'Item/get';
// Timbang
$route['api/timbang'] = 'Timbang/get';
// Equipment Status
$route['api/equipment-status'] = 'Equipmentstatus/get';
// Batch
$route['api/batch'] = 'Batch/get';
$route['api/batch/detail/(:any)'] = 'Batch/detail/$1';
$route['api/batch/fastest/(:num)'] = 'Batch/fastest/$1';
// Product
$route['api/product'] = 'Product/api';
$route['api/product/(:num)'] = 'Product/api_by_id/$1';
// Formula
$route['api/formula'] = 'Formula/api';
$route['api/formula/(:num)'] = 'Formula/api_by_id/$1';
// Webhook notif
$route['api/webhook/batch-notif'] = 'Notif/get';
// Log
$route['api/log-msg'] = 'Logmsg/get';
// Category
$route['api/category'] = 'Category/get';
// User
$route['api/user'] = 'User/api';
$route['api/user/(:num)'] = 'User/api_by_id/$1';
// Finishgood
$route['api/finishgood'] = 'Finishgood/get';
// Spk
$route['api/spk'] = 'Spk/api';
$route['api/spk/(:num)'] = 'Spk/api_by_id/$1';
$route['api/spk/today'] = 'Spk/getToday';
$route['api/spk/available'] = 'Spk/getAvailable';
// Transaction
$route['api/transaction'] = 'Transaction/api';
$route['api/transaction/(:num)'] = 'Transaction/api_by_id/$1';
$route['api/transaction-detail/delete/(:num)'] = 'Transaction/deleteDetail/$1';
$route['api/transaction/start'] = 'Transaction/start';
$route['api/transaction/stop'] = 'Transaction/stop';
$route['api/transaction/done'] = 'Transaction/done';

// Web
// Batch
$route['batch'] = 'Batch/index';
