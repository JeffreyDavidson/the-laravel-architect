<?php

namespace App\Actions;

use App\Data\ContactMessageData;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactInquiry;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        // The inquiry and encrypted jobs share one transaction. Deferring these
        // inserts until after commit would leave partial submissions on failure.
        DB::transaction(function () use ($data): void {
            ContactInquiry::query()->create([
                'name' => $data->name,
                'email' => $data->email,
                'type' => $data->type->value,
                'budget' => $data->budget?->value,
                'message' => $data->message,
                'project_title' => $data->projectTitle,
            ]);

            Mail::to(config('mail.contact_to', config('mail.from.address')))->queue(new ContactMessageReceived(
                senderName: $data->name,
                senderEmail: $data->email,
                contactType: $data->type->value,
                budget: $data->budget?->value,
                contactMessage: $data->message,
                projectTitle: $data->projectTitle,
            )->onConnection('database')
                ->beforeCommit());
            Mail::to(new Address($data->email, $data->name))->queue(new ContactMessageConfirmation(
                senderName: $data->name,
                contactType: $data->type->value,
                budget: $data->budget?->value,
                contactMessage: $data->message,
                projectTitle: $data->projectTitle,
            )->onConnection('database')
                ->beforeCommit());
        });
    }
}
