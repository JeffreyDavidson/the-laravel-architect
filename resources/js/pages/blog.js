const copyIcon = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
const copiedIcon = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';

async function initializeCodeHighlighting() {
    try {
        await import('../prism');
    } catch {
        document.documentElement.dataset.codeHighlightingState = 'fallback';
    }
}

function scheduleCodeHighlighting() {
    if (!document.querySelector('.prose code[class*="language-"]')) {
        return;
    }

    document.documentElement.dataset.codeHighlightingState = 'idle';

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(initializeCodeHighlighting, { timeout: 1200 });

        return;
    }

    window.setTimeout(initializeCodeHighlighting);
}

function initializeCodeCopyButtons() {
    document.querySelectorAll('.prose pre').forEach((pre) => {
        const code = pre.querySelector('code');

        if (!code || pre.querySelector('.copy-btn')) {
            return;
        }

        const button = document.createElement('button');

        button.innerHTML = copyIcon;
        button.className = 'copy-btn';
        button.type = 'button';
        button.title = 'Copy code';
        button.setAttribute('aria-label', 'Copy code');
        button.addEventListener('click', async () => {
            await navigator.clipboard.writeText(code.innerText);
            button.innerHTML = copiedIcon;
            button.classList.add('copied');

            window.setTimeout(() => {
                button.innerHTML = copyIcon;
                button.classList.remove('copied');
            }, 2000);
        });
        pre.appendChild(button);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initializeCodeCopyButtons();
        scheduleCodeHighlighting();
    }, { once: true });
} else {
    initializeCodeCopyButtons();
    scheduleCodeHighlighting();
}
