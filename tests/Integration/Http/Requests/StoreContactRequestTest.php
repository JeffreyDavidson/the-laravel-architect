<?php

use App\Enums\ContactBudget;
use App\Enums\ContactType;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Support\Facades\Validator;

it('accepts every contact type and optional budget', function (ContactType $type, ?ContactBudget $budget) {
    $validator = Validator::make([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => $type->value,
        'budget' => $budget?->value,
        'message' => 'A project inquiry.',
    ], (new StoreContactRequest)->rules());

    expect($validator->passes())->toBeTrue();
})->with(ContactType::cases())->with([null, ...ContactBudget::cases()]);

it('rejects invalid contact selections', function (string $field, mixed $value) {
    $validator = Validator::make(array_replace([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => ContactType::Freelance->value,
        'budget' => null,
        'message' => 'A project inquiry.',
    ], [$field => $value]), (new StoreContactRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has($field))->toBeTrue();
})->with([
    ['type', 'invalid'],
    ['type', null],
    ['type', ['freelance']],
    ['budget', 'invalid'],
    ['budget', ['small']],
]);

it('maps only validated fields into typed contact data', function (ContactType $type, ?ContactBudget $budget) {
    $request = new StoreContactRequest;
    $request->setValidator(Validator::make([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => $type->value,
        'budget' => $budget?->value,
        'message' => 'A project inquiry.',
        'cf-turnstile-response' => 'private-token',
    ], $request->rules()));

    $data = $request
        ->toData();

    expect($data->name)->toBe('Jane Doe')
        ->and($data->email)->toBe('jane@example.com')
        ->and($data->type)->toBe($type)
        ->and($data->budget)->toBe($budget)
        ->and($data->message)->toBe('A project inquiry.')
        ->and(get_object_vars($data))->toHaveCount(5);
})->with(ContactType::cases())->with([null, ...ContactBudget::cases()]);
