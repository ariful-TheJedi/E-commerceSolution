<?php

arch('shared kernel does not use Illuminate')
    ->expect('Shared')
    ->not->toUse('Illuminate');

arch('domain code never lives in app Models')
    ->expect('App\Models')
    ->not->toBeUsed();
