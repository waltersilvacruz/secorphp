<?php

namespace TCEMT\Facades;

use Illuminate\Support\Facades\Facade;

class Secorphp extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'secorphp';
    }
}
