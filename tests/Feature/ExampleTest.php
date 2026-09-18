<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_can_be_rendered(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('Solusi Anda')
            ->assertSee('Prioritas')
            ->assertSee('hero-driver-evaluation.png', false)
            ->assertSee('Solusi Empat Pilar Bisnis')
            ->assertSee('Kelola Pembersihan &amp; Perawatan AC', false)
            ->assertSee('PT. Jamkrida Bali Mandara')
            ->assertSee('Lembaga Perkreditan Desa (LPD) se-Bali')
            ->assertDontSee('Solusi Tiga Pilar Bisnis')
            ->assertDontSee('images/bds/armada.png', false);

        $this->assertSame(4, substr_count($response->getContent(), 'class="bds-service-card"'));
        $this->assertSame(8, substr_count($response->getContent(), 'class="bds-partner"'));
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }
}
