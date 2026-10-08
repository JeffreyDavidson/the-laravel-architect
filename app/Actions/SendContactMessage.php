<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ContactMessageData;
use App\Jobs\SendContactInquiryEmails;
use App\Models\ContactInquiry;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use LogicException;

final class SendContactMessage
{
    public function handle(ContactMessageData $data): void
    {
        $queue = Queue::connection('database');

        if (! $queue instanceof DatabaseQueue || $queue->getDatabase() !== DB::connection()) {
            throw new LogicException('Contact notifications must share the application database.');
        }

        // The inquiry and its email job share one transaction. Deferring the job
        // insert until after commit could leave an inquiry that is never emailed.
        DB::transaction(function () use ($data): void {
            $inquiry = ContactInquiry::create([
                'name' => $data->name,
                'email' => $data->email,
                'type' => $data->type->value,
                'budget' => $data->budget?->value,
                'message' => $data->message,
                'project_title' => $data->projectTitle,
            ]);

            dispatch(new SendContactInquiryEmails($inquiry->id))
                ->beforeCommit();
        });
    }
}
