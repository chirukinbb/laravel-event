<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Events\Models\Category;
use Modules\Events\Models\Event;
use Modules\Events\Models\Tag;

/**
 * Сидер, воспроизводящий данные из frontend-файла mock-data.ts.
 *
 * ВАЖНО: все id — автоинкрементные. Здесь НИГДЕ не хардкодятся числовые id:
 * связи (category_id, user_id, tag_id, event_id) строятся через реальные
 * созданные модели и firstOrCreate/lookup по уникальным текстовым ключам.
 * Сидер идемпотентен — повторный запуск не дублирует записи.
 */
class MockDataSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password';

    /**
     * Категории из MOCK_CATEGORIES (id 1..10 в моке, здесь — в порядке вставки).
     */
    private const CATEGORIES = [
        'Спорт',
        'Музыка',
        'IT / Технологии',
        'Искусство',
        'Бизнес',
        'Еда и напитки',
        'Путешествия',
        'Кино',
        'Образование',
        'Настольные игры',
    ];

    /**
     * События из MOCK_EVENTS.
     *
     * planing_time в моке = now + N * DAY (в секундах). Здесь planing_days = N.
     * 'reserved' в моке — это количество members (таблица members).
     */
    private const EVENTS = [
        [
            'title' => 'Йога на крыше',
            'category' => 'Спорт',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600',
            'description' => 'Расслабляющая йога на закате с панорамным видом на город. Подходит для любого уровня подготовки. Занятие проводит сертифицированный инструктор с 10-летним стажем. Приносите свой коврик!',
            'coordinate_lat' => '55.7558',
            'coordinate_lng' => '37.6173',
            'country_iso' => 'RU',
            'planing_days' => 3,
            'slots' => 20,
            'address' => 'Москва, ул. Тверская, 15, крыша БЦ "Галерея"',
            'reserved' => 12,
            'author' => [
                'name' => 'Анна Соколова',
                'email' => 'anna.sokolova@example.com',
                'languages' => ['ru', 'en'],
                'bio' => 'Сертифицированный инструктор по йоге, 10 лет практики',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003284634-061b509069e9?w=600',
            ],
            'tags' => ['йога', 'здоровье', 'на природе'],
        ],
        [
            'title' => 'Джазовый вечер',
            'category' => 'Музыка',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=600',
            'description' => 'Живой джаз в уютном подвальчике. Трио саксофон-контрабас-ударные. В программе — классика Майлза Дэвиса и авторские композиции. В баре специальные коктейли вечера.',
            'coordinate_lat' => '55.7618',
            'coordinate_lng' => '37.6046',
            'country_iso' => 'RU',
            'planing_days' => 5,
            'slots' => 40,
            'address' => 'Москва, Брюсов пер., 7, джаз-клуб "Эссе"',
            'reserved' => 38,
            'author' => [
                'name' => 'Игорь Левин',
                'email' => 'igor.levin@example.com',
                'phone' => '+79035551234',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru'],
                'bio' => 'Джазовый пианист и аранжировщик',
                'avatar_url' => 'https://images.unsplash.com/photo-1494455964968-9701934108d7?w=600',
            ],
            'tags' => ['джаз', 'живая музыка'],
        ],
        [
            'title' => 'AI Meetup: Будущее LLM',
            'category' => 'IT / Технологии',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=600',
            'description' => 'Обсуждение трендов в больших языковых моделях. Доклады от инженеров Яндекса и Сбера. Рассмотрим RAG, fine-tuning, мультиагентные системы. Нетворкинг после докладов.',
            'coordinate_lat' => '55.7338',
            'coordinate_lng' => '37.5881',
            'country_iso' => 'RU',
            'planing_days' => 2,
            'slots' => 100,
            'address' => 'Москва, ул. Льва Толстого, 16, Технопарк',
            'reserved' => 87,
            'author' => [
                'name' => 'Дмитрий Волков',
                'email' => 'dmitry.volkov@example.com',
                'phone' => '+79164567890',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru', 'en'],
                'bio' => 'ML Engineer, ex-Яндекс',
                'avatar_url' => 'https://images.unsplash.com/photo-1500648769246-c26e83668095?w=600',
            ],
            'tags' => ['AI', 'митап', 'технологии'],
        ],
        [
            'title' => 'Мастер-класс по керамике',
            'category' => 'Искусство',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=600',
            'description' => 'Создайте свою первую чашку из глины! Все материалы включены. Мастер покажет базовые техники ручной лепки и работы на гончарном круге. Готовое изделие можно забрать через неделю после обжига.',
            'coordinate_lat' => '59.9343',
            'coordinate_lng' => '30.3351',
            'country_iso' => 'RU',
            'planing_days' => 7,
            'slots' => 8,
            'address' => 'Санкт-Петербург, наб. реки Фонтанки, 52',
            'reserved' => 5,
            'author' => [
                'name' => 'Мария Гончарова',
                'email' => 'maria.goncharova@example.com',
                'phone' => '+79213456789',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru'],
                'bio' => 'Художник-керамист, выпускница Мухинского училища',
                'avatar_url' => 'https://images.unsplash.com/photo-1502886042949-2993a2110846?w=600',
            ],
            'tags' => ['мастер-класс', 'творчество'],
        ],
        [
            'title' => 'Стартап-бранч',
            'category' => 'Бизнес',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=600',
            'description' => 'Неформальная встреча фаундеров и инвесторов за завтраком. Обсуждение трендов венчурного рынка. Три питча ранних стартапов. Отличная возможность найти кофаундера или ментора.',
            'coordinate_lat' => '55.7410',
            'coordinate_lng' => '37.6274',
            'country_iso' => 'RU',
            'planing_days' => 4,
            'slots' => 30,
            'address' => 'Москва, Космодамианская наб., 52, БЦ Riverside',
            'reserved' => 22,
            'author' => [
                'name' => 'Алексей Смирнов',
                'email' => 'alexey.smirnov@example.com',
                'phone' => '+79857654321',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru', 'en'],
                'bio' => 'Основатель стартап-студии, бизнес-ангел',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003284634-061b509069e9?w=600',
            ],
            'tags' => ['стартап', 'нетворкинг', 'бизнес'],
        ],
        [
            'title' => 'Винная дегустация',
            'category' => 'Еда и напитки',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=600',
            'description' => 'Дегустация 6 сортов вин Нового Света с сомелье. Лёгкие закуски включены. Узнаете, как правильно дегустировать, сочетать с едой и выбирать вино в магазине.',
            'coordinate_lat' => '55.7677',
            'coordinate_lng' => '37.5934',
            'country_iso' => 'RU',
            'planing_days' => 6,
            'slots' => 16,
            'address' => 'Москва, ул. Большая Никитская, 22',
            'reserved' => 14,
            'author' => [
                'name' => 'Екатерина Виноградова',
                'email' => 'ekaterina.vinogradova@example.com',
                'phone' => '+79038889900',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru', 'fr'],
                'bio' => 'Сомелье WSET Level 3, преподаватель винной школы',
                'avatar_url' => 'https://images.unsplash.com/photo-1502886042949-2993a2110846?w=600',
            ],
            'tags' => ['вино', 'дегустация'],
        ],
    ];

    /**
     * Профиль текущего пользователя (MOCK_PROFILE / MOCK_AUTH_RESPONSE).
     */
    private const BOGDAN = [
        'name' => 'Богдан',
        'email' => 'bogdan@example.com',
        'avatar_url' => 'https://images.unsplash.com/photo-1502886042949-2993a2110846?w=600',
        'languages' => ['ru', 'en'],
        'bio' => 'Люблю активный отдых и технологии. Всегда открыт к новым знакомствам.',
    ];

    /**
     * Гео-фильтр (MOCK_FILTER). categories — id категорий из мока [1,2,3,6,10],
     * которые соответствуют названиям ниже; берём реальные id по названию.
     */
    private const FILTER = [
        'center_lat' => 55.7558,
        'center_lng' => 37.6173,
        'radius' => 10,
        'category_titles' => ['Спорт', 'Музыка', 'IT / Технологии', 'Еда и напитки', 'Настольные игры'],
    ];

    public function run(): void
    {
        $categories = $this->seedCategories();
        $tags = $this->seedTags();
        $this->seedBogdan($categories);
        $this->seedEvents($categories, $tags);

        $this->command?->info('MockDataSeeder: данные из mock-data.ts загружены.');
    }

    /**
     * @return array<string, Category> title => Category
     */
    private function seedCategories(): array
    {
        $map = [];
        foreach (self::CATEGORIES as $title) {
            $map[$title] = Category::firstOrCreate(['title' => $title]);
        }

        return $map;
    }

    /**
     * @return array<string, Tag> name => Tag
     */
    private function seedTags(): array
    {
        $map = [];
        foreach (self::EVENTS as $eventData) {
            foreach ($eventData['tags'] as $tagName) {
                $map[$tagName] ??= Tag::firstOrCreate(['name' => $tagName]);
            }
        }

        return $map;
    }

    private function seedBogdan(array $categories): void
    {
        $user = User::firstOrCreate(
            ['email' => self::BOGDAN['email']],
            [
                'name' => self::BOGDAN['name'],
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]
        );

        // User::boot() уже создал пустой профиль и фильтр — заполняем их.
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => self::BOGDAN['name'],
                'avatar_url' => self::BOGDAN['avatar_url'],
                'languages' => self::BOGDAN['languages'],
                'bio' => self::BOGDAN['bio'],
            ]
        );

        $categoryIds = $this->categoryIds($categories, self::FILTER['category_titles']);

        $user->filter()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'center' => [
                    'lat' => self::FILTER['center_lat'],
                    'lng' => self::FILTER['center_lng'],
                ],
                'radius' => self::FILTER['radius'],
                'categories' => $categoryIds,
            ]
        );
    }

    private function seedEvents(array $categories, array $tags): void
    {
        foreach (self::EVENTS as $eventData) {
            $author = $this->seedAuthor($eventData['author']);

            $event = Event::firstOrCreate(
                ['title' => $eventData['title']],
                [
                    'user_id' => $author->id,
                    'category_id' => $categories[$eventData['category']]->id,
                    'thumbnail_url' => $eventData['thumbnail_url'],
                    'description' => $eventData['description'],
                    'coordinate_lat' => $eventData['coordinate_lat'],
                    'coordinate_lng' => $eventData['coordinate_lng'],
                    'country_iso' => $eventData['country_iso'],
                    'planing_time' => Carbon::now()->addDays($eventData['planing_days'])->toDateTimeString(),
                    'slots' => $eventData['slots']
                ]
            );

            // Связываем теги по реальным id.
            $tagIds = array_map(fn(string $name) => $tags[$name]->id, $eventData['tags']);
            $event->tags()->sync($tagIds);

            // 'reserved' в моке = members->count().
            $this->seedMembers($event, $eventData['reserved']);
        }
    }

    private function seedAuthor(array $data): User
    {
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]
        );

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $data['name'],
                'avatar_url' => $data['avatar_url'],
                'languages' => $data['languages'],
                'bio' => $data['bio'],
            ]
        );

        return $user;
    }

    private function seedMembers(Event $event, int $reserved): void
    {
        $existing = $event->members()->count();
        $needed = $reserved - $existing;

        if ($needed <= 0) {
            return;
        }

        // Участники — фиктивные пользователи (фабрика генерит уникальные email).
        // User::boot() автоматически создаёт им профиль и фильтр.
        $participants = User::factory()->count($needed)->create();

        foreach ($participants as $participant) {
            $event->members()->create(['user_id' => $participant->id]);
        }
    }

    /**
     * @param array<string, Category> $categories
     * @param string[] $titles
     * @return int[]
     */
    private function categoryIds(array $categories, array $titles): array
    {
        return array_map(fn(string $title) => $categories[$title]->id, $titles);
    }
}
