@props(['schemas' => []])

<script nonce="{{ Vite::cspNonce() }}" type="application/ld+json">
    {!!
        json_encode([
            '@context' => 'https://schema.org',
            '@graph' => $schemas,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    !!}
</script>
