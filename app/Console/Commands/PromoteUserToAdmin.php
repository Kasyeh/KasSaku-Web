<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PromoteUserToAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:promote-admin {username} {--force : Promote without confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote an existing user account to admin role';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = (string) $this->argument('username');
        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User '{$username}' tidak ditemukan.");
            return 1;
        }

        if ($user->role === 'admin') {
            $this->info("User '{$username}' sudah memiliki role admin.");
            return 0;
        }

        if (!$this->option('force')) {
            $confirmed = $this->confirm("Promosikan user '{$username}' menjadi admin?");
            if (!$confirmed) {
                $this->warn('Aksi dibatalkan.');
                return 1;
            }
        }

        $user->role = 'admin';
        $user->save();

        Log::info('User promoted to admin via artisan command', [
            'username' => $user->username,
            'id_user' => $user->id_user,
        ]);

        $this->info("User '{$username}' berhasil dipromosikan menjadi admin.");
        return 0;
    }
}
