<x-mail.layout title="Confirm your subscription" preheader="One click to confirm your subscription. The link expires in 24 hours.">
    <h1 class="mail-heading" style="margin: 0 0 16px; font-size: 26px; line-height: 1.25; font-weight: 800; color: #111827;">
        Confirm your subscription
    </h1>
    <p class="mail-text" style="margin: 0 0 24px; font-size: 16px; line-height: 1.65; color: #374151;">
        Thanks for signing up for the newsletter. Confirm your email address to start getting practical Laravel tips,
        tutorials, and thoughts on building better apps.
    </p>
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 0 28px;">
        <tr>
            <td align="center" bgcolor="#3f6fa8" style="border-radius: 10px; background: #3f6fa8;">
                <a href="{{ $confirmationUrl }}" style="display: inline-block; padding: 14px 28px; font-size: 16px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 10px;">Confirm subscription</a>
            </td>
        </tr>
    </table>
    <p class="mail-note" style="margin: 0 0 24px; padding: 12px 14px; background: #eaf3fa; border-left: 3px solid #4a7fbf; border-radius: 4px; font-size: 14px; line-height: 1.6; color: #374151;">
        This link expires in 24 hours. If you didn't sign up, you can ignore this email. You won't be subscribed unless
        you confirm.
    </p>
    <p class="mail-muted" style="margin: 0; font-size: 13px; line-height: 1.6; color: #6b7280;">
        If the button doesn't work, copy and paste this link into your browser:<br />
        <x-mail.link :href="$confirmationUrl" style="word-break: break-all;">{{ $confirmationUrl }}</x-mail.link>
    </p>
    <x-slot:footer>
        <p style="margin: 0 0 8px;">You're receiving this because this address was entered on the newsletter sign-up form.</p>
    </x-slot:footer>
</x-mail.layout>
