<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeAction;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeResource;

class PassportScopeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scopes = [
            'profile:read',
            'identity:read',
            'organization:read',
            'phone:read',
            'email:read',
            'user:manage',
        ];

        $resources = [];
        $actions = [];

        foreach ($scopes as $scope) {
            [$resourceName, $actionName] = explode(':', $scope);
            $resources[] = $resourceName;
            $actions[] = $actionName;
        }

        $resources = array_unique($resources);
        $actions = array_unique($actions);

        foreach ($resources as $resource) {
            PassportScopeResource::firstOrCreate(['name' => $resource]);
        }

        foreach ($actions as $action) {
            PassportScopeAction::firstOrCreate(['name' => $action]);
        }
    }
}
