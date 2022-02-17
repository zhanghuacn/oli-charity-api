<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => Str::uuid(),
            'lottery_code' => str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_BOTH) ,
            'charity_id' => 2,
            'activity_id' => 14,
            'type' => Ticket::TYPE_DONOR,
            'price' => 0,
            'amount' => 0,
            'anonymous' => false,
            'seat_num' => null,
            'verified_at' => now(),
        ];
    }
}
