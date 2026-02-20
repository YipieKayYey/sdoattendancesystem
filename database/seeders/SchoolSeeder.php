<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            'Bagong Silang Elementary School',
            'Balanga City Science National High School',
            'Balanga Elementary School',
            'Bani Integrated School',
            'Bataan National High School -JHS',
            'Bataan National High School -SHS',
            'Bo. Central Elementary School',
            'Cabog-Cabog Integrated  School',
            'Cataning Integrated School',
            'City of Balanga National High School',
            'Cupang Integrated School',
            'E. Bernabe Elementary School',
            'G.L. David Mem. Integrated  School',
            'M. delos Reyes Mem. Elem. School',
            'M.P. Cuaderno Sr. Mem. Elem. School',
            'Our Lady of Lourdes Elem. School',
            'Pto. Rivas Elementary School',
            'Schools Division Office Balanga City',
            'T.Camacho Sr. Elementary School',
            'Tanato Integrated School',
            'Tenejero Integrated School',
            'Tortugas Integrated  School',
            'Tuyo Integrated School',
        ];

        foreach ($schools as $name) {
            School::firstOrCreate(['name' => $name]);
        }
    }
}
