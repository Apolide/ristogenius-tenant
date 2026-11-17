<?php

namespace Tests\Feature\Localization;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InterfaceLocalizationTest extends TestCase
{
    #[DataProvider('locales')]
    public function test_notification_overlay_is_localized(string $locale, string $title, string $empty, string $action, string $count): void
    {
        app()->setLocale($locale);
        $html = (string) $this->view('livewire.header.notifications', [
            'notifications' => new Collection,
            'unreadCount' => 3,
        ]);

        foreach ([$title, $empty, $action, $count] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        if ($locale !== 'it') {
            $this->assertStringNotContainsString('Notifiche', $html);
            $this->assertStringNotContainsString('non lette', $html);
        }
    }

    public static function locales(): array
    {
        return [
            ['it', 'Notifiche', 'Nessuna notifica', 'Segna come lette', '3 non lette'],
            ['en', 'Notifications', 'No notifications', 'Mark all as read', '3 unread'],
            ['de', 'Benachrichtigungen', 'Keine Benachrichtigungen', 'Alle als gelesen markieren', '3 ungelesen'],
        ];
    }

    public function test_order_event_translations_have_all_italian_keys(): void
    {
        $italian = require lang_path('it/orders.php');
        foreach (['en', 'de'] as $locale) {
            $translated = require lang_path($locale.'/orders.php');
            foreach ($italian as $group => $values) {
                $this->assertSame(array_keys($values), array_keys($translated[$group]));
                foreach ($values as $key => $value) {
                    if ($group === 'events') {
                        $this->assertNotSame($value, $translated[$group][$key], $locale.'.'.$group.'.'.$key);
                    }
                }
            }
        }
    }
}
