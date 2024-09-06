<?php

declare(strict_types=1);

$tax_rate = $argv[1] or die('1st argument missing');
$cost_price = $argv[2] or die('2nd argument missing');

const MIN_PROFIT = 5;
const MAX_PROFIT = 200;

function calculateSellPrice($cost_price, $tax_rate, $sell_price = null)
{
    $price = $sell_price ?? $cost_price;
    $sell_price = $cost_price + ($price * $tax_rate / 100);
    $profit = min(max($sell_price * 0.1, MIN_PROFIT), MAX_PROFIT);
    return round($sell_price + $profit, 2);
}

for ($i = 0; $i < 10; $i++) {
    $sell_price = calculateSellPrice($cost_price, $tax_rate, $sell_price ?? null);
}

$tax = round($sell_price * $tax_rate / 100, 2);
$profit = round($sell_price - $cost_price - $tax, 2);

echo "Tax: {$tax}" . PHP_EOL;
echo "Profit: {$profit}" . PHP_EOL;
echo "Sell price: {$sell_price}" . PHP_EOL;
echo "Validation: " . ($cost_price + $tax + $profit) . PHP_EOL;
