<?php

namespace Database\Seeders;

use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Enums\ReceiptStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\SavingGoal;
use App\Models\SavingGoalContribution;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class UiReviewSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->orderBy('id')->first();

        if ($user === null) {
            $user = User::factory()->create([
                'name' => 'Dikri Ali',
                'email' => 'admin@gmail.com',
            ]);
        }

        $wallets = $this->seedWallets($user);
        $categories = $this->seedCategories($user);
        $this->seedTransactions($user, $wallets, $categories);
        $this->seedBudgets($user, $categories);
        $this->seedSavingGoals($user);
        $this->seedReceipts($user, $wallets, $categories);
    }

    private function seedWallets(User $user): array
    {
        $definitions = [
            ['name' => 'Tunai', 'type' => WalletType::Cash, 'institution' => 'Dompet', 'account_number' => null, 'initial_balance' => '1500000', 'color' => '#10b981', 'is_active' => true],
            ['name' => 'Mandiri', 'type' => WalletType::Bank, 'institution' => 'Bank Mandiri', 'account_number' => '1370019876543', 'initial_balance' => '12500000', 'color' => '#3b82f6', 'is_active' => true],
            ['name' => 'Dana', 'type' => WalletType::EWallet, 'institution' => 'DANA', 'account_number' => '08113336422', 'initial_balance' => '2750000', 'color' => '#0ea5e9', 'is_active' => true],
            ['name' => 'Rekening Cadangan', 'type' => WalletType::Bank, 'institution' => 'BCA', 'account_number' => '091200445566', 'initial_balance' => '30000000', 'color' => '#8b5cf6', 'is_active' => false],
        ];

        $wallets = [];

        foreach ($definitions as $definition) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'name' => $definition['name']],
                ['user_id' => $user->id] + $definition
            );

            $wallet->update($definition);
            $wallets[$definition['name']] = $wallet;
        }

        return $wallets;
    }

    private function seedCategories(User $user): array
    {
        $definitions = [
            ['Makanan & Minuman', CategoryType::Expense, '#f97316'],
            ['Transportasi', CategoryType::Expense, '#3b82f6'],
            ['Belanja', CategoryType::Expense, '#ec4899'],
            ['Tagihan & Utilitas', CategoryType::Expense, '#eab308'],
            ['Hiburan', CategoryType::Expense, '#8b5cf6'],
            ['Kesehatan', CategoryType::Expense, '#14b8a6'],
            ['Pendidikan', CategoryType::Expense, '#6366f1'],
            ['Olahraga', CategoryType::Expense, '#f43f5e'],
            ['Lainnya', CategoryType::Expense, '#6b7280'],
            ['Gaji', CategoryType::Income, '#10b981'],
            ['Bonus', CategoryType::Income, '#22c55e'],
            ['Investasi', CategoryType::Income, '#0ea5e9'],
        ];

        $categories = [];

        foreach ($definitions as [$name, $type, $color]) {
            $category = Category::firstOrCreate(
                ['user_id' => $user->id, 'type' => $type, 'name' => $name],
                ['user_id' => $user->id, 'color' => $color, 'icon' => null]
            );

            $category->update(['color' => $color]);
            $categories[$name] = $category;
        }

        return $categories;
    }

    private function seedTransactions(User $user, array $wallets, array $categories): void
    {
        $activeWallets = array_values(array_filter(
            $wallets,
            fn (Wallet $wallet): bool => $wallet->is_active
        ));

        $expenseTemplates = [
            ['Makan siang di kantor', 'Makanan & Minuman', 185000],
            ['Bensin motor', 'Transportasi', 95000],
            ['Belanja kebutuhan rumah', 'Belanja', 340000],
            ['Kontrol rutin dokter', 'Kesehatan', 260000],
        ];

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = today()->startOfMonth()->subMonths($offset);
            $date = $offset === 0 ? today()->subDays(3) : $month->copy()->day(15);

            foreach ($expenseTemplates as $index => [$description, $categoryName, $amount]) {
                Transaction::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'wallet_id' => $activeWallets[$index % count($activeWallets)]->id,
                        'type' => TransactionType::Expense,
                        'transaction_date' => $date->copy()->addDays($index),
                        'description' => $description,
                    ],
                    [
                        'category_id' => $categories[$categoryName]->id,
                        'amount' => (string) ($amount + ($offset * 7500)),
                        'destination_wallet_id' => null,
                    ]
                );
            }

            Transaction::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'wallet_id' => $activeWallets[1]->id,
                    'type' => TransactionType::Income,
                    'transaction_date' => $month->copy()->day(1),
                    'description' => 'Gaji bulanan',
                ],
                [
                    'category_id' => $categories['Gaji']->id,
                    'amount' => (string) (8500000 + ($offset * 250000)),
                    'destination_wallet_id' => null,
                ]
            );
        }

        $transfers = [
            ['Tunai', 'Mandiri', 750000, 'Setor tunai ke rekening', 4],
            ['Mandiri', 'Dana', 500000, 'Isi saldo e-wallet', 8],
            ['Dana', 'Tunai', 250000, 'Tarik tunai', 12],
        ];

        foreach ($transfers as [$from, $to, $amount, $description, $daysAgo]) {
            Transaction::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'wallet_id' => $wallets[$from]->id,
                    'type' => TransactionType::Transfer,
                    'transaction_date' => today()->subDays($daysAgo),
                    'description' => $description,
                ],
                [
                    'destination_wallet_id' => $wallets[$to]->id,
                    'category_id' => null,
                    'amount' => (string) $amount,
                ]
            );
        }
    }

    private function seedBudgets(User $user, array $categories): void
    {
        $month = today()->startOfMonth();
        $budgets = [
            ['Makanan & Minuman', '300000'],
            ['Transportasi', '100000'],
            ['Kesehatan', '200000'],
        ];

        foreach ($budgets as [$categoryName, $amount]) {
            Budget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $categories[$categoryName]->id,
                    'month' => $month,
                ],
                [
                    'amount' => $amount,
                    'period' => BudgetPeriod::Monthly,
                ]
            );
        }
    }

    private function seedSavingGoals(User $user): void
    {
        $goals = [
            [
                'name' => 'Liburan ke Yogyakarta',
                'target_amount' => '8000000',
                'saved_amount' => '3200000',
                'deadline' => today()->addMonths(6),
                'description' => 'Dana tiket, hotel, dan makan selama liburan.',
                'completed_at' => null,
                'contributions' => [
                    ['500000', 3, 'Setoran awal'],
                    ['1200000', 2, 'Gaji bulan lalu'],
                    ['1500000', 1, 'Bonus kecil'],
                ],
            ],
            [
                'name' => 'Laptop baru',
                'target_amount' => '12000000',
                'saved_amount' => '12000000',
                'deadline' => today()->subDays(10),
                'description' => 'Pengganti laptop lama yang sudah sering bermasalah.',
                'completed_at' => today()->subDays(9),
                'contributions' => [
                    ['4000000', 6, 'Setoran pertama'],
                    ['3500000', 5, 'Setoran kedua'],
                    ['2500000', 4, 'Setoran ketiga'],
                    ['2000000', 3, 'Pelunasan'],
                ],
            ],
            [
                'name' => 'Renovasi dapur',
                'target_amount' => '15000000',
                'saved_amount' => '4500000',
                'deadline' => today()->subDays(30),
                'description' => 'Rencana mengganti meja dan kabinet dapur.',
                'completed_at' => null,
                'contributions' => [
                    ['2500000', 8, 'Setoran awal'],
                    ['2000000', 7, 'Setoran lanjutan'],
                ],
            ],
        ];

        foreach ($goals as $definition) {
            $goal = SavingGoal::firstOrCreate(
                ['user_id' => $user->id, 'name' => $definition['name']],
                [
                    'user_id' => $user->id,
                    'target_amount' => $definition['target_amount'],
                    'saved_amount' => $definition['saved_amount'],
                    'deadline' => $definition['deadline'],
                    'description' => $definition['description'],
                    'completed_at' => $definition['completed_at'],
                ]
            );

            $goal->update([
                'target_amount' => $definition['target_amount'],
                'saved_amount' => $definition['saved_amount'],
                'deadline' => $definition['deadline'],
                'description' => $definition['description'],
                'completed_at' => $definition['completed_at'],
            ]);

            foreach ($definition['contributions'] as [$amount, $monthsAgo, $note]) {
                SavingGoalContribution::firstOrCreate(
                    [
                        'saving_goal_id' => $goal->id,
                        'amount' => $amount,
                        'contribution_date' => today()->subMonths($monthsAgo)->day(10),
                        'note' => $note,
                    ]
                );
            }
        }
    }

    private function seedReceipts(User $user, array $wallets, array $categories): void
    {
        $source = Storage::disk('local')->exists('receipts/1/struk.png')
            ? 'receipts/1/struk.png'
            : 'receipts/2/struk.png';

        $confirmedTransaction = Transaction::updateOrCreate(
            [
                'user_id' => $user->id,
                'wallet_id' => $wallets['Tunai']->id,
                'type' => TransactionType::Expense,
                'transaction_date' => today()->subDay(),
                'description' => 'Belanja di Kopi Nako',
            ],
            [
                'category_id' => $categories['Makanan & Minuman']->id,
                'amount' => '87450',
                'destination_wallet_id' => null,
            ]
        );

        $receipts = [
            [
                'original_name' => 'struk-diproses.png',
                'path' => 'receipts/ui-review/struk-diproses.png',
                'status' => ReceiptStatus::Pending,
                'merchant' => null,
                'transaction_date' => today(),
                'total' => null,
                'transaction_id' => null,
                'ocr_data' => ['raw_text' => '', 'warnings' => []],
                'items' => [],
            ],
            [
                'original_name' => 'struk-perlu-dikonfirmasi.png',
                'path' => 'receipts/ui-review/struk-perlu-dikonfirmasi.png',
                'status' => ReceiptStatus::Review,
                'merchant' => 'KOPI NAKO',
                'transaction_date' => '2023-06-18',
                'total' => '75400',
                'transaction_id' => null,
                'ocr_data' => [
                    'raw_text' => "KOPI NAKO\nJun 18, 2023 7:32:42 PM\nTAKE AWAY\nKopi Susu 22000\nRoti Bakar 18000\nTOTAL 75,400",
                    'warnings' => [],
                    'suggested_category' => 'Makanan & Minuman',
                    'total_amount' => 75400,
                    'currency' => 'IDR',
                    'transaction_date' => '2023-06-18',
                    'date_status' => 'detected',
                    'parser' => 'receipt-rules-v2',
                ],
                'items' => [
                    ['Kopi Susu', '1', '22000', '22000'],
                    ['Roti Bakar', '1', '18000', '18000'],
                ],
            ],
            [
                'original_name' => 'struk-terkonfirmasi.png',
                'path' => 'receipts/ui-review/struk-terkonfirmasi.png',
                'status' => ReceiptStatus::Confirmed,
                'merchant' => 'KOPI NAKO',
                'transaction_date' => today()->subDay(),
                'total' => '87450',
                'transaction_id' => $confirmedTransaction->id,
                'ocr_data' => [
                    'raw_text' => "KOPI NAKO\nJun 18, 2023 7:32:42 PM\nKopi Latte 32000\nCroissant 25000\nTOTAL 87,450",
                    'warnings' => [],
                    'suggested_category' => 'Makanan & Minuman',
                    'total_amount' => 87450,
                    'currency' => 'IDR',
                    'transaction_date' => today()->subDay()->toDateString(),
                    'date_status' => 'detected',
                    'parser' => 'receipt-rules-v2',
                ],
                'items' => [
                    ['Kopi Latte', '1', '32000', '32000'],
                    ['Croissant', '1', '25000', '25000'],
                ],
            ],
            [
                'original_name' => 'struk-gagal.png',
                'path' => 'receipts/ui-review/struk-gagal.png',
                'status' => ReceiptStatus::Failed,
                'merchant' => null,
                'transaction_date' => today()->subDays(2),
                'total' => null,
                'transaction_id' => null,
                'ocr_data' => ['raw_text' => '', 'warnings' => [], 'error' => 'OCR_SERVICE_TOKEN belum dikonfigurasi.'],
                'items' => [],
            ],
        ];

        foreach ($receipts as $definition) {
            if (Storage::disk('local')->exists($source)) {
                Storage::disk('local')->makeDirectory(dirname($definition['path']));
                Storage::disk('local')->put($definition['path'], Storage::disk('local')->get($source));
            }

            $receipt = Receipt::firstOrCreate(
                ['user_id' => $user->id, 'original_name' => $definition['original_name']],
                [
                    'user_id' => $user->id,
                    'transaction_id' => $definition['transaction_id'],
                    'path' => $definition['path'],
                    'mime_type' => 'image/png',
                    'size_in_bytes' => Storage::disk('local')->exists($definition['path'])
                        ? Storage::disk('local')->size($definition['path'])
                        : 0,
                    'status' => $definition['status'],
                    'merchant' => $definition['merchant'],
                    'transaction_date' => $definition['transaction_date'],
                    'total' => $definition['total'],
                    'ocr_data' => $definition['ocr_data'],
                ]
            );

            $receipt->update([
                'transaction_id' => $definition['transaction_id'],
                'path' => $definition['path'],
                'status' => $definition['status'],
                'merchant' => $definition['merchant'],
                'transaction_date' => $definition['transaction_date'],
                'total' => $definition['total'],
                'ocr_data' => $definition['ocr_data'],
            ]);

            foreach ($definition['items'] as [$name, $quantity, $unitPrice, $subtotal]) {
                ReceiptItem::firstOrCreate(
                    ['receipt_id' => $receipt->id, 'name' => $name],
                    ['quantity' => $quantity, 'unit_price' => $unitPrice, 'subtotal' => $subtotal]
                );
            }
        }
    }
}
