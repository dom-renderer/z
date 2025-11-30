<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $shifts = [
            [
                'title' => 'Morning',
                'start' => '09:00:00',
                'end' => '05:00:00'
            ],
            [
                'title' => 'Night',
                'start' => '21:00:00',
                'end' => '05:00:00'
            ]
        ];

        foreach ( $shifts as $shift ) {
            Shift::firstOrCreate(
                [ 'title' => $shift['title'] ],
                $shift
            );
        }
    }
}
