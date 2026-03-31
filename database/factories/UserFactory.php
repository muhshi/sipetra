<?php

namespace Database\Factories;

use App\Enums\IdentityType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'identity_type' => IdentityType::Admin,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State: Pegawai BPS.
     */
    public function pegawai(): static
    {
        return $this->state(fn (array $attributes) => [
            'identity_type' => IdentityType::Pegawai,
            'nip' => fake()->unique()->numerify('#########'),
            'nip_baru' => fake()->unique()->numerify('##################'),
            'kd_satker' => fake()->numerify('####0'),
            'jabatan' => fake()->randomElement(['Staf', 'Kepala Seksi', 'Kepala Sub Bagian', 'Kepala Bidang']),
            'unit_kerja' => 'BPS ' . fake()->city(),
            'golongan' => fake()->randomElement(['III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b']),
            'jenis_kelamin' => fake()->randomElement(['LK', 'PR']),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-25 years'),
            'pendidikan' => fake()->randomElement(['S1', 'S2', 'S3', 'D3', 'SMA']),
            'phone' => fake()->phoneNumber(),
        ]);
    }

    /**
     * State: Mitra Statistik.
     */
    public function mitra(): static
    {
        return $this->state(fn (array $attributes) => [
            'identity_type' => IdentityType::Mitra,
            'sobat_id' => fake()->unique()->numerify('#############'),
            'kd_satker' => fake()->numerify('####0'),
            'jenis_kelamin' => fake()->randomElement(['LK', 'PR']),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-20 years'),
            'pendidikan' => fake()->randomElement([
                'Tamat SD/Sederajat',
                'Tamat SMP/Sederajat',
                'Tamat SMA/Sederajat',
                'Diploma',
                'Sarjana',
            ]),
            'phone' => fake()->phoneNumber(),
        ]);
    }

    /**
     * State: inactive user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
