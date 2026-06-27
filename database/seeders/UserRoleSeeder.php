<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');
        $now = now();

        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'admin@radiif.com'],
            [
                'name' => 'Super Admin',
                'password' => $password,
                'role' => 'superadmin',
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        // 2. Expert (Student)
        User::updateOrCreate(
            ['email' => 'expert@radiif.com'],
            [
                'name' => 'Expert User',
                'password' => $password,
                'role' => 'expert',
                'is_active' => true,
                'national_id' => '1234567890',
                'school_name' => 'Radiif University',
                'email_verified_at' => $now,
            ]
        );

        // 3. Freelancer
        User::updateOrCreate(
            ['email' => 'freelancer@radiif.com'],
            [
                'name' => 'Freelancer User',
                'password' => $password,
                'role' => 'freelancer',
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        // 4. Supplier (Company)
        $supplierCompany = Company::updateOrCreate(
            ['cr_number' => 'SUP-123456'],
            [
                'name' => 'Supplier Co.',
                'industry' => 'Technology',
                'size' => '10-50',
                'is_supplier' => true,
                'is_requester' => false,
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'supplier@radiif.com'],
            [
                'name' => 'Supplier User',
                'password' => $password,
                'role' => 'supplier',
                'company_id' => $supplierCompany->company_id,
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        // 5. Requester (Company)
        $requesterCompany = Company::updateOrCreate(
            ['cr_number' => 'REQ-123456'],
            [
                'name' => 'Requester Co.',
                'industry' => 'services',
                'size' => '50-100',
                'is_supplier' => false,
                'is_requester' => true,
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'requester@radiif.com'],
            [
                'name' => 'Requester User',
                'password' => $password,
                'role' => 'requester',
                'company_id' => $requesterCompany->company_id,
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        // 6. Workflow Hospital
        $hospital = Company::updateOrCreate(
            ['cr_number' => 'HOSP-445899'],
            [
                'name' => 'King Faisal Specialist Hospital (Submitter)',
                'industry' => 'healthcare',
                'size' => 'large',
                'is_supplier' => true,
                'is_verified_provider' => true,
            ]
        );
        User::updateOrCreate(
            ['email' => 'hospital@radiif.com'],
            [
                'name' => 'Dr. Khalid (Hospital Admin)',
                'password' => $password,
                'role' => 'supplier',
                'company_id' => $hospital->company_id,
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        // 7. Workflow Payer
        $payer = Company::updateOrCreate(
            ['cr_number' => 'INS-202688'],
            [
                'name' => 'Tawuniya Insurance Company (Payer)',
                'industry' => 'insurance',
                'size' => 'large',
                'is_requester' => true,
                'is_verified_provider' => true,
            ]
        );
        User::updateOrCreate(
            ['email' => 'insurance@radiif.com'],
            [
                'name' => 'Ahmed (Payer Admin)',
                'password' => $password,
                'role' => 'requester',
                'company_id' => $payer->company_id,
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        // 8. Workflow Doctor
        User::updateOrCreate(
            ['email' => 'doctor@radiif.com'],
            [
                'name' => 'Dr. Sarah (HITL Clinical Auditor)',
                'password' => $password,
                'role' => 'expert',
                'expert_domain' => 'healthcare',
                'expert_specialization' => 'doctor',
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        $this->command->info('Users for all roles have been seeded successfully!');
        $this->command->info('Emails: admin@radiif.com, expert@radiif.com, freelancer@radiif.com, supplier@radiif.com, requester@radiif.com, hospital@radiif.com, insurance@radiif.com, doctor@radiif.com');
        $this->command->info('Password: password');
    }
}
