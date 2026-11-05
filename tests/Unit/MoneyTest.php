<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    #[DataProvider('amounts')]
    public function test_formatea_centavos_como_pesos_mexicanos(int $cents, string $expected): void
    {
        $this->assertSame($expected, Money::format($cents));
    }

    public static function amounts(): array
    {
        return [
            'cero' => [0, '$0.00'],
            'centavos' => [86, '$0.86'],
            'un peso' => [100, '$1.00'],
            'miles' => [150000, '$1,500.00'],
            'millones' => [123456789, '$1,234,567.89'],
            'negativo' => [-600000, '−$6,000.00'],
        ];
    }

    #[DataProvider('percentages')]
    public function test_calcula_porcentajes_enteros(int $part, int $total, int $expected): void
    {
        $this->assertSame($expected, Money::percent($part, $total));
    }

    public static function percentages(): array
    {
        return [
            'mitad' => [300000, 600000, 50],
            'presupuesto de figma' => [720000, 1600000, 45],
            'sin total' => [100, 0, 0],
            'excedido' => [2000000, 1600000, 100],
            'negativo' => [-100, 1000, 0],
            'truncado' => [1, 3, 33],
        ];
    }
}
