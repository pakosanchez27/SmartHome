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

    /** EP04-03: la captura del usuario se convierte a centavos sin float. */
    #[DataProvider('capturas')]
    public function test_convierte_capturas_de_usuario_a_centavos(string $input, ?int $expected): void
    {
        $this->assertSame($expected, Money::parse($input), "La captura «{$input}» debe interpretarse sin ambigüedad.");
    }

    public static function capturas(): array
    {
        return [
            'solo unidades' => ['1500', 150000],
            'con centavos' => ['1500.50', 150050],
            'un decimal' => ['12.5', 1250],
            'con coma de miles' => ['1,500.75', 150075],
            'con símbolo y espacio' => ['$1 234.5', 123450],
            'cero' => ['0', 0],
            'negativo' => ['-50.25', -5025],
            'tres decimales se rechazan' => ['12.345', null],
            'texto basura' => ['abc', null],
            'vacío' => ['', null],
            'doble punto' => ['1.2.3', null],
        ];
    }

    /** EP04-01: el entero de PHP viaja a la columna DECIMAL(14,2) como texto exacto. */
    #[DataProvider('decimales')]
    public function test_serializa_centavos_como_decimal(int $cents, string $expected): void
    {
        $this->assertSame($expected, Money::toDecimal($cents));
        $this->assertSame($cents, Money::parse(Money::toDecimal($cents)), 'El ciclo centavos → decimal → centavos no pierde precisión.');
    }

    public static function decimales(): array
    {
        return [
            'cero' => [0, '0.00'],
            'centavos' => [86, '0.86'],
            'miles' => [150000, '1500.00'],
            'negativo' => [-1, '-0.01'],
            'negativo grande' => [-200000, '-2000.00'],
        ];
    }

    public function test_un_entero_ya_en_centavos_se_acepta_directo(): void
    {
        $this->assertSame(150000, Money::parse(1500));
    }
}
