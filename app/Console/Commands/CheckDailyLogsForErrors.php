<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class CheckDailyLogsForErrors extends Command
{
    /**
     * Имя команды для запуска через CLI или Scheduler.
     */
    protected $signature = 'log:check-errors {--date= : Дата в формате YYYY-MM-DD (по умолчанию вчера)}';

    protected $description = 'Проверяет лог предыдущего дня на наличие ошибок и отправляет уведомление в Telegram';

    public function handle()
    {
        // 1. Вычисляем дату (вчерашний день по умолчанию)
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->format('Y-m-d')
            : Carbon::yesterday()->format('Y-m-d');

        // Имя файла daily-лога
        $fileName = "userlog-{$date}.log";
        $logPath = storage_path("logs/{$fileName}");

        if (!File::exists($logPath)) {
            $this->info("Файл логов {$fileName} не найден.");
            return Command::SUCCESS;
        }

        // 2. Читаем файл логов
        $fileContent = File::get($logPath);
        $lines = explode("\n", $fileContent);

        $errors = [];

        // Ищем ключевые маркеры ошибок (CRITICAL, ERROR, EMERGENCY)
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            if (
                str_contains($line, '.ERROR:') ||
                str_contains($line, '.CRITICAL:') ||
                str_contains($line, '.EMERGENCY:')
            ) {
                // Обрезаем длинные строки до 200 символов для компактности
                $errors[] = mb_substr($line, 0, 200) . '...';
            }
        }

        // 3. Если ошибки найдены — отправляем в Telegram
        $totalErrors = count($errors);

        if ($totalErrors > 0) {
            $this->warn("Обнаружено {$totalErrors} ошибок за {$date}. Отправка в Telegram...");

            $message = "🚨 *Алерт ошибок сервера за {$date}*\n\n";
            $message .= "Всего ошибок: *{$totalErrors}*\n\n";
            $message .= "*Первые логи:* \n";

            // Берем первые 5 уникальных/характерных ошибок, чтобы не упереться в лимит Telegram (4096 символов)
            $sampleErrors = array_slice($errors, 0, 5);
            foreach ($sampleErrors as $err) {
                // Экранируем спецсимволы Markdown V2
                $cleanErr = str_replace(['`', '*', '_'], '', $err);
                $message .= "```text\n" . $cleanErr . "\n```\n";
            }

            if ($totalErrors > 5) {
                $message .= "\n_... и еще " . ($totalErrors - 5) . " ошибок в файле " . $fileName . "_";
            }

            $this->sendTelegramNotification($message);
            $this->info('Уведомление успешно отправлено.');
        } else {
            $this->info("За {$date} ошибок не обнаружено.");
        }

        return Command::SUCCESS;
    }

    /**
     * Отправка сообщения через Telegram Bot API
     */
    private function sendTelegramNotification(string $text)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_ALERT_CHAT_ID');

        if (!$botToken || !$chatId) {
            $this->error('Параметры TELEGRAM_BOT_TOKEN или TELEGRAM_ALERT_CHAT_ID не заданы в .env!');
            return;
        }

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }
}