<?php

namespace App\Jobs;

use App\Models\Receipt;
use App\Services\ReceiptService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessReceipt implements ShouldQueue
{
    use Queueable;

    public function __construct(public Receipt $receipt) {}

    public function handle(ReceiptService $service): void
    {
        $service->process($this->receipt);
    }
}
