<?php

use App\Enums\ContactBudget;
use App\Enums\ContactType;
use App\ViewModels\ContactViewModel;

it('provides the existing page SEO metadata', function () {
    $data = app(ContactViewModel::class)
        ->data();

    expect($data)->toHaveKeys(['contactTypeOptions', 'contactBudgetOptions', 'defaultContactType', 'seoSource'])
        ->and($data['contactTypeOptions'])
        ->toBe([
            ContactType::Freelance->value => 'Freelance Project',
            ContactType::Consulting->value => 'Consulting / Code Review',
            ContactType::Modernization->value => 'Legacy Modernization',
            ContactType::Collaboration->value => 'Collaboration',
            ContactType::Other->value => 'Just Saying Hi',
        ])
        ->and($data['contactBudgetOptions'])
        ->toBe([
            ContactBudget::Small->value => 'Under $5,000',
            ContactBudget::Medium->value => '$5,000 to $15,000',
            ContactBudget::Large->value => '$15,000 to $50,000',
            ContactBudget::Enterprise->value => '$50,000+',
        ])
        ->and($data['defaultContactType'])
        ->toBe(ContactType::Freelance->value)
        ->and($data['selectedProject'])
        ->toBeNull()
        ->and($data['seoSource']->title)
        ->toBe('Contact')
        ->and($data['seoSource']->description)
        ->toBe('Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.');
});
