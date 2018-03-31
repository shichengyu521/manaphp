<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 2015/12/28
 * Time: 0:04
 */
namespace Tests\Models;

use ManaPHP\Mvc\Model;

/**
 * Class Rental
 * @package Tests\Models
 * @property \Tests\Models\Inventory   $inventory
 * @property \Tests\Models\Inventory[] $inventories
 * @property \Tests\Models\Customer    $customer
 * @property \Tests\Models\Customer[]  $customers
 */
class Rental extends Model
{
    public $rental_id;
    public $rental_date;
    public $inventory_id;
    public $customer_id;
    public $return_date;
    public $staff_id;
    public $last_update;

    public function getForeignKeys()
    {
        return ['inventory_id', 'customer_id'];
    }
}