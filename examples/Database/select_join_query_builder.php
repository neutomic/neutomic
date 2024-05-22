<?php

declare(strict_types=1);

/*
 * This file is part of the Neutomic package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neu\Examples\Database;

use Neu\Component\Database\DatabaseInterface;
use Psl\IO;

/** @var DatabaseInterface $database */
$database = require __DIR__ . '/bootstrap.php';

$database->query('DROP TABLE IF EXISTS customers');
$database->query('DROP TABLE IF EXISTS vendors');
$database->query('DROP TABLE IF EXISTS orders');

$database->query('CREATE TABLE IF NOT EXISTS customers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(256) NOT NULL
)');

$database->query('CREATE TABLE IF NOT EXISTS vendors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(256) NOT NULL
)');

$database->query('CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL,
    vendor_id INT NOT NULL,
    item VARCHAR(256) NOT NULL,
    status INT NOT NULL
)');

$database
    ->createQueryBuilder()
    ->insert('customers')
    ->values(
        ['name' => ':name1'],
        ['name' => ':name2'],
        ['name' => ':name3'],
        ['name' => ':name4'],
        ['name' => ':name5'],
        ['name' => ':name6'],
        ['name' => ':name7'],
    )
    ->execute(['name1' => 'Jasmine', 'name2' => 'Anna', 'name3' => 'James', 'name4' => 'David', 'name5' => 'Mark', 'name6' => 'Salma', 'name7' => 'Ali']);

$database
    ->createQueryBuilder()
    ->insert('vendors')
    ->values(
        ['name' => ':name1'],
        ['name' => ':name2'],
        ['name' => ':name3'],
        ['name' => ':name4'],
    )
    ->execute(['name1' => 'Foo', 'name2' => 'Bar', 'name3' => 'Baz', 'name4' => 'Qux']);

$database
    ->createQueryBuilder()
    ->insert('orders')
    ->values(
        ['customer_id' => ':customer1', 'vendor_id' => ':vendor1', 'item' => ':item1', 'status' => ':status1'],
        ['customer_id' => ':customer2', 'vendor_id' => ':vendor2', 'item' => ':item2', 'status' => ':status2'],
        ['customer_id' => ':customer3', 'vendor_id' => ':vendor3', 'item' => ':item3', 'status' => ':status3'],
        ['customer_id' => ':customer4', 'vendor_id' => ':vendor4', 'item' => ':item4', 'status' => ':status4'],
        ['customer_id' => ':customer5', 'vendor_id' => ':vendor5', 'item' => ':item5', 'status' => ':status5'],
        ['customer_id' => ':customer6', 'vendor_id' => ':vendor6', 'item' => ':item6', 'status' => ':status6'],
    )
    ->execute([
        'customer1' => 1, 'vendor1' => 1, 'item1' => 'T-Shirt - grey - XXL',   'status1' => 1,
        'customer2' => 2, 'vendor2' => 2, 'item2' => 'T-Shirt - grey - M',     'status2' => 1,
        'customer3' => 3, 'vendor3' => 1, 'item3' => 'T-Shirt - blue - S',     'status3' => 0,
        'customer4' => 4, 'vendor4' => 3, 'item4' => 'T-Shirt - green - L',    'status4' => 0,
        'customer5' => 4, 'vendor5' => 3, 'item5' => 'T-Shirt - red - L',      'status5' => 0,
        'customer6' => 5, 'vendor6' => 2, 'item6' => 'T-Shirt - red - XL',     'status6' => 0,
    ])
;

$query = $database
    ->createQueryBuilder()
    ->select('o.id as id', 'o.item as item', 'c.name as customer')
    ->from('orders', 'o')
    ->innerJoin('o', 'customers', 'c', condition: 'o.customer_id = c.id')
    ->where(
        $database->createExpressionBuilder()->equal('o.status', ':status')
    );

$pending_orders =  $query->execute(['status' => 0])->getRows();
foreach ($pending_orders as $order) {
    IO\write_line('Customer "%s" is waiting for their order of "%s" ( order #%d )', $order['customer'], $order['item'], $order['id']);
}

$completed_orders =  $query->execute(['status' => 1])->getRows();
foreach ($completed_orders as $order) {
    IO\write_line('Customer "%s" has received their order of "%s" ( order #%d )', $order['customer'], $order['item'], $order['id']);
}

$query = $database
    ->createQueryBuilder()
    ->select('c.name as name', 'o.id as order')
    ->from('customers', 'c')
    ->leftJoin('c', 'orders', 'o', condition: 'c.id = o.customer_id')
    ->where(
        $database->createExpressionBuilder()->equal('o.status', ':status')
    );

$customers_with_pending_orders =  $query->execute(['status' => 0])->getRows();
foreach ($customers_with_pending_orders as $customer) {
    IO\write_line('customer "%s" has a pending order #%d', $customer['name'], $customer['order']);
}

$customers_with_completed_orders =  $query->execute(['status' => 1])->getRows();
foreach ($customers_with_completed_orders as $customer) {
    IO\write_line('customer "%s" has a completed order #%d', $customer['name'], $customer['order']);
}

$query = $database
    ->createQueryBuilder()
    ->select('o.id as order', 'v.name as vendor')
    ->from('orders', 'o')
    ->rightJoin('o', 'vendors', 'v', condition: 'o.vendor_id = v.id')
    ->where(
        $database->createExpressionBuilder()->equal('o.status', ':status')
    );

$pending_orders =  $query->execute(['status' => 0])->getRows();
foreach ($pending_orders as $order) {
    IO\write_line('waiting for vendor "%s" to ship order #%d', $order['vendor'], $order['order']);
}

$completed_orders =  $query->execute(['status' => 1])->getRows();
foreach ($completed_orders as $order) {
    IO\write_line('vendor "%s" has shipped order #%d', $order['vendor'], $order['order']);
}
