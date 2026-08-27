<?php

it('renders the public host', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(config('app.name'))
        ->assertSee('modules/', false);
});

it('renders the admin shell', function () {
    $this->get('/admin')
        ->assertOk();
});
