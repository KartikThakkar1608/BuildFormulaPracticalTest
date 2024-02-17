<?php

namespace App\Models;

use App\Http\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Category extends Authenticatable
{
    use HasFactory, Notifiable, CreatedUpdatedBy, SoftDeletes;
    protected $table = 'category';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
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
    public static function getQueryForList($data)
    {
        $sql = Category::where(function ($query) use ($data) {
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