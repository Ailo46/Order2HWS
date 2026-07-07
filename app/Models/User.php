<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasRoles;

    protected $fillable = [

        'name',

        'agent_code',

        'phone',

        'mobile',

        'is_active',

        'max_discount_percent',

        'email',

        'password',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [

            'email_verified_at' => 'datetime',

            'is_active' => 'boolean',

            'max_discount_percent' => 'decimal:2',

            'password' => 'hashed',

        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'sales_agent_id');
    }
}