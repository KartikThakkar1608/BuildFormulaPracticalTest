<?php

namespace App\Models;

use App\Http\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Product extends Authenticatable
{
    use HasFactory, Notifiable, CreatedUpdatedBy, SoftDeletes;
    protected $table = 'product';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'description',
        'price',
        'image',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
    protected $auditEvents = [
        'created',
        'updated',
        'deleted'
    ];
    public function productCategory()
    {
        return $this->hasMany(ProductCategory::class, 'product_id')
        ->select('id','product_id','category_id', 'category_name');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')
        ->select('id', 'name');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id')
        ->select('id', 'name');
    }
    public function productImages()
{
    return $this->hasMany(ProductImages::class, 'product_id')
        ->select('id', 'product_id', \DB::raw("CONCAT('" . asset('images/') . "/', image) AS image_path"));
}
    public static function getQueryForList($data)
    {
        $sql = Product::where(function ($query) use ($data) {
            if (@$data['search']) {
                $query->where('name', 'like', '%' . $data['search'] . '%');
            }
            if (@$data['name']) {
                $query->where('name', $data['name']);
            }
            if (@$data['status']) {
                $query->where('status', $data['status']);
            }
        });
        return $sql;
    }
}