<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DemoSeederGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_environment_rejects_demo_seeding(): void
    {
        $exception = $this->captureSeederException('production', true, 'StrongLocalPassword123');

        $this->assertSame(
            'Demo seeding is allowed only in local or testing environments.',
            $exception->getMessage()
        );
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('roles', 0);
    }

    public function test_local_environment_without_explicit_flag_rejects_demo_seeding(): void
    {
        $exception = $this->captureSeederException('local', false, 'StrongLocalPassword123');

        $this->assertSame(
            'Set ALLOW_DEMO_SEEDING=true to create local demo records.',
            $exception->getMessage()
        );
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('roles', 0);
    }

    public function test_weak_or_empty_demo_password_is_rejected(): void
    {
        foreach ([null, '', 'TooShort1'] as $password) {
            try {
                $this->withApplicationEnvironment('local', function () use ($password): void {
                    config([
                        'app.allow_demo_seeding' => true,
                        'app.demo_password' => $password,
                    ]);

                    app(DatabaseSeeder::class)->run();
                });

                $this->fail('Weak or empty demo passwords must be rejected.');
            } catch (LogicException $exception) {
                $this->assertSame(
                    'DEMO_PASSWORD must contain at least 12 characters.',
                    $exception->getMessage()
                );
            }
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('roles', 0);
    }

    public function test_local_environment_with_flag_and_strong_password_creates_expected_demo_data(): void
    {
        $this->withApplicationEnvironment('local', function (): void {
            config([
                'app.allow_demo_seeding' => true,
                'app.demo_password' => 'StrongLocalPassword123',
            ]);

            app(DatabaseSeeder::class)->run();
        });

        $this->assertDatabaseCount('roles', 3);
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('learners', 3);

        $admin = User::where('email', 'admin@employee-lems.test')->firstOrFail();

        $this->assertTrue($admin->hasRole(User::ROLE_ADMIN));
        $this->assertTrue(Hash::check('StrongLocalPassword123', $admin->password));
        $this->assertNotNull($admin->email_verified_at);
        $employee = User::where('email', 'employee@employee-lems.test')->firstOrFail();
        $learner = User::where('email', 'learner@employee-lems.test')->firstOrFail();

        $this->assertTrue($employee->hasRole(User::ROLE_EMPLOYEE));
        $this->assertTrue($learner->hasRole(User::ROLE_LEARNER));

        $this->assertSame(
            [User::ROLE_ADMIN, User::ROLE_EMPLOYEE, User::ROLE_LEARNER],
            Role::query()->orderBy('name')->pluck('name')->sort()->values()->all()
        );
    }

    private function captureSeederException(
        string $environment,
        bool $enabled,
        ?string $password
    ): LogicException {
        try {
            $this->withApplicationEnvironment($environment, function () use ($enabled, $password): void {
                config([
                    'app.allow_demo_seeding' => $enabled,
                    'app.demo_password' => $password,
                ]);

                app(DatabaseSeeder::class)->run();
            });
        } catch (LogicException $exception) {
            return $exception;
        }

        $this->fail('The demo seeder should have rejected this configuration.');
    }

    private function withApplicationEnvironment(string $environment, callable $callback): void
    {
        $originalEnvironment = app()->environment();
        app()->detectEnvironment(fn (): string => $environment);

        try {
            $callback();
        } finally {
            app()->detectEnvironment(fn (): string => $originalEnvironment);
        }
    }
}
