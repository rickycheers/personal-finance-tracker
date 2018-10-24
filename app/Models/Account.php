<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Account
 * @package App\Models
 *
 * @property string $name
 * @property string $currency
 * @property string $type
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Account extends Model
{
    protected $fillable = ['name', 'currency', 'type'];

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Split::class);
    }
}