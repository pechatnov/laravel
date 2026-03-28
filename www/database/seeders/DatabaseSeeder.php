<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    private const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function run(): void
    {
        Storage::disk('public')->deleteDirectory('seed');
        $png = base64_decode(self::TINY_PNG, true);
        if ($png === false) {
            throw new \RuntimeException('Invalid seed image payload.');
        }

        $users = collect();
        for ($i = 1; $i <= 5; $i++) {
            $path = "seed/users/{$i}.png";
            Storage::disk('public')->put($path, $png);

            $users->push(User::query()->create([
                'first_name' => 'Имя_'.$i.'_seed',
                'last_name' => 'Фам_'.$i.'_seed',
                'phone' => sprintf('+7999%07d', 1000000 + $i),
                'avatar_path' => $path,
            ]));
        }

        $companies = collect();
        for ($i = 1; $i <= 5; $i++) {
            $path = "seed/logos/{$i}.png";
            Storage::disk('public')->put($path, $png);

            $companies->push(Company::query()->create([
                'title' => 'Компания_'.$i.'_title',
                'description' => str_repeat(
                    'Описание компании номер '.$i.'. ',
                    12
                ),
                'logo_path' => $path,
            ]));
        }

        $targets = $users->concat($companies)->values();
        $reviewBodies = [
            'Подробный и честный отзыв о взаимодействии. ',
            'Хороший опыт, рекомендую обратить внимание на детали сервиса и сроки. ',
            'Смешанные впечатления: сильные стороны очевидны, но есть и зоны роста. ',
        ];

        for ($r = 1; $r <= 20; $r++) {
            $target = $targets[($r - 1) % $targets->count()];
            $author = $users[($r + 1) % $users->count()];

            $body = str_repeat($reviewBodies[$r % count($reviewBodies)], 6);
            if (mb_strlen($body) < 150) {
                $body .= str_repeat('Дополнение текста. ', 20);
            }
            if (mb_strlen($body) > 550) {
                $body = mb_substr($body, 0, 550);
            }

            Review::query()->create([
                'user_id' => $author->id,
                'reviewable_type' => $target->getMorphClass(),
                'reviewable_id' => $target->getKey(),
                'content' => $body,
                'rating' => ($r % 10) + 1,
            ]);
        }
    }
}
