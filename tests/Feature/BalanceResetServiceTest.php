<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BalanceResetService;
use App\Services\ImpianProgressService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BalanceResetServiceTest extends TestCase
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

    protected function seedBalance(int $userId, float $saldo, float $pemasukan, float $pengeluaran): void
    {
        DB::table('tb_saldo_user')->insert([
            'id_user' => $userId,
            'saldo' => $saldo,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function createDream(int $userId, string $name = 'Laptop Belajar', float $target = 5000000): int
    {
        return (int) DB::table('tb_impian')->insertGetId([
            'id_user' => $userId,
            'nama_barang' => $name,
            'harga_barang' => $target,
            'deadline' => now()->addMonths(6)->toDateString(),
            'keterangan' => 'Target belajar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function insertTransaction(
        int $userId,
        string $type,
        float $nominal,
        Carbon $tanggal,
        ?string $kategori = null,
        ?string $keterangan = null
    ): void {
        DB::table('tb_transaksi')->insert([
            'id_user' => $userId,
            'tipe' => $type,
            'nominal' => $nominal,
            'kategori' => $kategori,
            'keterangan' => $keterangan,
            'tanggal' => $tanggal->toDateTimeString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function insertDreamDeposit(
        int $dreamId,
        int $userId,
        float $nominal,
        Carbon $tanggal,
        ?string $keterangan = null
    ): void {
        DB::table('tb_impian_setoran')->insert([
            'id_impian' => $dreamId,
            'id_user' => $userId,
            'nominal' => $nominal,
            'keterangan' => $keterangan,
            'tanggal' => $tanggal->toDateTimeString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_reset_current_month_deletes_current_month_transactions_and_dream_deposits(): void
    {
        $user = $this->createUser('reset_current_month');
        $dreamId = $this->createDream($user->id_user);
        $now = Carbon::create(2026, 4, 25, 10, 0, 0);

        $this->seedBalance($user->id_user, 600000, 1000000, 400000);
        $this->insertTransaction($user->id_user, 'pemasukan', 1000000, $now->copy()->subDays(2), 'gaji', 'gaji bulan ini');
        $this->insertTransaction($user->id_user, 'pengeluaran', 400000, $now->copy()->subDay(), 'Tabungan Impian', 'Setoran impian');
        $this->insertDreamDeposit($dreamId, $user->id_user, 400000, $now->copy()->subDay(), 'Setoran bulan ini');

        $result = app(BalanceResetService::class)->resetCurrentMonth($user->id_user, $now);

        $this->assertSame(2, $result['deleted_transaction_count']);
        $this->assertSame(1, $result['deleted_dream_deposit_count']);
        $this->assertSame(0.0, (float) $result['saldo']);
        $this->assertSame(0, DB::table('tb_transaksi')->where('id_user', $user->id_user)->count());
        $this->assertSame(0, DB::table('tb_impian_setoran')->where('id_user', $user->id_user)->count());

        $dream = \App\Models\ImpianModel::findOrFail($dreamId);
        $dream = ImpianProgressService::attachProgressToDream($dream);

        $this->assertSame(0, (int) $dream->dana_terkumpul);
    }

    public function test_reset_current_month_preserves_previous_month_transactions_and_dream_deposits(): void
    {
        $user = $this->createUser('reset_preserve_other_months');
        $dreamId = $this->createDream($user->id_user);
        $now = Carbon::create(2026, 4, 25, 10, 0, 0);
        $lastMonth = $now->copy()->subMonth();

        $this->seedBalance($user->id_user, 1200000, 1500000, 300000);

        $this->insertTransaction($user->id_user, 'pemasukan', 500000, $lastMonth->copy()->day(12), 'gaji', 'gaji bulan lalu');
        $this->insertTransaction($user->id_user, 'pengeluaran', 100000, $lastMonth->copy()->day(13), 'Tabungan Impian', 'Setoran bulan lalu');
        $this->insertDreamDeposit($dreamId, $user->id_user, 100000, $lastMonth->copy()->day(13), 'Setoran bulan lalu');

        $this->insertTransaction($user->id_user, 'pemasukan', 1000000, $now->copy()->day(20), 'gaji', 'gaji bulan ini');
        $this->insertTransaction($user->id_user, 'pengeluaran', 200000, $now->copy()->day(21), 'Tabungan Impian', 'Setoran bulan ini');
        $this->insertDreamDeposit($dreamId, $user->id_user, 200000, $now->copy()->day(21), 'Setoran bulan ini');

        $result = app(BalanceResetService::class)->resetCurrentMonth($user->id_user, $now);

        $this->assertSame(2, $result['deleted_transaction_count']);
        $this->assertSame(1, $result['deleted_dream_deposit_count']);
        $this->assertSame(400000.0, (float) $result['saldo']);
        $this->assertSame(500000.0, (float) $result['pemasukan']);
        $this->assertSame(100000.0, (float) $result['pengeluaran']);
        $this->assertSame(2, DB::table('tb_transaksi')->where('id_user', $user->id_user)->count());
        $this->assertSame(1, DB::table('tb_impian_setoran')->where('id_user', $user->id_user)->count());

        $dream = \App\Models\ImpianModel::findOrFail($dreamId);
        $dream = ImpianProgressService::attachProgressToDream($dream);

        $this->assertSame(100000, (int) $dream->dana_terkumpul);
    }

    public function test_reset_current_month_succeeds_without_dream_deposits(): void
    {
        $user = $this->createUser('reset_without_dream_deposits');
        $now = Carbon::create(2026, 4, 25, 10, 0, 0);

        $this->seedBalance($user->id_user, 300000, 500000, 200000);
        $this->insertTransaction($user->id_user, 'pemasukan', 500000, $now->copy()->day(10), 'gaji', 'gaji bulan ini');
        $this->insertTransaction($user->id_user, 'pengeluaran', 200000, $now->copy()->day(11), 'makan', 'belanja bulan ini');

        $result = app(BalanceResetService::class)->resetCurrentMonth($user->id_user, $now);

        $this->assertSame(2, $result['deleted_transaction_count']);
        $this->assertSame(0, $result['deleted_dream_deposit_count']);
        $this->assertSame(0.0, (float) $result['saldo']);
        $this->assertSame(0, DB::table('tb_impian_setoran')->where('id_user', $user->id_user)->count());
    }

    public function test_reset_current_month_removes_dream_deposits_even_without_current_month_transactions(): void
    {
        $user = $this->createUser('reset_dream_only');
        $dreamId = $this->createDream($user->id_user);
        $now = Carbon::create(2026, 4, 25, 10, 0, 0);
        $lastMonth = $now->copy()->subMonth();

        $this->seedBalance($user->id_user, 700000, 900000, 200000);
        $this->insertTransaction($user->id_user, 'pemasukan', 900000, $lastMonth->copy()->day(5), 'gaji', 'gaji bulan lalu');
        $this->insertTransaction($user->id_user, 'pengeluaran', 200000, $lastMonth->copy()->day(6), 'makan', 'belanja bulan lalu');
        $this->insertDreamDeposit($dreamId, $user->id_user, 150000, $now->copy()->day(22), 'Setoran tanpa transaksi bulan ini');

        $result = app(BalanceResetService::class)->resetCurrentMonth($user->id_user, $now);

        $this->assertSame(0, $result['deleted_transaction_count']);
        $this->assertSame(1, $result['deleted_dream_deposit_count']);
        $this->assertSame(700000.0, (float) $result['saldo']);
        $this->assertSame(2, DB::table('tb_transaksi')->where('id_user', $user->id_user)->count());
        $this->assertSame(0, DB::table('tb_impian_setoran')->where('id_user', $user->id_user)->count());
    }

    public function test_reset_current_month_only_affects_the_target_user(): void
    {
        $userA = $this->createUser('reset_user_a');
        $userB = $this->createUser('reset_user_b');
        $dreamA = $this->createDream($userA->id_user, 'Laptop A');
        $dreamB = $this->createDream($userB->id_user, 'Laptop B');
        $now = Carbon::create(2026, 4, 25, 10, 0, 0);

        $this->seedBalance($userA->id_user, 600000, 1000000, 400000);
        $this->seedBalance($userB->id_user, 350000, 500000, 150000);

        $this->insertTransaction($userA->id_user, 'pemasukan', 1000000, $now->copy()->day(15), 'gaji', 'gaji A');
        $this->insertTransaction($userA->id_user, 'pengeluaran', 400000, $now->copy()->day(16), 'Tabungan Impian', 'setoran A');
        $this->insertDreamDeposit($dreamA, $userA->id_user, 400000, $now->copy()->day(16), 'deposit A');

        $this->insertTransaction($userB->id_user, 'pemasukan', 500000, $now->copy()->day(15), 'gaji', 'gaji B');
        $this->insertTransaction($userB->id_user, 'pengeluaran', 150000, $now->copy()->day(16), 'Tabungan Impian', 'setoran B');
        $this->insertDreamDeposit($dreamB, $userB->id_user, 150000, $now->copy()->day(16), 'deposit B');

        $result = app(BalanceResetService::class)->resetCurrentMonth($userA->id_user, $now);

        $this->assertSame(2, $result['deleted_transaction_count']);
        $this->assertSame(1, $result['deleted_dream_deposit_count']);
        $this->assertSame(0, DB::table('tb_transaksi')->where('id_user', $userA->id_user)->count());
        $this->assertSame(0, DB::table('tb_impian_setoran')->where('id_user', $userA->id_user)->count());
        $this->assertSame(2, DB::table('tb_transaksi')->where('id_user', $userB->id_user)->count());
        $this->assertSame(1, DB::table('tb_impian_setoran')->where('id_user', $userB->id_user)->count());
    }
}
