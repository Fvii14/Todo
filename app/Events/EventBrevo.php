<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventBrevo
{
    use Dispatchable, SerializesModels;
    public $eventoOriginal;
   
    /**
     * Create a new event instance.
     * 
     * @param $eventoOriginal
     * @return void
     */
    public function __construct($eventoOriginal)
    {
        $this->eventoOriginal = $eventoOriginal;
    }

}
