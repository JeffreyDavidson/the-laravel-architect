<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\call;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

pest()->use(RefreshDatabase::class);

describe('create', function () {
    it('shows an explicit confirmation step without changing subscriber state', function () {
        Mail::fake();

        $token = 'valid-confirmation-token';
        $subscriber = Subscriber::factory()->pending()
            ->create(['verification_token_hash' => hash('sha256', $token)]);

        $url = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => $token],
        );

        $email = $subscriber->email;

        $response = get($url);

        $response
            ->assertOk()
            ->assertSee('Confirm your subscription')
            ->assertSee($email)
            ->assertSeeHtml('<meta name="robots" content="noindex, nofollow">');

        $subscriber->refresh();
        $tokenHash = $subscriber->verification_token_hash;
        expect($subscriber->verified_at)
            ->toBeNull()
            ->and($tokenHash)
            ->not
            ->toBeNull();
    });

    it('keeps the confirmation page out of every cache, including the back-forward cache', function () {
        Mail::fake();

        $token = 'valid-confirmation-token';
        $subscriber = Subscriber::factory()->pending()
            ->create(['verification_token_hash' => hash('sha256', $token)]);
        $url = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => $token],
        );

        $response = get($url);

        $response->assertHeader('Cache-Control', 'no-store, private');
    });

    it('renders the confirmation page in its confirming state with a no-script button inside the signed form', function () {
        Mail::fake();

        $token = 'valid-confirmation-token';
        $subscriber = Subscriber::factory()->pending()
            ->create([
                'email' => 'reader@example.com',
                'verification_token_hash' => hash('sha256', $token),
            ]);
        $url = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => $token],
        );

        $escapedUrl = e($url);

        $response = get($url);

        $response->assertOk()
            ->assertSeeHtmlInOrder([
                '<h1',
                'Confirming your subscription…',
                '</h1>',
                '<form',
                "action=\"{$escapedUrl}\"",
                'method="POST"',
                'name="_token"',
                'One moment.',
                '<noscript>',
                'Confirm that you want newsletter updates sent to reader@example.com.',
                'type="submit"',
                'Confirm subscription',
                '</noscript>',
                '</form>',
            ])
            ->assertDontSeeHtml('>Confirm your subscription</h1>');
    });

    it('titles the confirmation page tab with the confirming state', function () {
        Mail::fake();

        $token = 'valid-confirmation-token';
        $subscriber = Subscriber::factory()->pending()
            ->create(['verification_token_hash' => hash('sha256', $token)]);
        $url = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => $token],
        );

        $response = get($url);

        $response->assertOk()
            ->assertSeeHtml('<title>Confirming Your Subscription')
            ->assertDontSeeHtml('<title>Confirm Your Subscription');
    });

    it('limits confirmation link requests to 10 a minute from one address', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->pending()
            ->create(['verification_token_hash' => hash('sha256', 'valid-token')]);
        $url = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => 'valid-token'],
        );
        foreach (range(1, 10) as $attempt) {
            get($url)
                ->assertOk();
        }

        $response = get($url);

        $response->assertTooManyRequests();
    });
});

describe('store', function () {
    it('confirms a subscriber with an explicit post to a valid signed link and redirects to the confirmed page', function () {
        Mail::fake();

        $token = 'valid-confirmation-token';
        $subscriber = Subscriber::factory()->pending()
            ->create(['verification_token_hash' => hash('sha256', $token)]);

        $url = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => $token],
        );

        $response = post($url);

        $response
            ->assertRedirect(route('newsletter.confirmed'));

        $subscriber->refresh();
        $tokenHash = $subscriber->verification_token_hash;
        expect($subscriber->verified_at)
            ->not
            ->toBeNull()
            ->and($tokenHash)
            ->toBeNull();
    });
});

describe('shared contracts', function () {
    it('sends a confirmation link used a second time back to the signup form with its message', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->pending()
            ->create(['verification_token_hash' => hash('sha256', 'valid-token')]);
        $url = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => 'valid-token'],
        );
        post($url);

        $response = followingRedirects()
            ->get($url);

        $response->assertSeeText('This confirmation link has expired or has already been used. If you already confirmed, you’re subscribed. Otherwise, sign up again below.');
        $subscriber->refresh();
        expect($subscriber->verified_at)
            ->not
            ->toBeNull();
    });

    it('sends an unusable confirmation link back to the signup form without confirming anyone', function (Closure $link, string $method, string $routeName) {
        Mail::fake();

        $subscriber = Subscriber::factory()->pending()
            ->create(['verification_token_hash' => hash('sha256', 'valid-token')]);
        $url = $link($subscriber, $routeName);
        if (! is_string($url)) {
            throw new RuntimeException('The dataset must build a confirmation URL.');
        }

        $response = call($method, $url);

        $response
            ->assertRedirect(route('home').'#newsletter-form')
            ->assertSessionHasErrors(['email' => 'This confirmation link has expired or has already been used. If you already confirmed, you’re subscribed. Otherwise, sign up again below.']);

        $subscriber->refresh();
        $tokenHash = $subscriber->verification_token_hash;
        expect($subscriber->verified_at)
            ->toBeNull()
            ->and($tokenHash)
            ->toBe(hash('sha256', 'valid-token'));
    })->with([
        'expired signature' => [fn (Subscriber $subscriber, string $routeName): string => URL::temporarySignedRoute(
            $routeName,
            now()->subMinute(),
            ['subscriber' => $subscriber, 'token' => 'valid-token'],
        )],
        'tampered signature' => [fn (Subscriber $subscriber, string $routeName): string => str_replace(
            'expires=',
            'expires=1',
            URL::temporarySignedRoute($routeName, now()->addHour(), ['subscriber' => $subscriber, 'token' => 'valid-token']),
        )],
        'unsigned link' => [fn (Subscriber $subscriber, string $routeName): string => route(
            $routeName,
            ['subscriber' => $subscriber, 'token' => 'valid-token'],
        )],
        'unknown token' => [fn (Subscriber $subscriber, string $routeName): string => URL::temporarySignedRoute(
            $routeName,
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => 'unknown-token'],
        )],
        'unknown subscriber' => [fn (Subscriber $subscriber, string $routeName): string => URL::temporarySignedRoute(
            $routeName,
            now()->addHour(),
            ['subscriber' => $subscriber->id + 1, 'token' => 'valid-token'],
        )],
    ])->with([
        'confirmation page' => ['GET', 'newsletter.confirm'],
        'confirmation form' => ['POST', 'newsletter.confirm.store'],
    ]);
});
