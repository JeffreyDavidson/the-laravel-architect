<?php

use App\Enums\SocialPlatform;
use App\Models\ContactInquiry;
use App\Models\Project;
use App\Models\SocialProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use JeffreyDavidson\CreatorKit\Jobs\SendContactInquiryEmails;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\SocialProfileFixtures;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config()->set([
        'creator-kit.turnstile.site_key' => 'test-site-key',
        'creator-kit.turnstile.secret_key' => 'test-secret-key',
        'creator-kit.turnstile.siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'creator-kit.turnstile.contact_action' => 'contact-form',
        'creator-kit.turnstile.allowed_hostnames' => ['thelaravelarchitect.com', 'www.thelaravelarchitect.com'],
    ]);

    Mail::fake();
});

it('renders the Turnstile widget on the contact page', function () {
    $response = get(route('contact.create'));

    $response->assertOk()
        ->assertSeeHtml('data-turnstile-widget')
        ->assertSeeHtml('data-sitekey="test-site-key"')
        ->assertSeeHtml('data-action="contact-form"')
        ->assertSeeHtml('class="absolute -top-[9999px] -left-[9999px]"')
        ->assertDontSeeHtml('position:absolute;left:-9999px;top:-9999px;')
        ->assertSeeHtml('JavaScript is required to complete the verification.');

    $content = $response->getContent();
    if (! is_string($content)) {
        throw new RuntimeException('Expected contact form HTML.');
    }

    expect(substr_count($content, 'https://challenges.cloudflare.com/turnstile/v0/api.js'))
        ->toBe(0);
});

it('keeps a published project selected on the contact page', function () {
    $project = Project::factory()->published()
        ->create();

    get(route('contact.create', ['project' => $project->slug]))
        ->assertOk()
        ->assertSee('Project inquiry')
        ->assertSee($project->title)
        ->assertSeeHtml('name="project" value="'.$project->slug.'"');
});

it('shows the contact form without a project for an unusable project context', function (array $query) {
    Project::factory()->create(['slug' => 'draft-project']);

    get(route('contact.create', $query))
        ->assertOk()
        ->assertDontSee('Project inquiry')
        ->assertDontSeeHtml('name="project"');
})->with([
    'unknown slug' => [['project' => 'missing-project']],
    'draft project' => [['project' => 'draft-project']],
    'array' => [['project' => ['draft-project']]],
    'over-long' => [['project' => str_repeat('a', 300)]],
    'blank' => [['project' => '   ']],
]);

it('rejects a draft project context on contact submissions', function () {
    $project = Project::factory()->create();

    post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'project' => $project->slug,
        'message' => 'Can you help with an audit?',
    ])->assertSessionHasErrors('project');

    assertDatabaseCount('jobs', 0);
});

it('silently accepts honeypot submissions without sending mail', function () {
    post(route('contact.store'), [
        'name' => 'Spam Bot',
        'email' => 'spam@example.com',
        'type' => 'freelance',
        'budget' => 'small',
        'message' => 'This should not send.',
        'website' => 'filled-by-bot',
    ])->assertSessionHas('success');

    assertDatabaseCount('jobs', 0);
    expect(ContactInquiry::query()->count())->toBe(0);
    Http::assertNothingSent();
});

it('saves the inquiry and queues its emails after a valid submission', function () {
    $project = Project::factory()->published()
        ->create(['title' => 'Inquiry project']);

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'thelaravelarchitect.com',
        ]),
    ]);

    post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'project' => $project->slug,
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'valid-token',
    ])->assertSessionHas('success');

    $inquiry = ContactInquiry::query()->sole();
    expect($inquiry)
        ->name->toBe('Jane Doe')
        ->email->toBe('jane@example.com')
        ->message->toBe('Can you help with an audit?')
        ->project_title->toBe('Inquiry project');
    assertDatabaseCount('jobs', 1);
    expect(DB::table('jobs')->value('payload'))
        ->toContain(addslashes(SendContactInquiryEmails::class));
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
        && $request['secret'] === 'test-secret-key'
        && $request['response'] === 'valid-token'
        && $request['remoteip'] === '127.0.0.1');
});

it('rejects a contact submission when Turnstile verification fails', function () {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
    ]);

    $response = post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'invalid-token',
    ]);

    $response
        ->assertSessionHasErrors('cf-turnstile-response')
        ->assertSessionHasInput('name', 'Jane Doe');

    expect(session()->getOldInput('cf-turnstile-response'))
        ->toBeNull();
    assertDatabaseCount('jobs', 0);
    expect(ContactInquiry::query()->count())->toBe(0);
});

it('rejects Turnstile responses with invalid request context', function (array $turnstileResponse) {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response($turnstileResponse),
    ]);

    post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'valid-token',
    ])->assertSessionHasErrors('cf-turnstile-response');

    assertDatabaseCount('jobs', 0);
})->with([
    'wrong hostname' => [[
        'success' => true,
        'action' => 'contact-form',
        'hostname' => 'attacker.example',
    ]],
    'missing hostname' => [[
        'success' => true,
        'action' => 'contact-form',
    ]],
    'wrong action' => [[
        'success' => true,
        'action' => 'newsletter',
        'hostname' => 'thelaravelarchitect.com',
    ]],
    'missing action' => [[
        'success' => true,
        'hostname' => 'thelaravelarchitect.com',
    ]],
]);

it('fails closed when the Turnstile secret is missing', function () {
    config()->set('creator-kit.turnstile.secret_key');

    post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'valid-token',
    ])->assertSessionHasErrors('cf-turnstile-response');

    Http::assertNothingSent();
    assertDatabaseCount('jobs', 0);
});

it('fails closed when Turnstile cannot be reached', function () {
    Http::fake(fn () => throw new ConnectionException('Turnstile unavailable.'));

    post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'valid-token',
    ])->assertSessionHasErrors('cf-turnstile-response');

    assertDatabaseCount('jobs', 0);
});

it('does not count invalid submissions against the rate limit', function () {
    from(route('contact.create'))
        ->post(route('contact.store'), [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'type' => 'consulting',
            'budget' => 'medium',
            'message' => '',
        ])
        ->assertRedirect(route('contact.create'))
        ->assertSessionHasErrors(['email', 'message']);

    expect(session()->getOldInput())->toMatchArray([
        'name' => 'Jane Doe',
        'email' => 'not-an-email',
        'type' => 'consulting',
        'budget' => 'medium',
    ]);
    assertDatabaseCount('jobs', 0);
    Http::assertNothingSent();
});

it('renders preserved values and accessible validation feedback', function () {
    from(route('contact.create'))
        ->post(route('contact.store'), [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'type' => 'modernization',
            'budget' => 'large',
            'message' => '',
        ]);

    get(route('contact.create'))
        ->assertOk()
        ->assertSee('Please review the highlighted fields.')
        ->assertSeeHtml('value="Jane Doe"')
        ->assertSeeHtmlInOrder(['value="modernization"', 'selected'])
        ->assertSeeHtmlInOrder(['value="large"', 'selected'])
        ->assertSeeHtml('aria-invalid="true" aria-describedby="email-error"')
        ->assertSeeHtml('href="#email"')
        ->assertSeeHtml('id="email-error"');
});

/**
 * Post a contact submission that passes validation, from the given IP address.
 *
 * @param  array<string, string>  $overrides
 * @return TestResponse<Response>
 */
function submitContactForm(array $overrides = [], string $ipAddress = '127.0.0.1'): TestResponse
{
    return from(route('contact.create'))
        ->withServerVariables(['REMOTE_ADDR' => $ipAddress])
        ->post(route('contact.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'message' => 'Can you help with an audit?',
            'website' => '',
            'cf-turnstile-response' => 'valid-token',
            ...$overrides,
        ]);
}

/** Fake Turnstile verification results, one per request, in order. */
function fakeTurnstileVerification(bool ...$results): void
{
    $sequence = Http::sequence();

    foreach ($results as $passes) {
        $sequence->push([
            'success' => $passes,
            'action' => 'contact-form',
            'hostname' => 'thelaravelarchitect.com',
        ]);
    }

    Http::fake(['https://challenges.cloudflare.com/turnstile/v0/siteverify' => $sequence]);
}

it('rate limits repeated contact submissions by ip address', function () {
    fakeTurnstileVerification(true, true, true, true);

    foreach (range(1, 3) as $attempt) {
        submitContactForm()
            ->assertSessionHas('success');
    }

    $response = submitContactForm();

    $response
        ->assertRedirect(route('contact.create'))
        ->assertSessionHasErrors(['message' => 'Too many submissions. Please try again later.'])
        ->assertSessionHasInput('name', 'Jane Doe');
    expect(session()->getOldInput())
        ->not->toHaveKeys(['website', 'cf-turnstile-response'])
        ->and(ContactInquiry::query()->count())
        ->toBe(3);
});

it('does not count rejected or honeypot submissions against the rate limit', function (string $field, string $value, bool $turnstilePasses) {
    fakeTurnstileVerification(...[...array_fill(0, 4, $turnstilePasses), true]);

    foreach (range(1, 4) as $attempt) {
        submitContactForm([$field => $value]);
    }

    submitContactForm()
        ->assertSessionHas('success')
        ->assertSessionHasNoErrors();
    expect(ContactInquiry::query()->count())
        ->toBe(1);
})->with([
    'validation errors' => ['email', 'not-an-email', true],
    'failed Turnstile' => ['email', 'jane@example.com', false],
    'honeypot' => ['website', 'filled-by-bot', true],
]);

it('limits every contact attempt from one address to 10 a minute, before Turnstile is called', function () {
    fakeTurnstileVerification(...array_fill(0, 11, false));
    foreach (range(1, 10) as $attempt) {
        submitContactForm(['cf-turnstile-response' => 'junk-token'])
            ->assertSessionHasErrors('cf-turnstile-response');
    }

    $response = submitContactForm(['cf-turnstile-response' => 'junk-token']);

    $response->assertTooManyRequests();
    Http::assertSentCount(10);
});

it('limits each ip address separately', function () {
    fakeTurnstileVerification(true, true, true, true);

    foreach (range(1, 3) as $attempt) {
        submitContactForm();
    }

    submitContactForm(ipAddress: '203.0.113.10')
        ->assertSessionHas('success')
        ->assertSessionHasNoErrors();
});

it('names the contact routes by the shared contract without changing the URL', function (string $name) {
    expect(route($name, absolute: false))
        ->toBe('/contact');
})->with([
    'contact.create',
    'contact.store',
]);

it('renders enabled contact and footer social profiles but not disabled ones', function () {
    SocialProfile::query()->delete();

    $footerProfile = SocialProfileFixtures::create(SocialPlatform::GitHub, 'https://github.com/footer-only', true, false);
    $contactProfile = SocialProfileFixtures::create(SocialPlatform::LinkedIn, 'https://linkedin.com/in/contact-only', false, true);
    SocialProfileFixtures::create(SocialPlatform::Bluesky, 'https://bsky.app/profile/disabled', true, true, false);

    get(route('contact.create'))
        ->assertSeeHtml($contactProfile->url)
        ->assertSeeHtml($footerProfile->url)
        ->assertDontSeeHtml('https://bsky.app/profile/disabled');
});

it('escapes a social profile display label on the contact page', function () {
    SocialProfile::query()->delete();

    SocialProfileFixtures::create(
        SocialPlatform::LinkedIn,
        'https://linkedin.com/in/safe-profile',
        false,
        true,
        true,
        '<img src=x onerror=alert(1)>',
    );

    get(route('contact.create'))
        ->assertSee('<img src=x onerror=alert(1)>')
        ->assertDontSeeHtml('<img src=x onerror=alert(1)>');
});
