<?php

namespace Tests\Feature;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\FinancialReminderService;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class FinancialReminderFeatureTest extends TestCase
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

        $this->bootstrapSchema();
        $this->resetTables();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
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
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tb_notification_preferences')) {
            Schema::create('tb_notification_preferences', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_user')->unique();
                $table->boolean('reminders_enabled')->default(true);
                $table->boolean('daily_reminder_enabled')->default(true);
                $table->unsignedTinyInteger('daily_reminder_hour')->default(20);
                $table->boolean('budget_alert_enabled')->default(true);
                $table->unsignedTinyInteger('budget_alert_threshold')->default(80);
                $table->boolean('dream_reminder_enabled')->default(true);
                $table->unsignedTinyInteger('dream_inactive_days')->default(7);
                $table->dateTime('last_daily_reminder_sent_at')->nullable();
                $table->string('last_budget_alert_sent_key')->nullable();
                $table->string('last_dream_reminder_sent_key')->nullable();
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
                $table->string('periode');
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

        if (!Schema::hasTable('tb_impian_setoran')) {
            Schema::create('tb_impian_setoran', function (Blueprint $table) {
                $table->bigIncrements('id_setoran_impian');
                $table->unsignedBigInteger('id_impian');
                $table->unsignedBigInteger('id_user');
                $table->unsignedBigInteger('nominal');
                $table->string('keterangan', 255)->nullable();
                $table->dateTime('tanggal');
                $table->timestamps();
            });
        }
    }

    protected function resetTables(): void
    {
        DB::table('tb_impian_setoran')->delete();
        DB::table('tb_impian')->delete();
        DB::table('tb_budget_kategori')->delete();
        DB::table('tb_transaksi')->delete();
        DB::table('tb_notification_preferences')->delete();
        DB::table('personal_access_tokens')->delete();
        DB::table('users')->delete();
    }

    protected function createUser(string $username = 'reminder_user'): User
    {
        return User::create([
            'username' => $username,
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
            'fcm_token' => 'token-' . $username,
        ]);
    }

    public function test_api_can_save_and_read_notification_preferences(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('pref-test')->plainTextToken;

        $this->postJson('/api/notification/preferences', [
            'daily_reminder_hour' => 21,
            'budget_alert_threshold' => 85,
            'dream_inactive_days' => 5,
            'daily_reminder_enabled' => true,
            'budget_alert_enabled' => true,
            'dream_reminder_enabled' => false,
            'reminders_enabled' => true,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200)
            ->assertJsonPath('data.daily_reminder_hour', 21)
            ->assertJsonPath('data.budget_alert_threshold', 85)
            ->assertJsonPath('data.dream_inactive_days', 5)
            ->assertJsonPath('data.dream_reminder_enabled', false);

        $this->getJson('/api/notification/preferences', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200)
            ->assertJsonPath('data.daily_reminder_hour', 21)
            ->assertJsonPath('data.budget_alert_threshold', 85);

        $this->assertDatabaseHas('tb_notification_preferences', [
            'id_user' => $user->id_user,
            'daily_reminder_hour' => 21,
            'budget_alert_threshold' => 85,
        ]);
    }

    public function test_daily_reminder_dispatch_sends_once_and_updates_last_sent_at(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 28, 20, 0, 0));

        $user = $this->createUser('daily_user');
        NotificationPreference::create([
            'id_user' => $user->id_user,
            'reminders_enabled' => true,
            'daily_reminder_enabled' => true,
            'daily_reminder_hour' => 20,
            'budget_alert_enabled' => false,
            'budget_alert_threshold' => 80,
            'dream_reminder_enabled' => false,
            'dream_inactive_days' => 7,
        ]);

        $firebaseMock = Mockery::mock(FirebaseService::class);
        $firebaseMock->shouldReceive('sendFinancialReminderNotification')
            ->once()
            ->andReturn(true);
        $this->app->instance(FirebaseService::class, $firebaseMock);

        $summary = app(FinancialReminderService::class)->dispatchForCurrentHour(now());

        $this->assertSame(1, $summary['processed']);
        $this->assertSame(1, $summary['daily']);
        $this->assertNotNull(NotificationPreference::where('id_user', $user->id_user)->value('last_daily_reminder_sent_at'));

        Carbon::setTestNow();
    }
}
