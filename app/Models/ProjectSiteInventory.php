<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSiteInventory extends Model
{
    protected $table = 'project_site_inventories';

    protected $fillable = [
        'company_id',
        'project_id',
        'product_id',
        'warehouse_id',
        'quantity_on_site',
        'quantity_used',
        'last_delivery_date',
        'notes',
    ];

    protected $casts = [
        'quantity_on_site' => 'decimal:4',
        'quantity_used' => 'decimal:4',
        'last_delivery_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
