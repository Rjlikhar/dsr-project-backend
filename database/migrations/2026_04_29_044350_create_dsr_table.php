<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dsr', function (Blueprint $table) {
            $table->id();

            // 👤 Employee who creates DSR
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // 👨‍🏫 Mentor (assigned user)
            $table->foreignId('mentor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // 👀 Code Reviewer (assigned user)
            $table->foreignId('code_reviewer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // 📅 Work date
            $table->date('work_date');

            // 🧠 All DSR data stored in JSON (tasks, notes, hours, etc.)
            $table->json('data');

            // 🔄 Workflow status
            $table->enum('status', [
                'draft',
                'submitted',
                'approved',
                'rejected'
            ])->default('draft');

            // 🧑‍💼 Approval tracking (optional future use)
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // ⚡ Index for performance
            $table->index(['user_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dsr');
    }
};