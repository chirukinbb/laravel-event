<?php

namespace Modules\Bot\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Bot\Jobs\EnsureBotUsersJob;
use Modules\Bot\Jobs\SimulateEventActivityJob;
use Modules\Events\Models\Category;

class SimulateLifeCommand extends Command
{
    protected $signature = 'bot:simulate-life
        {--events=1 : Number of fake events to generate}
        {--subscribers=3 : How many random users subscribe to each event}
        {--messages=3 : How many chat messages each subscriber sends}
        {--delay=2 : Delay in seconds between activity steps}
        {--seed= : Optional seed for random generation}
        {--fresh-users=10 : Number of extra users to create if there are not enough}';

    protected $description = 'Generate fake app activity for the Bot module: random users create events, subscribe, and chat';

    public function handle(): int
    {
        $this->info('Starting bot simulation...');

        if ($this->option('seed')) {
            srand((int)$this->option('seed'));
        }

        $events = max(0, (int)$this->option('events'));
        $subscribers = max(0, (int)$this->option('subscribers'));
        $messagesPerUser = max(0, (int)$this->option('messages'));
        $delay = max(0, (int)$this->option('delay'));

        $this->ensureUsers((int)$this->option('fresh-users'));

        for ($i = 0; $i < $events; $i++) {
            $author = User::where('email', 'like', '%@example.%')->inRandomOrder()->first();

            if (!$author) {
                $this->warn('No example user found for event generation; queued users may still be creating.');
                continue;
            }

            $payload = $this->buildEventPayload($author, $i);
            $this->writeGeneratedEventToFile($author, $payload, $i + 1);

            $job = SimulateEventActivityJob::dispatch(
                $author->id,
                $payload,
                $subscribers,
                $messagesPerUser,
                $delay,
                $i + 1,
            );

            if ($delay > 0) {
                $job->delay(now()->addSeconds($delay * ($i + 1)));
            }

            $this->info(sprintf('Queued event #%s for %s', $i + 1, $author->email));
        }

        $this->info('Bot simulation jobs queued.');

        return self::SUCCESS;
    }

    protected function ensureUsers(int $count): void
    {
        $existing = User::count();
        $missing = max(0, $count - $existing);

        if ($missing <= 0) {
            return;
        }

        $job = EnsureBotUsersJob::dispatch($missing);

        if ($count > 0) {
            $job->delay(now()->addSeconds(1));
        }

        $this->info(sprintf('Queued %d fake users for simulation.', $missing));
    }

    protected function buildEventPayload(User $author, int $index): array
    {
        $prompt = sprintf(
            'Generate a realistic JSON object with fields: title, description, category_id, planing_time, coordinate_lat, coordinate_lng, country_iso, slots, is_public, chat_log.

    Event requirements:
    - The event must be small-scale, comparable to a literary evening or smaller.
    - Suitable for small Ukrainian cities, towns, villages, and local communities.
    - Examples: literary evenings, board game meetups, small workshops, book clubs, local walks, hobby meetings, community gatherings, small creative activities.
    - Avoid large-scale events, festivals, concerts, conferences, professional exhibitions, and commercial events.
    - The event should feel realistic and locally organized, not like a fictional promotional event.
    - Use a real Ukrainian settlement and accurate geographic coordinates.
    - country_iso must be "UA".
    - Keep the title in Ukrainian (priority) or Russian.
    - category_id must be 1, 2, or 3.
    - thumbnail_url must be a valid preview image URL related to the event topic.

    Chat log requirements:
    - Generate a realistic chat history between the event organizer and several participants.
    - Include 5-12 messages.
    - Each message must contain:
      - message: message text.
      - time: message time in HH:MM format.
      - role: either "organizer" or "participant".
    - Messages should look like a natural conversation about the event: questions about the location, time, participation, details, and occasional confirmations.
    - Participants should initiate most conversations.
    - The organizer should occasionally reply to participants, providing relevant information.
    - Do not make the organizer reply to every message.
    - Messages should be short and informal, like a real local event chat.
    - Keep the conversation coherent and consistent with the event description.
    - Do not use emojis.
    - Use Ukrainian for chat messages (Russian is acceptable occasionally).
    - Do not generate artificial greetings or unnecessary filler messages.'
        );

        $categories = Category::all();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => config('services.openrouter.model', 'openai/gpt-4o-mini'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Return ONLY valid JSON, no markdown.

    Required JSON structure:
    {
        "title": "Назва заходу",
        "description": "Опис заходу",
        "category_id": 1,
        "thumbnail_url": "https://images.unsplash.com/...",
        "planing_time": "2026-11-15 19:00:00",
        "coordinate_lat": 50.45,
        "coordinate_lng": 30.52,
        "country_iso": "UA",
        "slots": 20,
        "is_public": true,
        "chat_log": [
            {
                "message": "Доброго дня! Де саме відбудеться зустріч?",
                "time": "2026-11-12 10:15",
                "role": "participant"
            },
            {
                "message": "У міській бібліотеці, у читальній залі.",
                "time": "2026-11-13 10:22",
                "role": "organizer"
            }
        ]
    }

    List of available categories: ' . $categories->map(function ($category) {
                            return $category->id . ' - ' . $category->name;
                        })->implode(', '),
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        $payload = $this->parseJsonFromResponse($response->json());

        if (!is_array($payload)) {
            $payload = [
                'title' => 'Bot generated event ' . ($index + 1),
                'description' => 'Автоматически сгенерированное событие для симуляции активности в приложении.',
                'category_id' => 1,
                'planing_time' => now()->addDays(1 + $index)->setTime(18, 30)->format('Y-m-d H:i:s'),
                'coordinate_lat' => 50.45 + ($index * 0.01),
                'coordinate_lng' => 30.52 + ($index * 0.02),
                'country_iso' => 'UA',
                'slots' => 12,
                'is_public' => true,
            ];
        }

        $fallbackThumbnail = 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80';

        return [
            'title' => $payload['title'] ?? 'Bot generated event ' . ($index + 1),
            'description' => $payload['description'] ?? 'Автоматически сгенерированное событие.',
            'category_id' => (int)($payload['category_id'] ?? 1),
            'planing_time' => $payload['planing_time'] ?? now()->addDays(1 + $index)->setTime(18, 30)->format('Y-m-d H:i:s'),
            'coordinate_lat' => $payload['coordinate_lat'] ?? 50.45,
            'coordinate_lng' => $payload['coordinate_lng'] ?? 30.52,
            'country_iso' => $payload['country_iso'] ?? 'UA',
            'slots' => (int)($payload['slots'] ?? 12),
            'is_public' => (bool)($payload['is_public'] ?? true),
            'thumbnail_url' => $payload['thumbnail_url'] ?? $fallbackThumbnail,
            'chat_log' => is_array($payload['chat_log'] ?? null) ? $payload['chat_log'] : [],
        ];
    }

    protected function writeGeneratedEventToFile(User $author, array $payload, int $index): void
    {
        $directory = storage_path('app/bot-generated-events');

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            return;
        }

        $filePath = $directory . '/event-' . now()->format('Y-m-d-H-i-s') . '-' . $index . '.txt';

        $chatLog = is_array($payload['chat_log'] ?? null) ? $payload['chat_log'] : [];
        $lines = [
            'Author: ' . ($author->email ?? 'unknown'),
            'Title: ' . ($payload['title'] ?? 'Untitled event'),
            'Description: ' . ($payload['description'] ?? ''),
            'Category ID: ' . ($payload['category_id'] ?? ''),
            'Preview URL: ' . ($payload['thumbnail_url'] ?? 'not set'),
            'Planing time: ' . ($payload['planing_time'] ?? ''),
            'Coordinates: ' . ($payload['coordinate_lat'] ?? '') . ', ' . ($payload['coordinate_lng'] ?? ''),
            'Country: ' . ($payload['country_iso'] ?? ''),
            '',
            'Chat log:',
        ];

        foreach ($chatLog as $message) {
            $lines[] = sprintf(
                '[%s] %s: %s',
                $message['time'] ?? '',
                strtoupper((string)($message['role'] ?? 'participant')),
                $message['message'] ?? ''
            );
        }

        if ($chatLog === []) {
            $lines[] = 'No chat log generated.';
        }

        file_put_contents($filePath, implode(PHP_EOL, $lines) . PHP_EOL);
        $this->info(sprintf('Saved generated event text to %s', $filePath));
    }

    protected function parseJsonFromResponse(array $response): ?array
    {
        if (!isset($response['choices'][0]['message']['content'])) {
            return null;
        }

        $content = $response['choices'][0]['message']['content'];
        $clean = preg_replace('/```(?:json)?\s*/i', '', $content);
        $clean = trim(str_replace('```', '', $clean));

        $decoded = json_decode($clean, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function randomMessage(string $userName, int $index): string
    {
        $samples = [
            sprintf('%s: классно, обязательно приду!', $userName),
            sprintf('%s: кто еще едет? хочу в компанию!', $userName),
            sprintf('%s: когда стартуем и где встречаемся?', $userName),
            sprintf('%s: отличный формат, очень хочу поучаствовать!', $userName),
            sprintf('%s: есть ли свободные места?', $userName),
            sprintf('%s: скиньте линки и детали по плану!', $userName),
        ];

        return $samples[$index % count($samples)];
    }
}
