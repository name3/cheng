<?php

/**
 * @author  ryan <cumt.xiaochi@gmail.com>
 */
class Product extends Model 
{
    public static $table = 'product';

    protected function info()
    {
        $this->info = Pdb::fetchRow('*', self::$table, $this->selfCond());
        return $this->info;
    }

    public static function count()
    {
        return Pdb::count(self::$table);
    }

    public static function listProduct($conds = array()) 
    {
        extract(self::defaultConds($conds));
        $tail = "LIMIT $limit OFFSET $offset";
        return safe_array_map(function ($id) {
            return new Product($id);
        }, Pdb::fetchAll('id', self::$table, null, null, $tail));
    }

    public static function types()
    {
        return Pdb::fetchAll('name', 'product_type');
    }

    public function estimatePrice()
    {
        // todo 
        $info = $this->info();
        $material = $info['material'];
        return 
            $info['weight'] * (1 + Setting::get('wear_tear')) * Price::current($material) * ($material === 'PT950' ? Setting::get('weight_ratio') : 1)
                + Setting::get('labor_expense')
                + $info['small_stone'] * (Setting::get('st_expense') + Setting::get('st_price'));
    }
}
