<?php

use App\Traits\FormatsNumbers;

test('nf formats integers correctly', function () {
    $helper = new class {
        use FormatsNumbers;
    };

    expect($helper->nf(0))->toBe('0');
    expect($helper->nf(123))->toBe('123');
    expect($helper->nf(1000))->toBe('1.000');
    expect($helper->nf(1234567))->toBe('1.234.567');
    expect($helper->nf(-5000))->toBe('-5.000');
});

test('pf formats percentages correctly with default 1 decimal', function () {
    $helper = new class {
        use FormatsNumbers;
    };

    // simple cases
    expect($helper->pf(0, 100))->toBe('0.0');
    expect($helper->pf(50, 100))->toBe('50.0');
    expect($helper->pf(0.5, 1))->toBe('50.0'); // floats supported

    // rounding behavior: 1/3 = 33.333... => 33,3 with 1 decimal
    expect($helper->pf(1, 3))->toBe('33.3');

    // thousands separator
    // (1234567 / 10000) * 100 = 12345.67 => rounded to 12345.7 => "12.345,7"
    expect($helper->pf(1234567, 10000))->toBe('12,345.7');

    // typical ratio
    expect($helper->pf(1234, 10000))->toBe('12.3');

    // base zero should return 0 (formatted)
    expect($helper->pf(1, 0))->toBe('0.0');

    // values >100%
    expect($helper->pf(5, 2))->toBe('250.0');

    // negative numbers
    expect($helper->pf(-1, 2))->toBe('-50.0');
});

test('pf formats percentages correctly with other decimal places too', function () {
    $helper = new class {
        use FormatsNumbers;
    };

    // Two decimals: (1/8)*100 = 12.5 => "12,50"
    expect($helper->pf(1, 8, 2))->toBe('12.50');

    // More decimals and rounding: 1/3 with 3 decimals => 33,333
    expect($helper->pf(1, 3, 3))->toBe('33.333');

    // Zero decimals: integer percent (rounded)
    expect($helper->pf(1, 2, 0))->toBe('50');
});

test('pfc calculates percentage complement correctly', function () {
    $helper = new class {
        use FormatsNumbers;
    };

    // Normal case: 78,8% => complement 21,2
    expect($helper->pfc('78.8'))->toBe(21.2);

    // 100% => complement 0
    expect($helper->pfc('100.0'))->toBe(0.0);

    // 0% => complement 100
    expect($helper->pfc('0.0'))->toBe(100.0);
    expect($helper->pfc('0'))->toBe(100.0);

    // With thousands separator: "1.234,5" => 100 - 1234.5 = -1134.5
    expect($helper->pfc('1234.5'))->toBe(-1134.5);

    // Single decimal
    expect($helper->pfc('33.3'))->toBe(66.7);

    // Two decimals
    expect($helper->pfc('33.33', 2))->toBe(66.67);

    // Zero decimals
    expect($helper->pfc('33.33', 0))->toBe(67);

    // Negative percentage
    expect($helper->pfc('-12.5'))->toBe(112.5);

    // "Already rounded" values (50%, 75%)
    expect($helper->pfc('50.0'))->toBe(50.0);
    expect($helper->pfc('75.5'))->toBe(24.5);
});
