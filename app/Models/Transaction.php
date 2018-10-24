<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaction
 * @package App\Models

 * @property \DateTime $date
 * @property string $description
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Transaction extends Model
{
    protected $fillable = ['date', 'description'];

    public function splits()
    {
        return $this->hasMany(Split::class);
    }
}