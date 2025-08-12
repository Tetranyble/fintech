<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'firstname' => 'Ugbanawaji',
            'lastname' => 'Ekenekiso',
            'middlename' => 'Leonard',
            'email' => 'developer@ugbanawaji.com',
            'password' => 'password',
        ]);

        Payment::factory()->count(3)->create(['customer_id' => $user->id])
            ->each(fn($payment) => $payment->transactions()->saveMany(
                Transaction::factory()->count(3)->make([
                    'payment_id' => $payment->id,
                    'user_id' => $user->id
                ])
            ));

        User::factory(10)->create()
            ->each(fn ($user) =>
                $user->payments()
                    ->saveMany(Payment::factory()->count(3)->make(['customer_id' => $user->id]))
                ->each(fn($payment) => $payment->transactions()->saveMany(
                    Transaction::factory()->count(3)->make([
                        'payment_id' => $payment->id,
                        'user_id' => $user->id
                    ])
                ))
            );
    }
}
