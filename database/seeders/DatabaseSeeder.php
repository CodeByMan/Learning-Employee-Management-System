<?php

namespace Database\Seeders;

use App\Models\Learner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException(
                'Demo seeding is allowed only in local or testing environments.'
            );
        }

        if (! config('app.allow_demo_seeding')) {
            throw new LogicException(
                'Set ALLOW_DEMO_SEEDING=true to create local demo records.'
            );
        }

        $demoPassword = config('app.demo_password');

        if (! is_string($demoPassword) || strlen($demoPassword) < 12) {
            throw new LogicException(
                'DEMO_PASSWORD must contain at least 12 characters.'
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (User::assignableRoles() as $role) {
            Role::findOrCreate($role, 'web');
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@employee-lems.test'],
            [
                'name' => 'Demo Administrator',
                'password' => Hash::make($demoPassword),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles([User::ROLE_ADMIN]);

        $employee = User::updateOrCreate(
            ['email' => 'employee@employee-lems.test'],
            [
                'name' => 'Demo Employee',
                'password' => Hash::make($demoPassword),
                'email_verified_at' => now(),
            ]
        );
        $employee->syncRoles([User::ROLE_EMPLOYEE]);

        $learnerUser = User::updateOrCreate(
            ['email' => 'learner@employee-lems.test'],
            [
                'name' => 'Demo Learner',
                'password' => Hash::make($demoPassword),
                'email_verified_at' => now(),
            ]
        );
        $learnerUser->syncRoles([User::ROLE_LEARNER]);

        $learners = [
            [
                'fname' => 'Ayaan',
                'mname' => null,
                'lname' => 'Khan',
                'email' => 'ayaan.khan@employee-lems.test',
                'grade_level' => '1st Year',
                'section' => 'A',
                'qr_code' => 'LEMS-DEMO-1001',
            ],
            [
                'fname' => 'Sara',
                'mname' => 'M.',
                'lname' => 'Ahmed',
                'email' => 'sara.ahmed@employee-lems.test',
                'grade_level' => '2nd Year',
                'section' => 'B',
                'qr_code' => 'LEMS-DEMO-1002',
            ],
            [
                'fname' => 'Hamza',
                'mname' => 'S.',
                'lname' => 'Ali',
                'email' => 'hamza.ali@employee-lems.test',
                'grade_level' => '3rd Year',
                'section' => 'A',
                'qr_code' => 'LEMS-DEMO-1003',
            ],
        ];

        foreach ($learners as $learnerData) {
            $learner = Learner::firstOrNew(['email' => $learnerData['email']]);
            $learner->fill([
                'fname' => $learnerData['fname'],
                'mname' => $learnerData['mname'],
                'lname' => $learnerData['lname'],
                'email' => $learnerData['email'],
                'grade_level' => $learnerData['grade_level'],
                'section' => $learnerData['section'],
            ]);
            $learner->qr_code = $learnerData['qr_code'];
            $learner->save();
        }
    }
}
