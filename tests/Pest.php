<?php

pest()->extend(Tests\TestCase::class)
    ->in('Feature', 'System');

pest()->extend(Tests\TestCase::class)
    ->in('../modules/product/tests');
