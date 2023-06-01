<?php

namespace Codestage\Authorization\Tests;

use Codestage\Authorization\Contracts\IPermissionEnum;
use Codestage\Authorization\Models\{Role, RolePermission, UserRole};
use Codestage\Authorization\Providers\AuthorizationServiceProvider;
use Codestage\Authorization\Tests\Fakes\Enums\FakePermission;
use Codestage\Authorization\Tests\Fakes\Models\User;
use Illuminate\Config\Repository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use Illuminate\Support\Facades\{Auth, Schema};
use Illuminate\Support\Str;
use Mockery;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set the base path
        $this->app->setBasePath(__DIR__ . '/../');

        // Set up fake
        $this->setUpFaker();

        // Set the used permissions enum
        $configRepository = $this->app->make(Repository::class);
        $configRepository->set('authorization.permissions_enum', FakePermission::class);

        // Create a fake table
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->timestamps();
        });
        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->timestamps();
        });
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app): array
    {
        return [AuthorizationServiceProvider::class];
    }

    /**
     * Authenticate this session using the given permissions and roles.
     *
     * @param iterable<IPermissionEnum> $permissions
     * @param iterable<string> $roles
     * @return Authenticatable
     */
    protected function authenticateUser(iterable $permissions = [], iterable $roles = []): Authenticatable
    {
        // Create the user
        $user = User::query()->create();

        // Authenticate as this user
        Auth::login($user);

        // Accumulate all created roles in an array in order to assign them to the user at the end
        $createdRoles = [];

        // Create requested roles
        foreach ($roles as $role) {
            $createdRoles[] = Role::query()->create([
                'key' => $role,
                'name' => $role,
            ]);
        }

        // Assign requested permissions
        foreach ($permissions as $permission) {
            $name = $this->faker->unique()->jobTitle();
            $role = Role::query()->create([
                'key' => Str::slug($name),
                'name' => $name,
            ]);

            RolePermission::query()->create([
                'role_id' => $role->getKey(),
                'permission' => $permission
            ]);

            $createdRoles[] = $role;
        }

        // Assign created roles to the user
        foreach ($createdRoles as $role) {
            /** @var UserRole $association */
            $association = UserRole::query()->make([
                'role_id' => $role->getKey(),
            ]);
            $association->user()->associate($user);
            $association->save();
        }

        // Return the user instance
        return $user;
    }
}
