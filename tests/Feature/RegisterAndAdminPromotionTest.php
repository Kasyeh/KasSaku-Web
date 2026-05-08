<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegisterAndAdminPromotionTest extends TestCase
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
        DB::table('tb_saldo_user')->delete();
        DB::table('users')->delete();
    }

    public function test_api_register_always_creates_user_role_even_if_role_admin_sent(): void
    {
        $response = $this->postJson('/api/register', [
            'username' => 'apiuser',
            'password' => 'Password!1',
            'role' => 'admin',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'user');

        $this->assertDatabaseHas('users', [
            'username' => 'apiuser',
            'role' => 'user',
        ]);
    }

    public function test_web_register_creates_user_role_and_register_page_has_no_role_field(): void
    {
        $this->get('/register')->assertStatus(200)->assertDontSee('name="role"', false);

        $response = $this->post('/register/action', [
            'username' => 'webuser',
            'password' => 'Password!1',
            'role' => 'admin',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'username' => 'webuser',
            'role' => 'user',
        ]);
    }

    public function test_api_register_returns_friendly_message_for_duplicate_username(): void
    {
        DB::table('users')->insert([
            'username' => 'dupuser',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);

        $response = $this->postJson('/api/register', [
            'username' => 'dupuser',
            'password' => 'Password!1',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.username.0', 'Username sudah digunakan, coba nama lain.');
    }

    public function test_web_register_shows_inline_error_for_duplicate_username(): void
    {
        DB::table('users')->insert([
            'username' => 'dupweb',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);

        $this->followingRedirects()->post('/register/action', [
            'username' => 'dupweb',
            'password' => 'Password!1',
        ])
            ->assertStatus(200)
            ->assertSee('Username sudah digunakan, coba nama lain.', false)
            ->assertDontSee('<ul class="text-[10px] font-bold text-rose-500 text-center leading-relaxed">', false);
    }

    public function test_promote_admin_command_promotes_user_and_is_idempotent(): void
    {
        DB::table('users')->insert([
            'username' => 'targetadmin',
            'password' => bcrypt('Password!1'),
            'role' => 'user',
            'active' => 1,
        ]);

        $firstExit = Artisan::call('user:promote-admin', [
            'username' => 'targetadmin',
            '--force' => true,
        ]);
        $secondExit = Artisan::call('user:promote-admin', [
            'username' => 'targetadmin',
            '--force' => true,
        ]);
        $missingExit = Artisan::call('user:promote-admin', [
            'username' => 'notfound',
            '--force' => true,
        ]);

        $this->assertSame(0, $firstExit);
        $this->assertSame(0, $secondExit);
        $this->assertSame(1, $missingExit);
        $this->assertDatabaseHas('users', [
            'username' => 'targetadmin',
            'role' => 'admin',
        ]);
    }
}
