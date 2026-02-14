<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Create or find the author
        $user = User::firstOrCreate(
            ['email' => 'jeffrey@thelaravelarchitect.com'],
            [
                'name' => 'Jeffrey Davidson',
                'password' => Hash::make('temporary-password-change-me'),
            ]
        );

        // Create categories
        $personal = Category::firstOrCreate(['name' => 'Personal'], ['slug' => 'personal', 'description' => 'Personal stories, reflections, and life updates.']);
        $career = Category::firstOrCreate(['name' => 'Career'], ['slug' => 'career', 'description' => 'Career advice, lessons learned, and professional growth.']);
        $laravel = Category::firstOrCreate(['name' => 'Laravel'], ['slug' => 'laravel', 'description' => 'Laravel tutorials, opinions, and deep dives.']);

        // Post 1
        Post::updateOrCreate(['slug' => 'hello-world-why-im-starting-this-blog'], [
            'title' => "Hello World — Why I'm Starting This Blog",
            'excerpt' => "After 15 years of building things with PHP and Laravel — with two podcasts and a YouTube channel on the way — I figured it was time to start writing things down. Here's why.",
            'content' => self::post1(),
            'category_id' => $personal->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => '2026-01-15 09:00:00',
            'meta_title' => "Hello World — Why I'm Starting This Blog | The Laravel Architect",
            'meta_description' => "Jeffrey Davidson introduces The Laravel Architect blog — who he is, what to expect, and why he's adding a blog to the mix.",
        ]);

        // Post 2
        Post::updateOrCreate(['slug' => 'from-kansas-to-florida-a-developers-journey'], [
            'title' => "From Kansas to Florida: A Developer's Journey",
            'excerpt' => "I grew up in Kansas, discovered the web in the late 2000s, and somehow ended up writing code full-time in Florida. This is that story.",
            'content' => self::post2(),
            'category_id' => $personal->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => '2026-01-22 09:00:00',
            'meta_title' => "From Kansas to Florida: A Developer's Journey | The Laravel Architect",
            'meta_description' => "The personal story behind The Laravel Architect — from Kansas roots to a career in web development and a new life in Florida.",
        ]);

        // Post 3
        Post::updateOrCreate(['slug' => 'what-15-years-of-web-development-taught-me'], [
            'title' => 'What 15 Years of Web Development Taught Me',
            'excerpt' => "Fifteen years is a long time to do anything. Here are the lessons — technical and otherwise — that I wish someone had told me on day one.",
            'content' => self::post3(),
            'category_id' => $career->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => '2026-01-30 09:00:00',
            'meta_title' => 'What 15 Years of Web Development Taught Me | The Laravel Architect',
            'meta_description' => "Hard-won lessons from 15 years of building for the web — from PHP spaghetti to clean architecture, and everything in between.",
        ]);

        // Post 4
        Post::updateOrCreate(['slug' => 'why-i-still-choose-laravel-in-2026'], [
            'title' => 'Why I Still Choose Laravel in 2026',
            'excerpt' => "Every year someone declares PHP dead and Laravel irrelevant. Every year I start another Laravel project. Here's why I'm not switching.",
            'content' => self::post4(),
            'category_id' => $laravel->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => '2026-02-05 09:00:00',
            'meta_title' => 'Why I Still Choose Laravel in 2026 | The Laravel Architect',
            'meta_description' => "A seasoned developer makes the case for Laravel in 2026 — the ecosystem, the philosophy, and why it keeps winning.",
        ]);

        // Post 5
        Post::updateOrCreate(['slug' => 'how-i-structure-every-laravel-project'], [
            'title' => 'How I Structure Every Laravel Project',
            'excerpt' => "After years of iteration, this is the project structure I reach for on every new Laravel app. Actions, services, form requests, and Pest tests — all of it.",
            'content' => self::post5(),
            'category_id' => $laravel->id,
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => '2026-02-12 09:00:00',
            'meta_title' => 'How I Structure Every Laravel Project | The Laravel Architect',
            'meta_description' => "A practical, opinionated guide to structuring Laravel applications — with real code examples, folder conventions, and testing patterns.",
        ]);
    }

    private static function post1(): string
    {
        return <<<'MARKDOWN'
## The Obligatory Introduction

Alright, let's get this out of the way. My name is Jeffrey Davidson. I've been building things on the web for over fifteen years now, mostly with PHP, and for the last decade-plus, almost exclusively with Laravel. I live in Florida with my wife Cassie and our daughter Viola. I'm a Jayhawks fan stuck in Gator country, I play way too much poker, and I'm pretty sure I've been to Walt Disney World more times than some of the cast members.

I'm also launching a YouTube channel and two podcasts — *Coffee with The Laravel Architect* and *Embracing Cloudy Days* — and this site is where it all comes together. Blog posts, video tutorials, and everything in between.

## Why a Blog? Why Now?

Fair question. I've been a developer for over fifteen years, and for most of that time I've been learning from other people's content — blog posts, conference talks, tutorials, podcasts. At some point you stop just consuming and start wanting to contribute back.

I chose to start with writing because it forces clarity. You can't hand-wave in a blog post. You can't say "you know what I mean" and move on. You have to actually organize your thoughts and say what you mean. That's a discipline I think every developer benefits from.

Written content is also the most useful long-term format. It's scannable, searchable, and linkable. You can bookmark a heading and come back to it six months later. Try finding a specific tip buried in a 45-minute video — you'll be scrubbing back and forth for five minutes. A blog post? Ctrl+F.

Video and audio are coming too — I'm genuinely excited about those formats. But the blog felt like the right place to start. Get the ideas down in writing first, then bring them to life on screen and in conversation.

## What You Can Expect

This site is the home base for everything I'm building. Blog posts to start — with video tutorials, live coding sessions, and podcast episodes on the way.

Here's how I see the content mix shaping up:

- **Written posts** for architecture deep dives, opinions, and reference material — the stuff you want to scan, bookmark, and come back to.
- **Video tutorials** for walkthroughs, live coding, and anything where watching someone build it makes more sense than reading about it. Some blog posts will have companion videos, and some videos will have companion write-ups.
- **Podcast episodes** for longer conversations — interviews with other developers, discussions about the industry, and the human side of building software.

What I *will* cover:

- **Architecture and patterns.** How I structure projects, how I think about responsibilities, when to reach for certain patterns and when to keep it simple.
- **Opinions.** I have them. Sometimes they're spicy. I think the Laravel community could benefit from more honest, opinionated writing instead of the same "10 Tips" listicle recycled across forty blogs.
- **The human side of development.** Career stuff. Burnout. What it's like to be a developer and a dad. How I manage my time. The unglamorous reality of freelancing and running a brand.
- **Technical deep dives.** When I learn something interesting or solve a gnarly problem, I want to document it so you don't have to fight the same battle.

I'll try to publish weekly — written, video, or both — but I'm not going to stress about a rigid schedule. Quality over frequency. I'd rather put out one genuinely useful piece a month than four forgettable ones.

## My Philosophy on Teaching

Here's something I feel strongly about: **the best teachers are still practitioners.**

I'm not writing from some ivory tower. I'm actively building Laravel applications. When I tell you how I structure a project, it's because I structured a real project that way last Tuesday. When I recommend Pest over PHPUnit, it's because I actually switched and lived with the decision. When I say something doesn't scale, it's because I watched it not scale.

I also believe in being honest about what I don't know. The tech industry has this weird culture where admitting uncertainty is seen as weakness. I think that's garbage. If I'm not sure about something, I'll tell you. If I changed my mind about a previous take, I'll own it.

## A Bit More About Me

I'm not going to write a whole autobiography here — I'll save some of that for future posts. But a few things that might matter:

I'm a person of faith. I'm an ELCA Lutheran, and while this isn't a religious blog, my faith is part of who I am and it informs how I treat people and approach my work. I believe in grace, for myself and for junior developers who accidentally drop a production database.

I'm also a Type 2 diabetic, which has taught me a lot about discipline, routine, and taking care of yourself when your job involves sitting in a chair for ten hours. I'll probably write about the intersection of health and desk work at some point, because it matters more than most devs realize.

I'm a photographer. I'm a poker player. I'm a dad who will absolutely destroy you in Disney trivia. I contain multitudes.

## Let's Do This

I'm genuinely excited about this. I've spent years consuming content from developers I admire — on podcasts, YouTube, blogs. Now it's my turn to contribute. The YouTube channel and both podcasts are in the works, and I can't wait to share them with you.

This blog is the first piece of the puzzle. Another format, another avenue, another chance to be useful to someone.

If you've made it this far, thanks for reading. Seriously. Subscribe to the RSS feed if that's your thing, follow me on social media if you want the updates, or just bookmark the site and check back when you're bored at work. I won't judge.

Rock Chalk, and welcome to The Laravel Architect.
MARKDOWN;
    }

    private static function post2(): string
    {
        return <<<'MARKDOWN'
## Flat Land, Big Sky

I grew up in Kansas. And look, I know what you're picturing — flat fields stretching to the horizon, wheat, maybe a tornado or two. Fair enough, that *is* a lot of Kansas. But I grew up in the suburbs — strip malls, chain restaurants, and a neighborhood where every house looked like it came from the same three blueprints. Not exactly the dramatic prairie origin story people expect. Still, it was home, and it shaped me more than I realized at the time.

I grew up in a pretty typical Midwestern household. Church on Sundays, high school sports, the kind of town where everybody knows your parents and your parents know everybody. I played baseball, watched the Jayhawks with something approaching religious devotion, and didn't really know what I wanted to do with my life.

Computers were around, sure. I had a desktop in my room by high school, and I spent an embarrassing amount of time on forums and messing around with MySpace layouts. But I didn't look at a computer and think "career." It was just something I did for fun.

## The Accidental Developer

The web development thing happened almost by accident. I was in my late teens, messing around with HTML because I wanted to customize things online. Then someone showed me PHP, and suddenly I could make things *do* stuff. Not just look different — actually work. Forms that sent emails. Pages that pulled data from a database. It felt like magic, and I was hooked.

I started out completely self-taught, not even sure if this was going to be a real career or just a hobby that occasionally paid. After high school, I tried the traditional route — a couple semesters at a community college. But it was table-based layouts and outdated practices, and I knew that wasn't how the modern web worked. I was learning more on my own than I was in class.

So I made the leap and enrolled at Full Sail University, where I earned my Bachelor of Science in Web Design and Development. That experience gave me the structure and foundation I'd been missing as a self-taught developer — proper patterns, real collaboration, and the confidence that this wasn't just a hobby anymore. It was my career.

For the first few years though, I was still writing the kind of PHP that would make current-me break out in hives. Spaghetti code, SQL queries concatenated with user input, `include` files nested six deep. No version control. No tests. No architecture to speak of.

But I was building things, and people were paying me to build things, and I was learning something new every single day. That momentum carried me through the rough patches.

## Finding My Way Through Frameworks

I started writing PHP in 2008, and for the first few years it was all vanilla — no framework, no structure, just raw PHP files doing whatever I needed them to do. It worked, but as projects got bigger, the mess got harder to manage.

That's when I discovered CodeIgniter. It was my first real framework, and it was a revelation. Suddenly I had routing, a templating system, a database abstraction layer. I worked with CodeIgniter for a few years and it taught me the value of structure and convention. But as the PHP ecosystem evolved, I could feel it falling behind.

Then in 2014, I found Laravel — specifically version 4.2 — and it was one of those moments where everything just clicked. Here was this framework that was opinionated in all the right ways, that made PHP feel *modern*, that actually cared about developer experience. Eloquent blew my mind. Blade templates made sense. Artisan commands felt like having a conversation with your framework.

I went all in. Started rebuilding everything in Laravel. Started reading the source code. Started following Taylor Otwell on Twitter and absorbing everything the community was putting out. Within a couple of years, Laravel wasn't just my framework of choice — it was the lens through which I understood web development.

That might sound dramatic, but I think a lot of Laravel developers know what I'm talking about. The framework has a way of teaching you good patterns almost by osmosis. You use Eloquent long enough and you start thinking in terms of relationships. You use service providers and you start understanding dependency injection. Laravel is quietly educational in a way that I think is underappreciated.

## Building a Career

The next several years were a blur of projects, clients, and learning. I did freelance work. I did agency work. I built SaaS products that succeeded and SaaS products that absolutely did not. I learned about deployment, about server management, about the gap between "it works on my machine" and "it works in production."

A big part of my career has been modernization work — taking legacy PHP applications written in other frameworks and rewriting them in the latest version of Laravel. I've migrated codebases from CodeIgniter, ExpressionEngine, Yii2, and CakePHP. Every one of those projects taught me something different about untangling technical debt, understanding someone else's architectural decisions (or lack thereof), and building something clean from the wreckage. It's not glamorous work, but it's made me a significantly better architect. When you've seen every way a codebase can go wrong, you develop strong opinions about how to get it right.

I've also been feeling the pull to create content. For years I've learned from other developers who put themselves out there — on blogs, YouTube, podcasts — and I want to do the same. I remember how confusing everything was when I was starting out, and I want to make it less confusing for the next person.

That's the driving force behind everything I'm building now. *Coffee with The Laravel Architect* is exactly what it sounds like: me drinking coffee and talking about Laravel. *Embracing Cloudy Days* is more personal, more philosophical — less code and more life. And the YouTube channel will bring tutorials and live coding to the mix. None of it has launched yet, but it's all in the works and I couldn't be more excited about it.

## The Move South

Cassie and I got married in July of 2014, and almost immediately started talking about what we wanted our future to look like. By February we'd booked a trip to Orlando to look at apartments. By March, we'd packed up and moved. No years of deliberation, no endless pros-and-cons lists — we just did it. Packed up our Kansas lives and landed in the theme park kingdom.

The reasons were a mix of practical and personal. Weather played a role — Kansas winters are no joke, and the ability to be active outdoors year-round mattered. And being closer to Walt Disney World certainly didn't hurt.

Then in 2017, our daughter Viola came along, and suddenly the move felt even more right. Viola is autistic and nonverbal, and being her dad has changed me in ways I'm still figuring out. It's reshaped my priorities, my patience, and honestly my entire perspective on what matters. Florida has resources and communities that we wanted access to for her, and she lights up in the parks in a way that makes every minute of the drive worth it.

I won't pretend the transition was seamless. Leaving the place you grew up is weird, even when you're excited about where you're going. I missed the people. I missed the familiarity. I even missed the flatness, if you can believe that. But Florida has become home in ways I didn't expect. The developer community down here is solid. The pace of life suits us. And honestly, I don't miss shoveling snow even a little bit.

## Remote Work and Roots

One of the things that made the move possible is that software development is beautifully location-independent. My clients don't care where I am as long as the code ships on time. Future clients and collaborators won't care if I'm working from Kansas or Florida or the surface of the moon. Remote work isn't just a pandemic trend for me — it's been my reality for years, and it's the reason I could pick up and move my family a thousand miles without changing my career.

That said, I think there's something to be said for having roots. Kansas shaped me. The work ethic, the straightforwardness, the "just get it done" mentality — that's pure Midwest. I carry it with me. When I sit down to architect a Laravel application, I'm not trying to be clever. I'm trying to be clear. I'm trying to build something that works, that the next developer can understand, that solves the actual problem without unnecessary complexity.

Maybe that's a Kansas thing. Maybe it's just a me thing. Either way, it's served me well.

## Looking Forward

I'm settled in Florida now. Viola is eight and growing up faster than I'm ready for. Cassie keeps everything running while I stare at code and talk into microphones. The Jayhawks are still my team even though I'm surrounded by SEC fans. I still play poker. I still take too many photos. I still think PHP is a perfectly fine language and I'll die on that hill.

This blog, the podcasts, the YouTube channel — they're all part of the same thing. I want to build stuff, learn stuff, and share what I know. The address changed, but the mission didn't.

Thanks for reading. Next week, I'll get into some actual lessons learned from fifteen years of doing this. Fair warning: I have opinions.
MARKDOWN;
    }

    private static function post3(): string
    {
        return <<<'MARKDOWN'
## The Long View

Fifteen years. I've been thinking about that number a lot lately. When I started building websites, jQuery was revolutionary, responsive design wasn't really a thing yet, and deploying meant FTPing files to a shared hosting account. I've watched frameworks rise and fall. I've seen PHP go from the punching bag of the internet to a genuinely excellent modern language. I've written code I'm proud of and code that should probably be classified as a war crime.

Here's what I've learned. Not just the technical stuff — though there's plenty of that — but the broader lessons about building a career in this weird, wonderful, constantly-shifting industry.

## 1. The Fundamentals Don't Change

Languages come and go. Frameworks come and go. But the fundamentals — data structures, HTTP, SQL, how the request/response cycle works, basic security principles — those are essentially permanent. I've seen developers who could spin up a React app in five minutes but couldn't explain what a POST request actually does. That's building on sand.

Early in my career, I skipped over fundamentals to chase whatever was shiny. I learned Laravel without really understanding MVC. I used Eloquent without knowing SQL. It caught up with me. Every time. When things broke, I didn't have the mental model to debug them. I was just pattern-matching against Stack Overflow answers.

If I could go back and tell 2011 Jeffrey one thing, it would be: slow down, learn how the underlying technology works, *then* learn the framework. You'll be faster in the long run.

## 2. Boring Technology Is Usually the Right Choice

There's a famous blog post called "Choose Boring Technology" by Dan McKinley. If you haven't read it, go read it. I'll wait.

The gist is that you have a limited budget for novelty. Every new, unproven technology you adopt comes with a cost — unknown failure modes, missing documentation, smaller community. Sometimes that cost is worth it. Usually it isn't.

I've been burned by choosing the exciting option more times than I care to admit. Exotic databases, bleeding-edge JavaScript frameworks, fancy deployment tools that sounded great in a conference talk and turned into maintenance nightmares in production. Meanwhile, the boring apps — MySQL, Laravel, a straightforward VPS — just kept chugging along.

This doesn't mean never try new things. It means be honest about *why* you're trying them. Is it because the project actually needs this technology? Or is it because you're bored and want to play with something new? Those are different motivations, and they lead to different outcomes.

## 3. Testing Is Not Optional (But It Took Me Years to Believe That)

I didn't write tests for the first several years of my career. I told myself the usual excuses: the project is too small, the deadline is too tight, I'll add tests later. "Later" never came.

The turning point was Adam Wathan's *Test Driven Laravel* course. I'd heard people talk about testing for years, but it always felt like extra work with no clear payoff. Then I watched Adam build a real application test-first, and something clicked. It wasn't about writing tests *after* the fact to check boxes — it was about using tests to *drive* the design. The code that came out of that process was cleaner, more intentional, and easier to change. I was sold.

I started writing tests the next day. First with PHPUnit, which felt clunky and verbose. Then Pest came along and made testing feel like it actually belonged in my workflow. Pest's syntax is clean, expressive, and — this matters — *enjoyable*. I actually look forward to writing tests now, which is something I never thought I'd say.

If you're not testing, you're not being professional. I know that sounds harsh. I don't care. Write the test. Future you will send a thank-you card.

## 4. Code Is Communication

This took a long time to sink in. When you're junior, you think code is instructions for the computer. It is, technically. But more importantly, **code is communication between humans.** The computer doesn't care if your variable is called `$x` or `$customerEmailVerificationStatus`. The next developer reading your code at 11 PM on a Friday absolutely cares.

I obsess over naming now. I refactor for clarity more than for performance. I write code that tells a story — you should be able to read a controller action and understand the business process without jumping to six different files.

This is also why I've moved toward patterns like Action classes and descriptive method names. `CreateSubscription::handle($user, $plan)` reads like a sentence. It tells you exactly what's happening. That's not an accident — that's intentional communication.

## 5. Your Architecture Should Earn Its Complexity

I've gone through phases. Early on, everything was simple because I didn't know any better. Then I discovered design patterns and made everything absurdly complex — repositories wrapping repositories, interfaces for everything, abstraction layers that abstracted nothing. Then I swung back to simplicity, but this time *intentionally*.

Here's my rule now: start simple. Use Laravel's built-in patterns — controllers, models, form requests, policies. When a specific part of your app starts getting messy, *then* introduce more structure. Extract a service class. Create an action. Add a DTO. But do it because you have a real problem, not because you read a blog post (irony noted) about hexagonal architecture and got inspired.

Premature abstraction is just as dangerous as premature optimization. Possibly more so, because it makes your code harder to understand without making it any more correct.

## 6. Soft Skills Are Not Soft

I hate the term "soft skills" because it implies they're less important than technical skills. They're not. They might be more important.

The ability to communicate clearly, to listen, to manage expectations, to say "I don't know" without shame, to give and receive feedback, to write a coherent email — these are career-defining skills. I've watched brilliant developers stall out because they couldn't work on a team. I've watched average developers advance because they were great communicators who made everyone around them more effective.

If you want to level up your career and you're already technically competent, invest in communication. Take a writing course. Practice presenting. Learn to explain complex things simply. That's where the leverage is.

## 7. Take Care of Your Body

This one's personal, but I think it matters. For years, I treated my body like it was just a vehicle for getting my brain to the keyboard. Sitting in a chair for 10+ hours a day. Not exercising. Eating garbage. I told myself I'd deal with it later, that the work was more important.

Your career is a marathon, not a sprint. If you burn out physically, all the technical skill in the world won't help you. Get a standing desk. Take walks. Drink water. Get your blood work done. This stuff isn't sexy, but neither is a health crisis at 40.

## 8. The Community Matters More Than the Technology

I've stayed with Laravel for a lot of reasons — the elegant syntax, the ecosystem, the constant innovation. But if I'm being really honest, the biggest reason is the community. The Laravel community is, by and large, welcoming, generous, and genuinely helpful. Laracasts. Laracon. The Discord servers and forums. The package authors who maintain open-source tools for free because they believe in the ecosystem.

When you choose a technology, you're also choosing a community. You're choosing who you'll learn from, who you'll ask for help, who you'll collaborate with. Choose a community that makes you better.

## 9. Ship It

Perfectionism is a career killer. I've sat on projects for months, tweaking and refining, waiting until everything was "ready." Meanwhile, someone else shipped something half as polished and learned twice as much from real-world feedback.

Ship it. Get it in front of users. Learn from what actually happens instead of what you imagine will happen. You can iterate. You can improve. You cannot improve something that only exists on your local machine.

## 10. Stay Curious, Stay Humble

Fifteen years in, I still learn something new every week. Not because I'm some paragon of lifelong learning — because this industry demands it. The moment you think you know enough is the moment you start falling behind.

But it's more than that. Staying curious keeps the work fun. The day I stop being excited about a new Laravel feature or a clever Pest assertion or an elegant bit of architecture is the day I should find a different career.

I don't have it all figured out. I'm better than I was five years ago, and I'll be better five years from now. That's the deal. That's the journey.

And honestly? I wouldn't trade it for anything.
MARKDOWN;
    }

    private static function post4(): string
    {
        return <<<'MARKDOWN'
## The Annual Ritual

Every year, like clockwork, the hot takes arrive. "PHP is dead." "Laravel is just a monolith framework in a microservices world." "You should be using [insert JavaScript framework that didn't exist eighteen months ago]." And every year, I spin up a new Laravel project, build something great with it, and wonder what all the fuss was about.

It's 2026. I've been using Laravel for over a decade. And I'm not just sticking with it out of inertia or comfort — I'm actively, enthusiastically choosing it. Let me tell you why.

## Developer Experience Is Not a Luxury

There's a strain of thinking in software development that says developer experience doesn't matter. What matters is performance, scalability, theoretical purity. And sure, those things matter. But you know what else matters? Actually enjoying the eight-plus hours a day you spend writing code.

Laravel respects your time. The API is expressive and consistent. The documentation is genuinely good — not "good for open-source," but actually good. When you need to do something, there's usually a clean, well-documented way to do it. When you hit an edge case, the framework gets out of your way instead of fighting you.

Taylor Otwell has said that Laravel is about making developers happy, and I know some people roll their eyes at that. But happiness isn't trivial. Happy developers write better code. They stick around longer. They build better products. Optimizing for developer experience is optimizing for outcomes.

## The Ecosystem Is Unmatched

Let's talk about what you actually get when you choose Laravel in 2026.

**Livewire** lets you build reactive, dynamic interfaces without writing JavaScript for every interaction. I know the "no JavaScript" framing annoys some people, but the reality is that for 80% of the interactive components in a typical web app, Livewire is faster to build, easier to maintain, and perfectly performant. I'm not anti-JavaScript — I'm anti-unnecessary-complexity.

**Filament** has fundamentally changed how I think about admin panels. I used to spend days building CRUD interfaces by hand. Now I can stand up a full admin panel with custom forms, tables, filters, and actions in hours. And it doesn't look like a generic admin template — it looks *good*. The Filament team has done something remarkable.

**Pest** makes testing actually pleasant. I covered this in my last post, but it bears repeating: Pest's syntax is so clean that writing tests feels like writing a specification. `it('can create a user')` reads like English. That matters for adoption — if testing is painful, people won't do it.

Then there's **Forge** for deployment, **Vapor** for serverless, **Horizon** for queues, **Sanctum** and **Passport** for auth, **Scout** for search, **Cashier** for billing. The first-party ecosystem covers an enormous amount of ground, and it's all maintained to a high standard.

And that's before you get to the community packages. Spatie alone has probably saved me thousands of hours over the years. The breadth and quality of the Laravel package ecosystem is a genuine competitive advantage.

## Pragmatism Over Dogma

One of the things I love most about Laravel is its pragmatism. The framework doesn't force you into a rigid architectural pattern. It gives you sensible defaults and gets out of your way when you need to deviate.

Need a simple app? Controllers, models, Blade templates — done. Need something more structured? Bring in service classes, actions, DTOs, whatever makes sense. Want to go full DDD? You can do that too. Laravel doesn't judge. It gives you the tools and trusts you to make good decisions.

Compare this to frameworks that are opinionated to the point of rigidity, where deviating from the prescribed way of doing things means fighting the framework at every turn. Or to the opposite extreme — frameworks that give you nothing and expect you to assemble everything from scratch, making two hundred decisions before you can display a form.

Laravel hits the sweet spot. Opinionated enough to be productive out of the box. Flexible enough to accommodate your specific needs.

## PHP Is Actually Great Now

Part of choosing Laravel means choosing PHP, and I want to address this directly: **PHP in 2026 is an excellent language.** Enums, fibers, readonly properties, intersection types, match expressions, named arguments — the language has evolved enormously. The performance improvements from PHP 8.x onward have been staggering.

The "PHP is bad" meme is based on PHP circa 2005. It's like making fun of JavaScript based on pre-ES6. The language grew up. A lot of the people making fun of it didn't notice.

I've used other languages. I've built things in Python, dabbled in Go, written enough JavaScript to have opinions about it. PHP with Laravel remains my most productive stack. Not because I'm too lazy to learn something new — because I genuinely believe it's the best tool for the kind of work I do.

## The Community Is the Secret Weapon

I keep coming back to this, but it's true: the Laravel community is special. Laracon is one of the best developer conferences I've attended. The online community — Discord, Twitter, forums, YouTube — is active and welcoming. When I got stuck on something, I've had package maintainers personally help me troubleshoot in Discord threads. Try getting that kind of support from a billion-dollar framework backed by a trillion-dollar company.

The community also produces an incredible amount of educational content. Laracasts is a treasure. The number of high-quality blogs, podcasts (including, he said humbly, my own — launching soon), and YouTube channels dedicated to Laravel is remarkable for a framework of its size.

This community orientation isn't accidental — it flows from the top. Taylor and the Laravel team actively foster community. They spotlight community members. They maintain an ecosystem of tools that work together seamlessly. They listen to feedback. It's not perfect, but it's a culture that I want to be part of.

## "But What About..."

I can hear the objections.

**"But it doesn't scale."** It does. Laravel runs some seriously high-traffic applications. And if you hit the point where PHP itself is the bottleneck (you probably won't), you can extract specific services into other languages. You don't need to start with a microservices architecture to handle hypothetical future scale.

**"But it's a monolith."** Yes, and monoliths are fine. For most applications, a well-structured monolith is simpler to develop, deploy, and debug than a distributed system. The industry's obsession with microservices has caused more problems than it's solved for the vast majority of projects.

**"But JavaScript full-stack is the future."** Maybe. But I can build a full-featured application with Laravel, Livewire, and Alpine.js faster than most teams can agree on which JavaScript meta-framework to use. Shipping beats theorizing.

## It's About Building Things

At the end of the day, I choose Laravel because it lets me build things. Real things. Quickly. With confidence that they'll work, that they'll be maintainable, and that I'll enjoy the process of building them.

That's not a small thing. In a world of over-engineering and resume-driven development, the ability to sit down and just *build* is valuable. Laravel gives me that. It has for ten years, and I don't see that changing anytime soon.

If you're a Laravel developer and you're feeling pressure to switch to whatever's trendy, take a breath. Look at what you can build. Look at the ecosystem around you. Look at the community. You're in a good place.

And if you're not a Laravel developer and you're curious — come on in. The water's fine.
MARKDOWN;
    }

    private static function post5(): string
    {
        return <<<'MARKDOWN'
## A Starting Point, Not a Standard

Let me be clear upfront: this is how *I* structure my Laravel projects. It's not the One True Way. It's the result of years of iteration, mistakes, refactoring, and learning what works for the kinds of applications I build. Your mileage may vary, and that's fine.

That said, I've used this structure on enough projects — solo and on teams — that I'm confident it scales well from small apps to medium-large ones. It's not exotic. It doesn't require you to learn a whole new paradigm. It's just Laravel, organized intentionally.

## The Folder Structure

Here's what my `app/` directory typically looks like:

```
app/
├── Actions/
├── DTOs/
├── Enums/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Policies/
├── Providers/
├── Services/
├── Queries/
└── View/
    └── Components/
```

Nothing here should scare you. Most of these are standard Laravel. The additions — `Actions`, `DTOs`, `Enums`, `Services`, `Queries` — are just conventions for organizing code that would otherwise end up crammed into controllers or models.

## Controllers: Thin and Boring

My controllers are thin. Intentionally, almost aggressively thin. A controller method should do three things: validate input, call something that does the work, and return a response.

```php
class SubscriptionController extends Controller
{
    public function store(
        CreateSubscriptionRequest $request,
        CreateSubscription $action,
    ): RedirectResponse {
        $action->handle(
            user: $request->user(),
            plan: Plan::from($request->validated('plan')),
        );

        return redirect()
            ->route('dashboard')
            ->with('success', 'Subscription created.');
    }
}
```

That's it. The controller doesn't know *how* a subscription is created. It doesn't contain business logic. It delegates to a Form Request for validation and an Action for execution. If the business logic changes, the controller doesn't change. If the validation rules change, I update the Form Request. Everything has one reason to change.

I also lean toward resource controllers and single-action controllers (`__invoke`) for clarity. If a controller has more than five or six methods, something probably needs to be split up.

## Form Requests: Validation Lives Here

Every form submission gets a Form Request. No inline validation in controllers. Ever.

```php
class CreateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canSubscribe();
    }

    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', Rule::enum(Plan::class)],
        ];
    }
}
```

Form Requests are one of Laravel's best features and I think they're underused. They handle validation *and* authorization in one clean class. They're automatically injected. They keep your controllers clean. Use them.

## Actions: Single-Purpose Business Logic

This is the heart of my architecture. An Action is a class that does one thing. Not "manages subscriptions" — "creates a subscription." Singular, specific, testable.

```php
class CreateSubscription
{
    public function handle(User $user, Plan $plan): Subscription
    {
        $subscription = $user->subscriptions()->create([
            'plan' => $plan,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        SendWelcomeEmail::dispatch($subscription);
        SubscriptionMetrics::track($subscription);

        return $subscription;
    }
}
```

Why Actions instead of just putting this in the controller? Because this logic might need to be called from multiple places — a controller, a console command, a queued job, an API endpoint. If the logic lives in the controller, you end up duplicating it or doing awkward controller-to-controller calls. If it lives in an Action, anyone can call it.

Actions are also trivially easy to test:

```php
it('creates a subscription for the given plan', function () {
    $user = User::factory()->create();

    $subscription = (new CreateSubscription)->handle(
        user: $user,
        plan: Plan::Monthly,
    );

    expect($subscription)
        ->toBeInstanceOf(Subscription::class)
        ->plan->toBe(Plan::Monthly)
        ->starts_at->toBeInstanceOf(Carbon::class);

    expect($user->subscriptions)->toHaveCount(1);
});
```

That Pest test reads like a specification. You know exactly what it's testing and what the expected outcome is.

## Services: When Actions Aren't Enough

Sometimes you need a class that coordinates multiple operations or wraps an external API. That's what the `Services` directory is for.

```php
class StripeService
{
    public function __construct(
        private StripeClient $client,
    ) {}

    public function createCustomer(User $user): string
    {
        $customer = $this->client->customers->create([
            'email' => $user->email,
            'name' => $user->name,
        ]);

        return $customer->id;
    }

    public function charge(string $customerId, int $amount): PaymentIntent
    {
        return $this->client->paymentIntents->create([
            'customer' => $customerId,
            'amount' => $amount,
            'currency' => 'usd',
        ]);
    }
}
```

The distinction between Actions and Services is admittedly fuzzy, and I don't lose sleep over it. My rough guideline: Actions represent things your application *does* (business operations). Services represent integrations or utilities that support those operations.

## DTOs: Structured Data Without the Guesswork

For anything more complex than a couple of parameters, I use Data Transfer Objects.

```php
readonly class SubscriptionData
{
    public function __construct(
        public Plan $plan,
        public ?Carbon $startsAt = null,
        public ?string $couponCode = null,
    ) {}

    public static function fromRequest(CreateSubscriptionRequest $request): self
    {
        return new self(
            plan: Plan::from($request->validated('plan')),
            startsAt: $request->validated('starts_at')
                ? Carbon::parse($request->validated('starts_at'))
                : null,
            couponCode: $request->validated('coupon_code'),
        );
    }
}
```

DTOs give you autocompletion, type safety, and a clear contract for what data a method expects. No more passing arrays around and hoping the keys are right. PHP 8.2's `readonly` classes make this pattern incredibly clean.

## Enums: Because Magic Strings Are Evil

If a value has a fixed set of options, it's an enum. Period.

```php
enum Plan: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case Lifetime = 'lifetime';

    public function price(): int
    {
        return match ($this) {
            self::Monthly => 1500,
            self::Yearly => 15000,
            self::Lifetime => 50000,
        };
    }
}
```

No more `if ($plan === 'monthly')` scattered across your codebase. No more typos causing silent bugs. Enums are one of the best additions to PHP in years and I use them aggressively.

## Queries: Reusable Query Logic

When I have complex Eloquent queries that are used in multiple places, I extract them into Query classes.

```php
class ActiveSubscriptionsQuery
{
    public function __invoke(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where('ends_at', '>', now());
    }
}
```

These can be used as scopes, in pipelines, or called directly. They keep my models from becoming 500-line god classes, which is a trap I've fallen into more than once.

## Testing With Pest

Testing is something I'm genuinely passionate about, and I've developed a strong opinion on how test suites should be organized. Every project gets a `tests/` directory that mirrors the `app/` directory structure, split into three distinct suites: **Unit**, **Integration**, and **Feature**.

```
tests/
├── Feature/
│   ├── Controllers/
│   │   ├── SubscriptionControllerTest.php
│   │   └── AuthControllerTest.php
│   └── Commands/
│       └── PruneExpiredSubscriptionsTest.php
├── Integration/
│   ├── Actions/
│   │   └── CreateSubscriptionTest.php
│   ├── Models/
│   │   └── SubscriptionTest.php
│   ├── Queries/
│   │   └── ActiveSubscriptionsQueryTest.php
│   └── Services/
│       └── StripeServiceTest.php
└── Unit/
    ├── DTOs/
    │   └── SubscriptionDataTest.php
    └── Enums/
        └── PlanTest.php
```

The distinction between these three suites is important, and I see a lot of developers get it wrong.

**Feature tests** test the entry points into your application — controllers and console commands. They test the full HTTP request/response cycle or the full command execution. These are your highest-level tests: "when a user hits this endpoint with this data, does the right thing happen?"

```php
it('creates a subscription for the authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/subscriptions', [
            'plan' => 'monthly',
        ]);

    $response->assertRedirect(route('dashboard'));
    expect($user->subscriptions)->toHaveCount(1);
});

it('rejects invalid plan types', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/subscriptions', ['plan' => 'invalid'])
        ->assertSessionHasErrors('plan');
});
```

**Integration tests** are where you test how your code interacts with external systems — primarily the database, but also things like cache, queue, or third-party APIs. Models, query classes, actions, services that hit the database, and repository-style logic belong here. These tests use `RefreshDatabase` and actually read and write to a test database.

```php
it('returns only active subscriptions', function () {
    Subscription::factory()->active()->count(3)->create();
    Subscription::factory()->expired()->count(2)->create();

    $active = Subscription::query()
        ->tap(new ActiveSubscriptionsQuery)
        ->get();

    expect($active)->toHaveCount(3);
});
```

**Unit tests are true unit tests.** They test a single class in complete isolation with zero external dependencies. No database, no file system, no HTTP calls, no queue — nothing. If your "unit test" needs to migrate a database to pass, it's not a unit test. DTOs, Enums, and pure logic classes get unit tested. Mock or stub any dependencies.

```php
it('creates subscription data from valid input', function () {
    $data = new SubscriptionData(
        plan: Plan::Monthly,
        startsAt: Carbon::parse('2026-03-01'),
    );

    expect($data)
        ->plan->toBe(Plan::Monthly)
        ->startsAt->toBeInstanceOf(Carbon::class)
        ->couponCode->toBeNull();
});

it('calculates the correct price for each plan', function (Plan $plan, int $expected) {
    expect($plan->price())->toBe($expected);
})->with([
    [Plan::Monthly, 1500],
    [Plan::Yearly, 15000],
    [Plan::Lifetime, 50000],
]);
```

This three-suite approach gives me confidence at every level. Feature tests confirm the whole request pipeline holds together. Integration tests verify my data layer works. Unit tests catch logic bugs fast (and run in milliseconds). When something breaks, the failing test suite tells me *where* to look.

I use Pest exclusively. The syntax is cleaner, the output is better, and the `expect()` API makes assertions readable. If you're still using PHPUnit, give Pest a real try. Not a five-minute glance — actually build something with it. I think you'll be surprised.

## Filament for Admin

If the project needs an admin panel — and most do — I reach for Filament. It integrates with this structure beautifully. Filament resources can use the same Actions, Services, and DTOs that the rest of the application uses. You're not maintaining two separate sets of business logic.

I typically put Filament panels in a separate `app/Filament/` directory (which is the default) and keep the admin logic thin, just like controllers. The real work still happens in Actions and Services.

## What I Don't Do

A few things I've intentionally avoided:

- **Repository pattern** (in most cases). Eloquent is already an abstraction over database access. Wrapping it in another abstraction usually adds complexity without benefit. If you're building something that genuinely might switch databases, sure. But you're probably not.
- **Over-interfacing.** I don't create an interface for every class. Interfaces are great when you need polymorphism or when you're building a package. For application code, the concrete class is usually fine.
- **Domain-Driven Design by default.** DDD is powerful for complex domains, but it's overkill for most Laravel apps. I borrow concepts (like value objects and Actions), but I don't reorganize my entire application around bounded contexts unless the domain genuinely demands it.

## The Point

Good structure isn't about following rules — it's about making your codebase navigable, testable, and maintainable. When a new developer joins the project, they should be able to find things. When you come back to code you wrote six months ago, you should be able to understand it.

This structure gives me that. It's Laravel, organized with intention. Nothing more, nothing less.

If you've got a structure that works better for you, I'd genuinely love to hear about it. Hit me up on social media or drop a comment. The whole point of writing this stuff down is to start conversations, not end them.
MARKDOWN;
    }
}
