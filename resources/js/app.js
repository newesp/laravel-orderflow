/**
 * Global Button Disable & Loading Animation Handler
 * Prevents accidental double clicks and provides visual loading feedback across all forms and action buttons.
 */

// SVG Spinner Icon markup
const SPINNER_SVG = `
    <svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
`;

/**
 * Set a button into loading & disabled state
 * @param {HTMLElement} button
 */
export function setButtonLoading(button) {
    if (!button || button.dataset.isLoading === 'true') {
        return;
    }

    // Backup original state
    button.dataset.isLoading = 'true';
    button.dataset.originalHtml = button.innerHTML;
    button.dataset.originalDisabled = button.disabled ? 'true' : 'false';
    // Backup original display classes if we are going to modify them
    button.dataset.addedFlex = 'false';

    // Disable button to prevent duplicate clicks
    button.disabled = true;
    button.classList.add('opacity-75', 'cursor-not-allowed');

    // Ensure button content sits inline properly
    if (!button.classList.contains('flex') && !button.classList.contains('inline-flex')) {
        button.classList.add('inline-flex', 'items-center', 'justify-center');
        button.dataset.addedFlex = 'true';
    }

    // Create and prepend spinner element
    const spinner = document.createElement('span');
    spinner.className = 'inline-block align-middle mr-2 button-spinner flex-shrink-0';
    spinner.innerHTML = SPINNER_SVG;

    button.prepend(spinner);
}

/**
 * Restore a button back to its original interactive state
 * @param {HTMLElement} button
 */
export function resetButtonLoading(button) {
    if (!button || button.dataset.isLoading !== 'true') {
        return;
    }

    if (button.dataset.originalHtml !== undefined) {
        button.innerHTML = button.dataset.originalHtml;
    }
    button.disabled = button.dataset.originalDisabled === 'true';
    button.classList.remove('opacity-75', 'cursor-not-allowed');

    if (button.dataset.addedFlex === 'true') {
        button.classList.remove('inline-flex', 'items-center', 'justify-center');
    }

    delete button.dataset.isLoading;
    delete button.dataset.originalHtml;
    delete button.dataset.originalDisabled;
    delete button.dataset.addedFlex;
}

/**
 * Reset all currently loading buttons across the entire document
 */
export function resetAllButtons() {
    document.querySelectorAll('[data-is-loading="true"]').forEach((btn) => {
        resetButtonLoading(btn);
    });
}

// Track the last clicked submit button within any form
let lastClickedSubmitButton = null;

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;

    // Track clicked submit button inside forms
    const submitBtn = target.closest('button[type="submit"], input[type="submit"], form button:not([type])');
    if (submitBtn) {
        lastClickedSubmitButton = submitBtn;
    }

    // Handle standalone action buttons tagged with [data-loading-click]
    const actionBtn = target.closest('[data-loading-click]');
    if (actionBtn && !actionBtn.hasAttribute('data-no-disable')) {
        // If it's not a form submit button (e.g. type="button" or <a>), lock immediately
        const isFormSubmit = actionBtn.matches('button[type="submit"], input[type="submit"], form button:not([type])');
        if (!isFormSubmit) {
            setTimeout(() => {
                setButtonLoading(actionBtn);
            }, 0);
        }
    }
}, true);

// Global form submission handler
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    // Check if form or button has opted out
    if (form.hasAttribute('data-no-disable')) return;

    // If default was prevented by validation or user confirmation cancellation, abort
    if (event.defaultPrevented) return;

    // Verify HTML5 validity if available
    if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
        return;
    }

    // Determine target button to disable
    let buttonToDisable = null;
    if (lastClickedSubmitButton && form.contains(lastClickedSubmitButton)) {
        buttonToDisable = lastClickedSubmitButton;
    } else {
        buttonToDisable = form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
    }

    if (buttonToDisable && !buttonToDisable.hasAttribute('data-no-disable')) {
        // Use setTimeout(0) to allow browser submit pipeline to register the submission payload
        setTimeout(() => {
            setButtonLoading(buttonToDisable);

            // Also disable any sibling submit buttons in the same form
            const allSubmitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])');
            allSubmitButtons.forEach((btn) => {
                if (btn !== buttonToDisable && !btn.hasAttribute('data-no-disable')) {
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                }
            });
        }, 0);
    }
});

// Restore buttons on browser history navigation (bfcache)
window.addEventListener('pageshow', () => {
    resetAllButtons();
});

// Expose utilities globally on window object
window.setButtonLoading = setButtonLoading;
window.resetButtonLoading = resetButtonLoading;
window.resetAllButtons = resetAllButtons;
