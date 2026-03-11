<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    public function test_guest_can_switch_locale_and_see_translated_login_screen(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(route('locale.switch'), ['locale' => 'ru']);

        $response->assertRedirect(route('login'));
        $response->assertCookie('locale', 'ru');
        $this->assertSame('ru', session('locale'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Вход в систему')
            ->assertSee('Номер телефона');
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(route('locale.switch'), ['locale' => 'de']);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('locale');
        $this->assertNotSame('de', session('locale'));
        $response->assertCookieMissing('locale');
    }
}
