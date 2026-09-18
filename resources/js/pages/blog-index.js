function updateMetaContent(selector, content, attribute, value) {
    const element = document.querySelector(selector);

    if (!content) {
        element?.remove();

        return;
    }

    const meta = element ?? document.head.appendChild(document.createElement('meta'));

    if (!element) {
        meta.setAttribute(attribute, value);
    }

    meta.setAttribute('content', content);
}

function updateCanonicalUrl(url) {
    const element = document.querySelector('link[rel="canonical"]');

    if (element && url) {
        element.setAttribute('href', url);
    }
}

window.addEventListener(
    'blog-metadata-updated',
    ({ detail: { title, description, canonicalUrl, robots, structuredData } }) => {
        if (title) {
            document.title = title;
        }

        updateMetaContent('meta[name="description"]', description, 'name', 'description');
        updateMetaContent('meta[name="robots"]', robots, 'name', 'robots');
        updateMetaContent('meta[property="og:title"]', title, 'property', 'og:title');
        updateMetaContent('meta[property="og:description"]', description, 'property', 'og:description');
        updateMetaContent('meta[property="og:url"]', canonicalUrl, 'property', 'og:url');
        updateMetaContent('meta[name="twitter:title"]', title, 'name', 'twitter:title');
        updateMetaContent('meta[name="twitter:description"]', description, 'name', 'twitter:description');
        updateCanonicalUrl(canonicalUrl);
        const schema = document.querySelector('script[type="application/ld+json"]');

        if (schema && structuredData) {
            schema.textContent = JSON.stringify({ '@context': 'https://schema.org', '@graph': structuredData });
        }
    },
);
