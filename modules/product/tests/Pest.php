<?php

// Feature tests boot Laravel with uses(Tests\TestCase::class) in the test file.
// Do not pest()->extend()->in('Feature') here: that plus uses() is a Pest error
// ("Test case already in use"). Unit tests must not boot Laravel.
