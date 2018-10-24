<?php

namespace App\Observers;

use App\Models\Split;

class SplitObserver
{
    /**
     * Handle the app models split "created" event.
     *
     * @param  Split  $appModelsSplit
     * @return void
     */
    public function created(Split $appModelsSplit)
    {
        //
    }

    /**
     * Handle the app models split "updated" event.
     *
     * @param  Split  $appModelsSplit
     * @return void
     */
    public function updated(Split $appModelsSplit)
    {
        //
    }

    /**
     * Handle the app models split "deleted" event.
     *
     * @param  Split  $appModelsSplit
     * @return void
     */
    public function deleted(Split $appModelsSplit)
    {
        //
    }

    /**
     * Handle the app models split "restored" event.
     *
     * @param  Split  $appModelsSplit
     * @return void
     */
    public function restored(Split $appModelsSplit)
    {
        //
    }

    /**
     * Handle the app models split "force deleted" event.
     *
     * @param  Split  $appModelsSplit
     * @return void
     */
    public function forceDeleted(Split $appModelsSplit)
    {
        //
    }
}
