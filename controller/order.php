<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');
/**
 * @author  ryan <cumt.xiaochi@gmail.com>
 */

list(
    $name,
    $product_no,
    $order_no,
    $type,
    $time_start,
    $time_end,
    $state) = _get(
        'name',
        'product_no',
        'order_no',
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
    'order_no',
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

        if ($by_ajax) {
            switch ($action) {
                case 'change_remark':
                    $remark = _post('remark');

                    $order = new Order($target);
                    if ($remark) {
                        $customer->changeOrderRemark($order, $remark);
                    }
                    echo $order->customer_remark;
                    exit;
                
                default:
                    throw new Exception("unkown action: $action");
                    break;
            }
        }

        $customer = $customer->id; // for next
        break;

    case 'Admin':
    case 'SuperAdmin':
        $factories = Factory::names();

        // get some divs
        if ($by_ajax && is_numeric($target) && preg_match('get_.+_div', $action)) {
            include FrameFile::controller('order.get_div');
            exit;
        }

        // 改变工厂。我知道，应该变成那个样子。。不过，这个属于历史遗留问题啊
        // we beleive in runing code 这个就先这样吧
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
                    throw new Exception("ajax action not allowed: $action");
                    break;
            }
            exit;
        }

        // 对订单的操作，通过表单post过来的
        if ($by_ajax && $by_post && is_numeric($target)) {
            include FrameFile::controller('order.control');
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

// we don't need these two lines
if (empty($orders)) 
    $orders = array();

$matter = $view;
$view = 'board?master';

$page['scripts'][] = 'jquery.validate';
