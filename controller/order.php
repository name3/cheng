<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');
/**
 * @author  ryan <cumt.xiaochi@gmail.com>
 */

list(
    $name,
    $product_no,
    $no,
    $type,
    $time_start,
    $time_end,
    $state) = _get(
        'name',
        'product_no',
        'no',
        'type',
        'time_start',
        'time_end',
        'state');

if (!isset($_GET['state']))
    $state = $target;
if ($state === 'all')
    $state = '';

$conds = compact(
    'name',
    'product_no',
    'no',
    'time_start',
    'time_end',
    'type',
    'state');

$types = Product::types();
$state_map = $config['order_states'];
$next_action_map = $config['order_next_action'];
$next_button_map = $config['next_button_map'];

switch ($user_type) {
    case 'Customer':
        $customer = $customer->id;
        break;

    case 'Admin':
    case 'SuperAdmin':

        $factories = Factory::names();

        if ($by_ajax) {
            switch ($action) {
                case 'change_factory':
                    $factory_id = _get('factory_id');
                    $order_id = _get('order_id');
                    $order = new Order($order_id);
                    $order->edit('factory', $factory_id);
                    echo $factories[$factory_id];
                    exit;
                
                default:
                    $id = _get('id');
                    $action = $admin->__call($action . 'Order', new Order($id));
                    echo json_encode(array(
                        'action' => $next_button_map[$action],
                        'caption' => $next_button_map[$action]));
                    break;
            }
            exit;
        }

        list(
            $customer,
            $username,
            $factory) = _get(
                'customer',
                'username',
                'factory');
        $conds = array_merge(
            $conds,
            compact(
                'username',
                'factory'));

        $page['append_divs']['factory-select'] = 'factory.select';
        break;
    
    default:
        throw new Exception("unkown user type: $user_type");
        break;
}
$conds['customer'] = $customer;



$per_page = 50;
$total = Order::count($conds);
$paging = new Paginate($per_page, $total);
$paging->setCurPage(_get('p') ?: 1);
$orders = Order::listOrder(array_merge(
    array(
    'limit' => $per_page,
    'offset' => $paging->offset()),
    $conds));

// we don't need this two lines
if (empty($orders)) 
    $orders = array();

$matter = $view;
$view = 'board?master';
