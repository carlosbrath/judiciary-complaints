<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

/** The lower-judiciary courts of the ten districts of AJ&K. */
class CourtSeeder extends Seeder
{
    private const DISTRICTS = [
        ['Muzaffarabad', 'مظفرآباد'],
        ['Neelum', 'نیلم'],
        ['Jhelum Valley', 'جہلم ویلی'],
        ['Bagh', 'باغ'],
        ['Haveli', 'حویلی'],
        ['Poonch (Rawalakot)', 'پونچھ (راولاکوٹ)'],
        ['Sudhnoti', 'سدھنوتی'],
        ['Kotli', 'کوٹلی'],
        ['Mirpur', 'میرپور'],
        ['Bhimber', 'بھمبر'],
    ];

    private const COURTS = [
        ['District & Sessions Court', 'ضلعی و سیشن عدالت'],
        ['Civil Court', 'سول عدالت'],
        ['Family Court', 'فیملی عدالت'],
    ];

    public function run(): void
    {
        $order = 0;

        foreach (self::DISTRICTS as [$districtEn, $districtUr]) {
            foreach (self::COURTS as [$courtEn, $courtUr]) {
                Court::query()->updateOrCreate(
                    ['name_en' => $courtEn, 'district_en' => $districtEn],
                    ['name_ur' => $courtUr, 'district_ur' => $districtUr, 'is_active' => true, 'sort_order' => ++$order],
                );
            }
        }
    }
}
