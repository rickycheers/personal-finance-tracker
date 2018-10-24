<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use Money\Money;

/**
 * Class Split
 * @package App\Models
 *
 * @property Money $amount
 * @property \DateTime $reconciliation_date
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Split extends Model
{
    protected $fillable = ['amount', 'reconciliation_date'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function getAmountAttribute($value)
    {
        return new Money($value, new Currency('USD'));
    }

    public function setAmountAttribute(Money $amount)
    {
        $this->attributes['amount'] = $amount->getAmount();
    }
}
