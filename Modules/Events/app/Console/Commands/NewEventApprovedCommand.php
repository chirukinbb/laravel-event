<?php

namespace Modules\Events\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Log;
use Modules\Events\Jobs\NewEventNotificationJob;
use Modules\Events\Models\Event;
use Modules\Events\Models\Tag;

class NewEventApprovedCommand extends Command
{
    protected $signature = 'approved:new-event';

    protected $description = 'Command description';

    public function handle()
    {
        Event::whereApproved(false)->whereNull('reason')->whereUpdated(0)->limit(10)->each(function (Event $event) {
            try {
                $response = $this->openRouterRequest(
                    $event->thumbnail_url,
                    $event->title,
                    $event->description,
                    $event->tags->map(fn(Tag $tag) => $tag->name)->toArray()
                );

                // Если ЛЛМ вернула валидный ответ с полями
                if ($response && isset($response->approved)) {
                    $event->update([
                        'approved' => $response->approved,
                        'reason' => $response->reason ?? null
                    ]);

                    NewEventNotificationJob::dispatch($event->id);
                } else {
                    Log::channel('userlog')->warning("OpenRouter returned invalid response format for event ID: {$event->id}");
                }

            } catch (\Throwable $e) {
                // Логируем ошибку для конкретного события и идем дальше к следующим
                Log::channel('userlog')->error("Failed to process approval for event ID {$event->id}: " . $e->getMessage());
            }
        });
    }

    public function openRouterRequest(string $imageUrl, string $title, string $description, array $tags = [])
    {
        // 1. Отправляем запрос через фасад Http к OpenRouter API, передавая URL изображения
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => config('services.openrouter.model'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Ты модератор контента на анонс-платформе. Твой ответ должен быть СТРОГО в формате JSON без маркдаун-оберток (не используй ```json) и без лишнего текста, например: {"approved":false,"reason":"причина на языке описания"}. Поле reason нужно только если approved равен false. Запрещается публиковать: контент, нарушающий законодательство, призывающий к насилию, экстремизму, розни; материалы оскорбительного, порнографического или мошеннического характера; спам, коммерческую рекламу без согласования с Администрацией; персональные данные третьих лиц без их предварительного согласия. Анализируй также картинку перед принятием решения.'
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => sprintf('Title: %s Description: %s Tags: %s', $title, $description, implode(',', $tags)),
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imageUrl, // Передаем публичную ссылку на файл
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // 2. Обработка ответа
        if ($response->successful()) {
            $result = $response->json();
            $answer = $result['choices'][0]['message']['content'] ?? null;

            $this->info($answer);

            if (!$answer) {
                Log::channel('userlog')->warning("OpenRouter returned empty content for image: {$imageUrl}");
                return null;
            }

            // Очищаем ответ от возможных markdown-тегов (```json ... ```)
            $cleanAnswer = preg_replace('/```(?:json)?\s*([\s\S]*?)\s*```/', '$1', $answer);
            $cleanAnswer = trim($cleanAnswer);

            // Пытаемся декодировать JSON
            $decoded = json_decode($cleanAnswer);

            // Если это не JSON или произошла ошибка парсинга — защищаем код от падения
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning("OpenRouter did not return valid JSON. Response was: " . $answer);

                // Если в ответе модель написала что-то вроде "true" или содержала approved
                if (stripos($answer, 'true') !== false) {
                    return (object)['approved' => true, 'reason' => null];
                }
                if (stripos($answer, 'false') !== false) {
                    return (object)['approved' => false, 'reason' => 'Model returned non-JSON response'];
                }

                return null;
            }

            return $decoded;
        }

        $this->error('OpenRouter API Error: ' . $response->body());
        Log::channel('userlog')->error('OpenRouter API Error: ' . $response->body());

        return null;
    }
}
