<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Electricity Bill',
            'Water Bill',
            'Rent',
            'Internet/Fiber',
            'Telephone',
            'Stationery',
            'Printing Paper',
            'Toner/Ink',
            'Office Maintenance',
            'Staff Salaries',
            'Overtime Payments',
            'Traveling',
            'Fuel',
            'Vehicle Repair',
            'Cleaning Supplies',
            'Refreshments/Tea',
            'Marketing',
            'Facebook Ads',
            'Domain/Hosting',
            'Software Subscriptions',
            'Taxes',
            'Legal Fees',
            'Petty Cash',
            'Miscellaneous',
            'Insurance',
        ];

        foreach ($categories as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name]);
        }
    }
}
