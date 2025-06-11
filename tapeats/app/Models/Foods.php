<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Foods extends Model
{
    use HasFactory;
    use Search;
    protected $fillable = ['name', 'description', 'image', 'price', 'price_afterdiscount', 'percent', 'is_promo', 'categories_id', 'status', 'stock'];

    protected $searchable = ['name', 'description'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'categories_id');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItems::class, 'foods_id');
    }

    public function getPromo()
    {
        return self::with('category')
            ->withSum('transactionItems as total_sold', 'quantity')
            ->where('is_promo', 1)
            ->get();
    }

    public function getFavoriteFood()
    {
        return TransactionItems::select(
            'foods.*',
            DB::raw('SUM(transaction_items.quantity) as total_sold')
        )
            ->join('foods', 'transaction_items.foods_id', '=', 'foods.id')
            ->groupBy('foods.id')
            ->orderByDesc('total_sold')
            ->take(3)
            ->get();
    }

    public function getAllFoods()
    {
        return DB::table('foods')
            ->leftJoin('transaction_items', 'foods.id', '=', 'transaction_items.foods_id')
            ->select('foods.*', DB::raw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold'))
            ->groupBy('foods.id')
            ->get();
    }


    public function getFoodDetails($id)
    {
        return DB::table('foods')
            ->leftJoin('transaction_items', 'foods.id', '=', 'transaction_items.foods_id')
            ->select('foods.*', DB::raw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold'))
            ->where('foods.id', $id)
            ->groupBy('foods.id')
            ->get();
    }

    protected static function booted()
    {
        static::saving(function ($food) {
            if ($food->stock <= 0) {
                $food->status = 'out_of_stock';
            } elseif ($food->status === 'out_of_stock' && $food->stock > 0) {
                $food->status = 'available';
            }
        });
    }
}
