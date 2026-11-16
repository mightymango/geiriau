<?php

it('shows the launch intro over the page', function () {
    $this->get('/settings')
        ->assertOk()
        ->assertSee('id="launch-intro"', false)
        ->assertSee('Pum gair y dydd');
});
