import { execFileSync } from 'node:child_process';

export const E2E_ADMIN_EMAIL = 'e2e-admin@example.test';
export const E2E_ADMIN_PASSWORD = 'e2e-password';

// The browser suite owns this account and removes it in global teardown.

const phpString = (value: string): string => JSON.stringify(value);

function runPhp(code: string): void {
    execFileSync('php', ['-r', code], {
        cwd: process.cwd(),
        env: process.env,
        stdio: "inherit",
    });
}

export default async function globalSetup(): Promise<void> {
    runPhp(`
        require 'vendor/autoload.php';
        $app = require 'bootstrap/app.php';
        $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
        $user = App\\Models\\User::query()->updateOrCreate(
            ['email' => ${phpString(E2E_ADMIN_EMAIL)}],
            [
                'name' => 'E2E Admin',
                'password' => Illuminate\\Support\\Facades\\Hash::make(${phpString(E2E_ADMIN_PASSWORD)}),
            ],
        );
        $user->forceFill(['is_admin' => true])->save();
    `);
}

export async function globalTeardown(): Promise<void> {
    runPhp(`
        require 'vendor/autoload.php';
        $app = require 'bootstrap/app.php';
        $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
        App\\Models\\User::query()->where('email', ${phpString(E2E_ADMIN_EMAIL)})->delete();
    `);
}
