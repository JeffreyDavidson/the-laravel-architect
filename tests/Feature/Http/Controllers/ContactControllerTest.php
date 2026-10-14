<?php

use App\Enums\SocialPlatform;
use App\Models\ContactInquiry;
use App\Models\Post;
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
        'services.turnstile.site_key' => 'test-site-key',
        'services.turnstile.secret_key' => 'test-secret-key',
        'services.turnstile.siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'services.turnstile.contact_action' => 'contact-form',
        'services.turnstile.allowed_hostnames' => ['thelaravelarchitect.com', 'www.thelaravelarchitect.com'],
    ]);
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

describe('create', function () {
    it('renders the Turnstile widget on the contact page', function () {
        Mail::fake();

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
        Mail::fake();

        $project = Project::factory()->published()
            ->create();

        $response = get(route('contact.create', ['project' => $project->slug]));

        $response
            ->assertOk()
            ->assertSee('Project inquiry')
            ->assertSee($project->title)
            ->assertSeeHtml('name="project" value="'.$project->slug.'"');
    });

    it('shows the contact form without a project for an unusable project context', function (array $query) {
        Mail::fake();

        Project::factory()->create(['slug' => 'draft-project']);

        $response = get(route('contact.create', $query));

        $response
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

    it('names the contact routes by the shared contract without changing the URL', function (string $name) {
        Mail::fake();

        expect(route($name, absolute: false))
            ->toBe('/contact');
    })->with([
        'contact.create',
        'contact.store',
    ]);

    it('renders enabled contact and footer social profiles but not disabled ones', function () {
        Mail::fake();

        SocialProfile::query()->delete();

        $footerProfile = SocialProfileFixtures::create(SocialPlatform::GitHub, 'https://github.com/footer-only', true, false);
        $contactProfile = SocialProfileFixtures::create(SocialPlatform::LinkedIn, 'https://linkedin.com/in/contact-only', false, true);
        SocialProfileFixtures::create(SocialPlatform::Bluesky, 'https://bsky.app/profile/disabled', true, true, false);

        $response = get(route('contact.create'));

        $response
            ->assertSeeHtml($contactProfile->url)
            ->assertSeeHtml($footerProfile->url)
            ->assertDontSeeHtml('https://bsky.app/profile/disabled');
    });

    it('escapes a social profile display label on the contact page', function () {
        Mail::fake();

        SocialProfile::query()->delete();

        SocialProfileFixtures::create(
            SocialPlatform::LinkedIn,
            'https://linkedin.com/in/safe-profile',
            false,
            true,
            true,
            '<img src=x onerror=alert(1)>',
        );

        $response = get(route('contact.create'));

        $response
            ->assertSee('<img src=x onerror=alert(1)>')
            ->assertDontSeeHtml('<img src=x onerror=alert(1)>');
    });

    it('links the privacy notice from public collection points', function () {
        $privacyUrl = route('privacy');

        $response = get(route('contact.create'));

        $response
            ->assertOk()
            ->assertSeeHtml($privacyUrl)
            ->assertSee('Your details are used to reply to this inquiry.');
    });
});

describe('store', function () {
    it('rejects a draft project context on contact submissions', function () {
        Mail::fake();

        $project = Project::factory()->create();

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'project' => $project->slug,
            'message' => 'Can you help with an audit?',
        ];

        $response = post(route('contact.store'), $payload);

        $response->assertSessionHasErrors('project');

        assertDatabaseCount('jobs', 0);
    });

    it('silently accepts honeypot submissions without sending mail', function () {
        Mail::fake();

        $payload = [
            'name' => 'Spam Bot',
            'email' => 'spam@example.com',
            'type' => 'freelance',
            'budget' => 'small',
            'message' => 'This should not send.',
            'website' => 'filled-by-bot',
        ];

        $response = post(route('contact.store'), $payload);

        $response->assertSessionHas('success');

        assertDatabaseCount('jobs', 0);
        expect(ContactInquiry::query()->count())->toBe(0);
        Http::assertNothingSent();
    });

    it('saves the inquiry and queues its emails after a valid submission', function () {
        Mail::fake();

        $project = Project::factory()->published()
            ->create(['title' => 'Inquiry project']);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'action' => 'contact-form',
                'hostname' => 'thelaravelarchitect.com',
            ]),
        ]);

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'project' => $project->slug,
            'message' => 'Can you help with an audit?',
            'cf-turnstile-response' => 'valid-token',
        ];

        $response = post(route('contact.store'), $payload);

        $response->assertSessionHas('success');

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
        Mail::fake();

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
        ]);

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'message' => 'Can you help with an audit?',
            'cf-turnstile-response' => 'invalid-token',
        ];

        $response = post(route('contact.store'), $payload);

        $response
            ->assertSessionHasErrors('cf-turnstile-response')
            ->assertSessionHasInput('name', 'Jane Doe');

        expect(session()->getOldInput('cf-turnstile-response'))
            ->toBeNull();
        assertDatabaseCount('jobs', 0);
        expect(ContactInquiry::query()->count())->toBe(0);
    });

    it('rejects Turnstile responses with invalid request context', function (array $turnstileResponse) {
        Mail::fake();

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response($turnstileResponse),
        ]);

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'message' => 'Can you help with an audit?',
            'cf-turnstile-response' => 'valid-token',
        ];

        $response = post(route('contact.store'), $payload);

        $response->assertSessionHasErrors('cf-turnstile-response');

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
        Mail::fake();

        config()->set('services.turnstile.secret_key');

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'message' => 'Can you help with an audit?',
            'cf-turnstile-response' => 'valid-token',
        ];

        $response = post(route('contact.store'), $payload);

        $response->assertSessionHasErrors('cf-turnstile-response');

        Http::assertNothingSent();
        assertDatabaseCount('jobs', 0);
    });

    it('fails closed when Turnstile cannot be reached', function () {
        Mail::fake();

        Http::fake(fn () => throw new ConnectionException('Turnstile unavailable.'));

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'message' => 'Can you help with an audit?',
            'cf-turnstile-response' => 'valid-token',
        ];

        $response = post(route('contact.store'), $payload);

        $response->assertSessionHasErrors('cf-turnstile-response');

        assertDatabaseCount('jobs', 0);
    });

    it('does not count invalid submissions against the rate limit', function () {
        Mail::fake();

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'type' => 'consulting',
            'budget' => 'medium',
            'message' => '',
        ];

        $response = from(route('contact.create'))
            ->post(route('contact.store'), $payload);

        $response
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
        Mail::fake();

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'type' => 'modernization',
            'budget' => 'large',
            'message' => '',
        ];

        from(route('contact.create'))
            ->post(route('contact.store'), $payload);

        $response = get(route('contact.create'));

        $response
            ->assertOk()
            ->assertSee('Please review the highlighted fields.')
            ->assertSeeHtml('value="Jane Doe"')
            ->assertSeeHtmlInOrder(['value="modernization"', 'selected'])
            ->assertSeeHtmlInOrder(['value="large"', 'selected'])
            ->assertSeeHtml('aria-invalid="true" aria-describedby="email-error"')
            ->assertSeeHtml('href="#email"')
            ->assertSeeHtml('id="email-error"');
    });

    it('rate limits repeated contact submissions by ip address', function () {
        Mail::fake();

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
        Mail::fake();

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
        Mail::fake();

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
        Mail::fake();

        fakeTurnstileVerification(true, true, true, true);

        foreach (range(1, 3) as $attempt) {
            submitContactForm();
        }

        submitContactForm(ipAddress: '203.0.113.10')
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();
    });

    /** @param array<string, mixed> $overrides */
    it('rejects invalid contact fields before verification or persistence', function (array $overrides, string $field, string $error) {
        Mail::fake();
        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'budget' => 'medium',
            'message' => 'Can you help with an audit?',
            'cf-turnstile-response' => 'unspent-verification-token',
            ...$overrides,
        ];

        $response = from(route('contact.create'))
            ->post(route('contact.store'), $payload);

        $response->assertRedirect(route('contact.create'))
            ->assertSessionHasErrors([$field => $error]);

        foreach (['name', 'email', 'type', 'budget', 'message'] as $input) {
            $response->assertSessionHasInput($input, fn (mixed $value): bool => $value === $payload[$input]);
        }

        assertDatabaseCount('contact_inquiries', 0);
        assertDatabaseCount('jobs', 0);
        Http::assertNothingSent();
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    })->with([
        'missing name' => [['name' => null], 'name', 'The name field is required.'],
        'missing email' => [['email' => null], 'email', 'The email field is required.'],
        'missing type' => [['type' => null], 'type', 'The type field is required.'],
        'missing message' => [['message' => null], 'message', 'The message field is required.'],
        'array name' => [['name' => ['Jane']], 'name', 'The name field must be a string.'],
        'array email' => [['email' => ['jane@example.com']], 'email', 'The email field must be a valid email address.'],
        'array message' => [['message' => ['An audit']], 'message', 'The message field must be a string.'],
        'array type' => [['type' => ['consulting']], 'type', 'The type field must be a string.'],
        'array budget' => [['budget' => ['medium']], 'budget', 'The budget field must be a string.'],
        'array project' => [['project' => ['a-project']], 'project', 'The project field must be a string.'],
        'oversized name' => [['name' => str_repeat('n', 256)], 'name', 'The name field must not be greater than 255 characters.'],
        'oversized email' => [['email' => str_repeat('e', 250).'@example.com'], 'email', 'The email field must not be greater than 255 characters.'],
        'oversized message' => [['message' => str_repeat('m', 5001)], 'message', 'The message field must not be greater than 5000 characters.'],
        'oversized project' => [['project' => str_repeat('p', 256)], 'project', 'The project field must not be greater than 255 characters.'],
        'invalid email' => [['email' => 'not-an-email'], 'email', 'The email field must be a valid email address.'],
        'invalid type' => [['type' => 'invalid'], 'type', 'The selected type is invalid.'],
        'invalid budget' => [['budget' => 'invalid'], 'budget', 'The selected budget is invalid.'],
    ]);

    it('accepts contact field length boundaries without optional selections', function () {
        Mail::fake();

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'action' => 'contact-form',
                'hostname' => 'thelaravelarchitect.com',
            ]),
        ]);
        $payload = [
            'name' => str_repeat('n', 255),
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'budget' => null,
            'project' => null,
            'message' => str_repeat('m', 5000),
            'cf-turnstile-response' => 'valid-token',
        ];

        $response = from(route('contact.create'))
            ->post(route('contact.store'), $payload);

        $response->assertRedirect(route('contact.create'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $inquiry = ContactInquiry::query()->sole();

        expect($inquiry)->name->toBe($payload['name'])
            ->email->toBe($payload['email'])
            ->message->toBe($payload['message'])
            ->budget->toBeNull()
            ->project_title->toBeNull();
        assertDatabaseCount('jobs', 1);
        expect(DB::table('jobs')->value('payload'))
            ->toContain(addslashes(SendContactInquiryEmails::class));
        Http::assertSentCount(1);
    });

    it('answers a malformed Turnstile token with the verification error, not a server error', function (mixed $token) {
        config()->set([
            'services.turnstile.secret_key' => 'test-secret',
            'services.turnstile.siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            'services.turnstile.contact_action' => 'contact-form',
            'services.turnstile.allowed_hostnames' => ['thelaravelarchitect.com'],
        ]);
        Http::fake();

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'message' => 'Can you help with an audit?',
            'website' => '',
            'cf-turnstile-response' => $token,
        ];

        $response = from(route('contact.create'))
            ->post(route('contact.store'), $payload);

        $response
            ->assertRedirect(route('contact.create'))
            ->assertSessionHasErrors('cf-turnstile-response');
        Http::assertNothingSent();
        expect(ContactInquiry::query()->count())
            ->toBe(0);
    })->with([
        'array' => [['unexpected']],
        'nested array' => [[['unexpected']]],
        'number' => [123],
    ]);
});
