<?php

use Illuminate\Support\Facades\Storage;

it('serves a file from the prefixed master folder', function () {
    Storage::disk('media')->put('hello.txt', 'ok');

    $response = $this->get('/media/'.config('media.prefix').'/hello.txt');

    $response->assertOk();
    expect($response->streamedContent())->toBe('ok');
});

it('does not serve a file under another prefix', function () {
    Storage::disk('media')->put('hello.txt', 'ok');

    $this->get('/media/other/hello.txt')
        ->assertNotFound();
});
