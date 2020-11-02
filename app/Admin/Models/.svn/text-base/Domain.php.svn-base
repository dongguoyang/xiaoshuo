<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Builder;

class Domain extends Base
{
    //
    protected $fillable = ['host', 'app', 'status', 'type_id', 'ip_num', 'view_num'];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                $builder->where('app', $customer->id);
            }
        });
    }

    public function domainType() {
        return $this->belongsTo(DomainType::class, 'type_id', 'id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'app', 'id');
    }
}