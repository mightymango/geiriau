<?php

it('shows the splash artwork as the launch intro', function () {
    $this->get('/settings')
        ->assertOk()
        ->assertSee('id="launch-intro"', false)
        ->assertSee('/splash.png', false)
        ->assertSee('/splash-dark.png', false);
});
