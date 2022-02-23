<?php

namespace Database\Seeders;

use App\Models\Charity;
use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run()
    {
        News::truncate();
        $news = new News([
            'title' => Str::random(10),
            'description' => Str::random(30),
            'content' => Str::random(500),
            'published_at' => Carbon::now()->tz(config('app.timezone'))->addDays(3),
        ]);
        $news->newsable()->associate(Charity::find(1));
        $news->save();
    }
}
