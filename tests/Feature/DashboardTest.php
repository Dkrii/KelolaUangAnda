<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_visiting_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create(['name' => 'Siti Rahma']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Keuangan');
        $response->assertSee('Total Saldo');
        $response->assertSee('Pemasukan Bulan Ini');
        $response->assertSee('Pengeluaran Bulan Ini');
        $response->assertSee('Sisa Anggaran');
        $response->assertSee('Rp 0');
        $response->assertSee('Belum ada transaksi');
    }

    public function test_landing_page_can_be_viewed_by_guest(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Kelola Uang Anda');
        $response->assertSee('Daftar Gratis');
        $response->assertSee('Masuk');
    }
}
