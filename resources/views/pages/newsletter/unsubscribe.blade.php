<x-newsletter.action-page
    command="newsletter:unsubscribe"
    title="Unsubscribe from the newsletter"
    description="Stop newsletter updates to {{ $subscriber->email }}. You can subscribe again at any time."
    :action-url="$actionUrl"
    button-label="Unsubscribe"
    method="DELETE"
    :seo-source="$seoSource ?? null"
    :structured-data="$structuredData ?? []"
/>
