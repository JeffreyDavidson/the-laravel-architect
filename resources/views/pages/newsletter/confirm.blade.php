<x-newsletter.action-page
    command="newsletter:confirm"
    title="Confirm your subscription"
    description="Confirm that you want newsletter updates sent to {{ $subscriber->email }}."
    :action-url="$actionUrl"
    button-label="Confirm subscription"
    pending-label="Confirming your subscription…"
    :page-meta="$pageMeta"
/>
