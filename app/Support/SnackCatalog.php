<?php

namespace App\Support;

class SnackCatalog
{
    // Classroom sample prices and starting stock, not manufacturer price claims.
    public const ITEMS = [
        ['name' => 'Nova Multigrain Chips', 'price' => '18.00', 'stock' => 30, 'sketch' => 'nova'],
        ['name' => 'Piattos Cheese', 'price' => '22.00', 'stock' => 30, 'sketch' => 'piattos'],
        ['name' => 'V-Cut Potato Chips', 'price' => '25.00', 'stock' => 24, 'sketch' => 'vcut'],
        ['name' => 'Chippy Barbecue', 'price' => '12.00', 'stock' => 40, 'sketch' => 'chippy'],
        ['name' => 'Oishi Prawn Crackers', 'price' => '10.00', 'stock' => 40, 'sketch' => 'oishi'],
        ['name' => 'Boy Bawang Cornick', 'price' => '15.00', 'stock' => 36, 'sketch' => 'boy-bawang'],
    ];

    public static function sketch(string $name): string
    {
        foreach (self::ITEMS as $item) {
            if (str_contains(strtolower($name), strtolower(explode(' ', $item['name'])[0]))) {
                return $item['sketch'];
            }
        }

        return 'snack';
    }
}
