<?php

namespace App\Models;

use App\Http\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ProductCategory extends Authenticatable
{
    use HasFactory, Notifiable, CreatedUpdatedBy, SoftDeletes;
    protected $table = 'product_category';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'category_id',
        'category_name',
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
}