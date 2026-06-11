<?php

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('bank_name')->nullable();
            $table->string('screenshot')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Order::query()
            ->whereNotNull('payment_screenshot')
            ->each(function (Order $order) {
                if (PaymentTransaction::where('order_id', $order->id)->exists()) {
                    return;
                }

                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $order->total,
                    'bank_name' => $order->bank_name,
                    'screenshot' => $order->payment_screenshot,
                    'status' => in_array($order->payment_status, ['paid', 'partial'], true) ? 'approved' : 'pending',
                    'reviewed_at' => in_array($order->payment_status, ['paid', 'partial'], true) ? $order->updated_at : null,
                ]);
            });

        $adminRole = Role::where('slug', Role::SLUG_ADMIN)->first();
        if ($adminRole) {
            $permissions = $adminRole->permissions ?? [];
            if (! in_array('transactions', $permissions, true)) {
                $permissions[] = 'transactions';
                $adminRole->update(['permissions' => $permissions]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');

        $adminRole = Role::where('slug', Role::SLUG_ADMIN)->first();
        if ($adminRole) {
            $permissions = array_values(array_filter(
                $adminRole->permissions ?? [],
                fn ($p) => $p !== 'transactions'
            ));
            $adminRole->update(['permissions' => $permissions]);
        }
    }
};
