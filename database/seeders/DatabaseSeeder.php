<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * EP01-05: seeders por módulo, siempre con datos ficticios.
     * Ejecutar solo en bases de desarrollo: nunca contra datos reales.
     */
    public function run(): void
    {
        $this->call([
            FinanceDemoSeeder::class,
        ]);
    }
}
