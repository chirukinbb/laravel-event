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
        // Ваша текущая база
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

        // Дополнения для развлечений и социалок
        'Развлечения и вечеринки',
        'Дети и семья',
        'Красота и здоровье',
        'Языки и общение',
        'Фотография и видео',
        'Танцы',
        'Видеоигры и киберспорт',
        'Благотворительность и эко',
        'Авто и мото',
        'Шопинг и маркеты',
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
        [
            'title' => 'Утренняя пробежка по набережной',
            'category' => 'Спорт',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600',
            'description' => 'Лёгкая беговая утреняя группа по набережной, с разминкой, контролем темпа и короткой медитацией после финиша. Подойдёт как новичкам, так и тем, кто хочет поддерживать форму.',
            'coordinate_lat' => '55.7522',
            'coordinate_lng' => '37.6206',
            'country_iso' => 'RU',
            'planing_days' => 2,
            'slots' => 18,
            'address' => 'Москва, набережная Тараса Шевченко, 3',
            'reserved' => 9,
            'author' => [
                'name' => 'Никита Кузнецов',
                'email' => 'nikita.kuznetsov@example.com',
                'phone' => '+79034567890',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru', 'en'],
                'bio' => 'Тренер по бегу, сертифицированный коуч по выносливости',
                'avatar_url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=600',
            ],
            'tags' => ['бег', 'спорт', 'утро'],
        ],
        [
            'title' => 'Клубный сет в лофте',
            'category' => 'Музыка',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=600',
            'description' => 'Вечер электронного и фанк-музыки в индустриальном лофте с диджей-сетом, баром и танцполом. Настроение, звук и атмосфера — без лишних формальностей.',
            'coordinate_lat' => '55.7694',
            'coordinate_lng' => '37.6340',
            'country_iso' => 'RU',
            'planing_days' => 14,
            'slots' => 80,
            'address' => 'Москва, ул. Нижняя Сыромятническая, 10, Loft 83',
            'reserved' => 63,
            'author' => [
                'name' => 'Светлана Морозова',
                'email' => 'svetlana.morozova@example.com',
                'phone' => '+79056667788',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru', 'en'],
                'bio' => 'Диджей, куратор музыкальных событий в центре города',
                'avatar_url' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=600',
            ],
            'tags' => ['музыка', 'диджей', 'вечер'],
        ],
        [
            'title' => 'Горный weekend: треккинг и пикник',
            'category' => 'Путешествия',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=600',
            'description' => 'Выезд на горный маршрут с прогулкой, пикником и фотографиями на закате. Подойдёт для любителей активности и красивых пейзажей вдали от мегаполиса.',
            'coordinate_lat' => '56.0184',
            'coordinate_lng' => '37.7714',
            'country_iso' => 'RU',
            'planing_days' => 23,
            'slots' => 12,
            'address' => 'Московская область, Сокольники, база «Горный бережок»',
            'reserved' => 7,
            'author' => [
                'name' => 'Олег Крылов',
                'email' => 'oleg.krylov@example.com',
                'phone' => '+79169998877',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru'],
                'bio' => 'Гид по активным маршрутам и природным тропам',
                'avatar_url' => 'https://images.unsplash.com/photo-1504593811423-6dd665756598?w=600',
            ],
            'tags' => ['путешествия', 'природа', 'треккинг'],
        ],
        [
            'title' => 'Фотосессия в архитектурном центре',
            'category' => 'Фотография и видео',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=600',
            'description' => 'Групповая съёмка на фоне исторической архитектуры, с направлением по позам, свету и композиции. Подходит для друзей, подруг, креативных команд и начинающих фотографов.',
            'coordinate_lat' => '55.7599',
            'coordinate_lng' => '37.6156',
            'country_iso' => 'RU',
            'planing_days' => 31,
            'slots' => 10,
            'address' => 'Москва, Тверская улица, 11, исторический квартал',
            'reserved' => 6,
            'author' => [
                'name' => 'Ирина Плотникова',
                'email' => 'irina.plotnikova@example.com',
                'phone' => '+79051234567',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru', 'en'],
                'bio' => 'Фотограф и визуальный редактор, работает с портретами и событиями',
                'avatar_url' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600',
            ],
            'tags' => ['фото', 'съёмка', 'город'],
        ],
        [
            'title' => 'Воркшоп по английскому для общения',
            'category' => 'Языки и общение',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600',
            'description' => 'Практический разговорный курс на английском: живые диалоги, игровые задания и работа в мини-группах. Отлично подходит для тех, кто хочет говорить свободно и без стресса.',
            'coordinate_lat' => '55.7512',
            'coordinate_lng' => '37.6225',
            'country_iso' => 'RU',
            'planing_days' => 48,
            'slots' => 14,
            'address' => 'Москва, ул. Мясницкая, 25, языковой клуб',
            'reserved' => 11,
            'author' => [
                'name' => 'Элина Боровская',
                'email' => 'elina.borovskaya@example.com',
                'phone' => '+79032223344',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru', 'en'],
                'bio' => 'Преподаватель английского и международных коммуникаций',
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600',
            ],
            'tags' => ['english', 'общение', 'языки'],
        ],
        [
            'title' => 'Турнир по настольным играм',
            'category' => 'Настольные игры',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=600',
            'description' => 'Набор самых популярных настольных игр, мини-турнир, дружеская атмосфера и возможность попробовать новые механики. Идеально для компаний и новичков.',
            'coordinate_lat' => '55.7848',
            'coordinate_lng' => '37.5993',
            'country_iso' => 'RU',
            'planing_days' => 62,
            'slots' => 24,
            'address' => 'Москва, Кожевническая улица, 10, Game Room',
            'reserved' => 18,
            'author' => [
                'name' => 'Артём Дубинин',
                'email' => 'artem.dubin@example.com',
                'phone' => '+79115554433',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru'],
                'bio' => 'Арбитр настольных турниров и организатор игровых клубов',
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=600',
            ],
            'tags' => ['настолки', 'игры', 'команда'],
        ],
        [
            'title' => 'Вечер вкусной еды и новых друзей',
            'category' => 'Еда и напитки',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=600',
            'description' => 'Неформальный ужин в формате «смотри, пробуй, общайся»: небольшие блюда, разнообразные напитки и разговоры в уютной компании. Без строгих формальностей.',
            'coordinate_lat' => '55.7610',
            'coordinate_lng' => '37.6102',
            'country_iso' => 'RU',
            'planing_days' => 75,
            'slots' => 30,
            'address' => 'Москва, ул. Пятницкая, 12, домашняя кухня',
            'reserved' => 19,
            'author' => [
                'name' => 'Марина Белова',
                'email' => 'marina.belova@example.com',
                'phone' => '+79262224455',
                'country_phone_code' => '+7',
                'country_phone_iso' => 'RU',
                'languages' => ['ru', 'en'],
                'bio' => 'Кулинарный блогер и организатор событий в формате dinner club',
                'avatar_url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=600',
            ],
            'tags' => ['еда', 'друзья', 'ужин'],
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
        $this->seedProfile($user, [
            'name' => self::BOGDAN['name'],
            'avatar_url' => self::BOGDAN['avatar_url'],
            'languages' => self::BOGDAN['languages'],
            'bio' => self::BOGDAN['bio'],
        ]);

        $this->seedFilter($user, $categories, self::FILTER);
    }

    private function seedEvents(array $categories, array $tags): void
    {
        foreach (self::EVENTS as $eventData) {
            $author = $this->seedAuthor($eventData['author'], $categories);

            $event = Event::firstOrCreate(
                ['title' => $eventData['title'] . '[TEST DATA]'],
                [
                    'user_id' => $author->id,
                    'category_id' => $categories[$eventData['category']]->id,
                    'thumbnail_url' => $eventData['thumbnail_url'],
                    'description' => $eventData['description'],
                    'coordinate_lat' => $eventData['coordinate_lat'],
                    'coordinate_lng' => $eventData['coordinate_lng'],
                    'country_iso' => $eventData['country_iso'],
                    'planing_time' => Carbon::now()->addDays($eventData['planing_days'])->toDateTimeString(),
                    'slots' => $eventData['slots'],
                    'approved' => true,
                ]
            );

            // Связываем теги по реальным id.
            $tagIds = array_map(fn(string $name) => $tags[$name]->id, $eventData['tags']);
            $event->tags()->sync($tagIds);

            // 'reserved' в моке = members->count().
            $this->seedMembers($event, $eventData['reserved'], $categories);

            // Чат и несколько сообщений для каждого события.
            $this->seedChat($event, $author);
        }
    }

    /**
     * Создаёт чат события (если его ещё нет) и наполняет его несколькими сообщениями.
     *
     * Идемпотентно: при повторном запуске не дублирует ни чат, ни сообщения.
     * Для новых событий чат уже создаётся автоматически в Event::boot(),
     * поэтому здесь используем firstOrCreate.
     */
    private function seedChat(Event $event, User $author): void
    {
        $chat = $event->chat()->firstOrCreate([]);

        // Если сообщения уже есть — пропускаем (повторный запуск).
        if ($chat->messages()->exists()) {
            return;
        }

        $members = $event->members()->with('user')->get()->pluck('user');
        $eventDate = Carbon::parse($event->planing_time);

        $messages = [
            sprintf('Всем привет! Создал чат для события «%s». Задавайте вопросы 👋', $event->title),
            'Подскажите, во сколько лучше подойти?',
            'Уже записался, очень жду встречи!',
            'Кто-нибудь был на похожем мероприятии? Поделитесь впечатлениями 🙂',
            'Отличная идея, обязательно приду!',
            'Если будут вопросы по площадке — пишите, сориентирую.',
        ];

        $total = count($messages);

        foreach ($messages as $i => $text) {
            // Первое сообщение — от автора события, остальные — от участников.
            $user = $i === 0
                ? $author
                : ($members->get(($i - 1) % max($members->count(), 1)) ?? $author);

            $message = $chat->messages()->make([
                'content' => $text,
                'user_id' => $user->id,
            ]);

            // Разносим сообщения по времени относительно даты события,
            // чтобы чат выглядел естественно и привязывался к реальному мероприятию.
            $offsetDays = max(1, $total - $i);
            $message->created_at = $eventDate->copy()->subDays($offsetDays)->subHours(($i % 4) * 3 + 2);
            $message->save();
        }
    }

    private function seedAuthor(array $data, array $categories): User
    {
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]
        );

        $this->seedProfile($user, [
            'name' => $data['name'],
            'avatar_url' => $data['avatar_url'],
            'languages' => $data['languages'],
            'bio' => $data['bio'],
        ]);

        $this->seedFilter($user, $categories, self::FILTER);

        return $user;
    }

    private function seedMembers(Event $event, int $reserved, array $categories): void
    {
        $existing = $event->members()->count();
        $needed = $reserved - $existing;

        if ($needed <= 0) {
            return;
        }

        // Участники — фиктивные пользователи (фабрика генерит уникальные email).
        // User::boot() уже создал пустые профиль и фильтр — заполняем их.
        $participants = User::factory()->count($needed)->create();

        foreach ($participants as $participant) {
            $this->seedProfile($participant, $this->memberProfileData($participant));
            $this->seedFilter($participant, $categories, self::FILTER);
            $event->members()->create(['user_id' => $participant->id]);
        }
    }

    /**
     * Заполняет профиль пользователя (upsert по user_id).
     */
    private function seedProfile(User $user, array $data): void
    {
        $user->profile()->updateOrCreate(['user_id' => $user->id], $data);
    }

    /**
     * Заполняет гео-фильтр пользователя (upsert по user_id).
     *
     * @param array<string, Category> $categories
     * @param array{center_lat: float, center_lng: float, radius: int, category_titles: string[]} $data
     */
    private function seedFilter(User $user, array $categories, array $data): void
    {
        $user->filter()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'center' => [
                    $data['center_lat'],
                    $data['center_lng'],
                ],
                'radius' => $data['radius'],
                'categories' => $this->categoryIds($categories, $data['category_titles']),
            ]
        );
    }

    /**
     * Профиль фиктивного участника: имя берём у пользователя, остальное генерим.
     */
    private function memberProfileData(User $user): array
    {
        return [
            'name' => $user->name,
            'avatar_url' => 'https://i.pravatar.cc/300?u=' . urlencode($user->email),
            'languages' => ['ru'],
            'bio' => fake('ru_RU')->sentence(),
        ];
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
