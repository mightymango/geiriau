<?php

use App\Srs\AnswerChecker;

it('accepts an exact answer whatever the case and spacing', function () {
    expect((new AnswerChecker)->check('  Cath ', 'cath'))->toBe('exact');
});

it('calls an answer close when only the accents differ', function () {
    expect((new AnswerChecker)->check('dwr', 'dŵr'))->toBe('close');
});

it('accepts any of the alternatives separated by a slash', function () {
    expect((new AnswerChecker)->check('moyn', 'eisiau / moyn'))->toBe('exact');
});

it('marks anything else wrong', function () {
    expect((new AnswerChecker)->check('ci', 'cath'))->toBe('wrong');
});
