<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\SubDepartment;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Role::factory()->create([
            'name' => 'Medewerker',
            'slug' => 'medewerker',
        ]);

        Role::factory()->create([
            'name' => 'Sub Manager',
            'slug' => 'sub-manager',
        ]);

        Role::factory()->create([
            'name' => 'Manager',
            'slug' => 'manager',
        ]);

        Role::factory()->create([
            'name' => 'Staff',
            'slug' => 'staff',
        ]);

        Role::factory()->create([
            'name' => 'CEO',
            'slug' => 'ceo',
        ]);



        Department::factory()->create([
            'name' => 'GeoICT',
            'slug' => 'geoict',
        ]);

        // GeoICT SubDepartments
        SubDepartment::factory()->create([
            'name' => 'Development',
            'slug' => 'development',
            'department_slug' => 'geoict',
        ]);

        SubDepartment::factory()->create([
            'name' => 'Scanning',
            'slug' => 'scanning',
            'department_slug' => 'geoict',
        ]);

        SubDepartment::factory()->create([
            'name' => 'Processing',
            'slug' => 'processing',
            'department_slug' => 'geoict',
        ]);


        Department::factory()->create([
            'name' => 'Geodesy',
            'slug' => 'geodesy',
        ]);

        // Geodesy SubDepartments
        SubDepartment::factory()->create([
            'name' => 'Preparation',
            'slug' => 'preparation',
            'department_slug' => 'geodesy',
        ]);

        SubDepartment::factory()->create([
            'name' => 'Measuring',
            'slug' => 'measuring',
            'department_slug' => 'geodesy',
        ]);

        SubDepartment::factory()->create([
            'name' => 'Document',
            'slug' => 'document',
            'department_slug' => 'geodesy',
        ]);


        Department::factory()->create([
            'name' => 'Relation Management',
            'slug' => 'relation-management',
        ]);

        Department::factory()->create([
            'name' => 'Finance',
            'slug' => 'finance',
        ]);

        Department::factory()->create([
            'name' => 'HRM',
            'slug' => 'hrm',
        ]);

        Department::factory()->create([
            'name' => 'ICT',
            'slug' => 'ict',
        ]);



        User::factory(5)->create();
    }
}
