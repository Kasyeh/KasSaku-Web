<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TransactionModel;
use App\Services\TransactionService;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
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

        if (!Schema::hasTable('tb_permintaan_unblock')) {
            Schema::create('tb_permintaan_unblock', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_user');
                $table->text('pesan');
                $table->string('status')->default('pending');
                $table->text('alasan_admin')->nullable();
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
    }

    protected function resetTables(): void
    {
        DB::table('personal_access_tokens')->delete();
        DB::table('tb_permintaan_unblock')->delete();
        DB::table('tb_transaksi')->delete();
        DB::table('tb_saldo_user')->delete();
        DB::table('users')->delete();
    }

    public function test_user_a_cannot_save_fcm_token_for_user_b(): void
    {
        $userA = User::create([
            'username' => 'user_a',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);

        $userB = User::create([
            'username' => 'user_b',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);

        $token = $userA->createToken('test-a')->plainTextToken;

        $this->postJson('/api/fcm-token', [
            'user_id' => $userB->id_user,
            'token' => 'fcm_token_baru',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', [
            'id_user' => $userB->id_user,
            'fcm_token' => 'fcm_token_baru',
        ]);
    }

    public function test_user_a_cannot_send_test_notification_to_user_b(): void
    {
        $userA = User::create([
            'username' => 'user_a_test',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
            'fcm_token' => 'fcm_a',
        ]);

        $userB = User::create([
            'username' => 'user_b_test',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
            'fcm_token' => 'fcm_b',
        ]);

        $token = $userA->createToken('test-a-2')->plainTextToken;

        $this->postJson('/api/notification/test', [
            'user_id' => $userB->id_user,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }

    public function test_public_unblock_request_cannot_spoof_id_user(): void
    {
        $blockedA = User::create([
            'username' => 'blocked_a',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 0,
        ]);

        $blockedB = User::create([
            'username' => 'blocked_b',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 0,
        ]);

        $this->postJson('/api/unblock-request', [
            'username' => $blockedA->username,
            'password' => 'Password!1',
            'id_user' => $blockedB->id_user,
            'pesan' => 'Mohon dibuka blokir akun saya.',
        ])->assertStatus(200);

        $this->assertDatabaseHas('tb_permintaan_unblock', [
            'id_user' => $blockedA->id_user,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('tb_permintaan_unblock', [
            'id_user' => $blockedB->id_user,
        ]);
    }

    public function test_register_internal_error_does_not_expose_exception_details(): void
    {
        Schema::dropIfExists('users');

        $response = $this->postJson('/api/register', [
            'username' => 'will_fail',
            'password' => 'Password!1',
        ])->assertStatus(500);

        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        $this->assertStringNotContainsString('no such table', strtolower($response->getContent()));
    }

    public function test_unblock_request_internal_error_does_not_expose_exception_details(): void
    {
        $blocked = User::create([
            'username' => 'blocked_error',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 0,
        ]);

        Schema::dropIfExists('tb_permintaan_unblock');

        $response = $this->postJson('/api/unblock-request', [
            'username' => $blocked->username,
            'password' => 'Password!1',
            'pesan' => 'uji error',
        ])->assertStatus(500);

        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        $this->assertStringNotContainsString('no such table', strtolower($response->getContent()));
    }

    public function test_transaction_service_rolls_back_when_balance_update_fails(): void
    {
        $user = User::create([
            'username' => 'trx_user',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);

        Schema::dropIfExists('tb_saldo_user');

        try {
            TransactionService::createTransaction($user->id_user, 'pemasukan', '10000', 'lainnya', 'uji rollback');
            $this->fail('Expected QueryException was not thrown.');
        } catch (QueryException $e) {
            $this->assertTrue(true);
        }

        $this->assertSame(0, DB::table('tb_transaksi')->count());
    }

    public function test_api_user_endpoint_hides_password_and_remember_token(): void
    {
        $user = User::create([
            'username' => 'secure_user',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
            'remember_token' => 'secret-token',
        ]);

        $token = $user->createToken('user-endpoint')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200);

        $payload = $response->json();
        $this->assertIsArray($payload);
        $this->assertArrayNotHasKey('password', $payload);
        $this->assertArrayNotHasKey('remember_token', $payload);
    }

    public function test_legacy_route_rejects_path_user_id_mismatch(): void
    {
        $userA = User::create([
            'username' => 'path_user_a',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);
        $userB = User::create([
            'username' => 'path_user_b',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);

        DB::table('tb_saldo_user')->insert([
            'id_user' => $userA->id_user,
            'saldo' => 10000,
            'pemasukan' => 10000,
            'pengeluaran' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = $userA->createToken('path-mismatch')->plainTextToken;

        $this->getJson('/api/user/' . $userB->id_user . '/saldo', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }

    public function test_me_saldo_route_returns_authenticated_user_data(): void
    {
        $user = User::create([
            'username' => 'me_saldo_user',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);

        DB::table('tb_saldo_user')->insert([
            'id_user' => $user->id_user,
            'saldo' => 25000,
            'pemasukan' => 25000,
            'pengeluaran' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = $user->createToken('me-saldo')->plainTextToken;

        $this->getJson('/api/me/saldo', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200)
            ->assertJsonPath('data.id_user', $user->id_user);
    }

    public function test_post_transaction_rejects_body_user_id_mismatch(): void
    {
        $userA = User::create([
            'username' => 'trx_user_a',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);
        $userB = User::create([
            'username' => 'trx_user_b',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);

        DB::table('tb_saldo_user')->insert([
            'id_user' => $userA->id_user,
            'saldo' => 0,
            'pemasukan' => 0,
            'pengeluaran' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = $userA->createToken('trx-body-mismatch')->plainTextToken;

        $this->postJson('/api/pemasukan/tambah', [
            'id_user' => $userB->id_user,
            'nominal' => 15000,
            'kategori' => 'gaji',
            'keterangan' => 'uji mismatch',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }

    public function test_export_pdf_returns_422_when_filtered_result_is_empty(): void
    {
        $user = User::create([
            'username' => 'pdf_empty_user',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);
        $token = $user->createToken('pdf-empty')->plainTextToken;

        $this->get('/api/me/riwayat/export-pdf?search=tidak_ada_data', [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Riwayat masih kosong nih');
    }

    public function test_export_pdf_supports_tipe_and_search_filters(): void
    {
        $user = User::create([
            'username' => 'pdf_filter_user',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);
        $token = $user->createToken('pdf-filter')->plainTextToken;

        TransactionModel::create([
            'id_user' => $user->id_user,
            'tipe' => 'pemasukan',
            'nominal' => 10000,
            'kategori' => 'gaji',
            'keterangan' => 'bonus bulanan',
            'tanggal' => now(),
        ]);
        TransactionModel::create([
            'id_user' => $user->id_user,
            'tipe' => 'pengeluaran',
            'nominal' => 5000,
            'kategori' => 'makan',
            'keterangan' => 'harian',
            'tanggal' => now(),
        ]);

        $response = $this->get('/api/me/riwayat/export-pdf?tipe=pemasukan&search=bonus', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200);

        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }
}
