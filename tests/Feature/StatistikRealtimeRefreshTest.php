<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StatistikRealtimeRefreshTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite driver tidak tersedia di environment ini.');
        }

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->bootstrapSchema();
        $this->resetTables();
    }

    protected function bootstrapSchema(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->bigIncrements('id_user');
                $table->string('username')->unique();
                $table->string('password');
                $table->string('role')->default('user');
                $table->unsignedTinyInteger('active')->default(1);
                $table->string('remember_token', 100)->nullable();
                $table->text('fcm_token')->nullable();
            });
        }

        if (!Schema::hasTable('tb_saldo_user')) {
            Schema::create('tb_saldo_user', function (Blueprint $table) {
                $table->bigIncrements('id_saldo');
                $table->unsignedBigInteger('id_user');
                $table->decimal('saldo', 20, 2)->default(0);
                $table->decimal('pemasukan', 20, 2)->default(0);
                $table->decimal('pengeluaran', 20, 2)->default(0);
                $table->decimal('target_pengeluaran', 20, 2)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tb_transaksi')) {
            Schema::create('tb_transaksi', function (Blueprint $table) {
                $table->bigIncrements('id_transaksi');
                $table->unsignedBigInteger('id_user');
                $table->string('tipe');
                $table->decimal('nominal', 20, 2);
                $table->string('kategori')->nullable();
                $table->text('keterangan')->nullable();
                $table->string('icon')->nullable();
                $table->dateTime('tanggal');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tb_budget_kategori')) {
            Schema::create('tb_budget_kategori', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_user');
                $table->string('kategori');
                $table->decimal('nominal', 20, 2);
                $table->string('periode')->default('bulanan');
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_akhir')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tb_impian')) {
            Schema::create('tb_impian', function (Blueprint $table) {
                $table->bigIncrements('id_impian');
                $table->unsignedBigInteger('id_user');
                $table->string('nama_barang');
                $table->string('foto_barang')->nullable();
                $table->decimal('harga_barang', 20, 2);
                $table->date('deadline')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tb_motivasi')) {
            Schema::create('tb_motivasi', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('tipe')->default('text');
                $table->text('isi')->nullable();
                $table->string('foto')->nullable();
                $table->timestamps();
            });
        }
    }

    protected function resetTables(): void
    {
        DB::table('tb_motivasi')->delete();
        DB::table('tb_impian')->delete();
        DB::table('tb_budget_kategori')->delete();
        DB::table('tb_transaksi')->delete();
        DB::table('tb_saldo_user')->delete();
        DB::table('users')->delete();
    }

    protected function createUser(string $username): User
    {
        return User::create([
            'username' => $username,
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);
    }

    public function test_simpan_pemasukan_returns_statistik_snapshot_payload(): void
    {
        $user = $this->createUser('statistik_income');

        $response = $this->actingAs($user)->postJson('/user/simpanPemasukan', [
            'nominal' => 150000,
            'kategori' => 'freelance',
            'keterangan' => 'proyek dadakan',
            'tanggal' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.saldo', 150000)
            ->assertJsonPath('data.summary.monthly_pemasukan', 150000);

        $this->assertStringContainsString(
            'Freelance',
            $response->json('data.fragments.recent_activities_html')
        );
    }

    public function test_simpan_pengeluaran_returns_over_budget_summary_and_fragments(): void
    {
        $user = $this->createUser('statistik_expense');

        DB::table('tb_saldo_user')->insert([
            'id_user' => $user->id_user,
            'saldo' => 0,
            'pemasukan' => 0,
            'pengeluaran' => 0,
            'target_pengeluaran' => 50000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tb_budget_kategori')->insert([
            'id_user' => $user->id_user,
            'kategori' => 'makan',
            'nominal' => 50000,
            'periode' => 'bulanan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/user/simpanPengeluaran', [
            'nominal' => 75000,
            'kategori' => 'makan',
            'keterangan' => 'makan malam',
            'tanggal' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.monthly_pengeluaran', 75000)
            ->assertJsonPath('data.summary.is_over_budget', true);

        $this->assertStringContainsString(
            'OVER BUDGET',
            $response->json('data.fragments.budget_section_html')
        );
        $this->assertStringContainsString(
            'Uang Keluar vs Lalu',
            $response->json('data.fragments.performance_summary_html')
        );
    }

    public function test_statistik_snapshot_endpoint_returns_consistent_shape(): void
    {
        $user = $this->createUser('statistik_snapshot');
        $now = Carbon::now();

        DB::table('tb_saldo_user')->insert([
            'id_user' => $user->id_user,
            'saldo' => 0,
            'pemasukan' => 0,
            'pengeluaran' => 0,
            'target_pengeluaran' => 200000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tb_transaksi')->insert([
            [
                'id_user' => $user->id_user,
                'tipe' => 'pemasukan',
                'nominal' => 250000,
                'kategori' => 'gaji',
                'keterangan' => 'gaji bulanan',
                'tanggal' => $now->copy()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_user' => $user->id_user,
                'tipe' => 'pengeluaran',
                'nominal' => 50000,
                'kategori' => 'makan',
                'keterangan' => 'makan siang',
                'tanggal' => $now->copy()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('tb_impian')->insert([
            'id_user' => $user->id_user,
            'nama_barang' => 'Laptop Belajar',
            'harga_barang' => 5000000,
            'deadline' => $now->copy()->addMonths(6)->toDateString(),
            'keterangan' => 'Target semester ini',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/user/statistik/snapshot');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.saldo', 200000)
            ->assertJsonPath('data.summary.monthly_pemasukan', 250000)
            ->assertJsonPath('data.summary.monthly_pengeluaran', 50000)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary' => [
                        'saldo',
                        'monthly_pemasukan',
                        'monthly_pengeluaran',
                        'target_pengeluaran',
                        'is_over_budget',
                        'prev_month_pemasukan',
                        'prev_month_pengeluaran',
                        'avg_savings',
                        'trend',
                        'most_productive_month',
                        'most_wasteful_month',
                    ],
                    'cashflow_series' => [
                        '7d',
                        '30d',
                        '3m',
                        '12m',
                    ],
                    'fragments' => [
                        'budget_section_html',
                        'dream_forecast_html',
                        'recent_activities_html',
                        'performance_summary_html',
                    ],
                ],
            ]);
    }
}
