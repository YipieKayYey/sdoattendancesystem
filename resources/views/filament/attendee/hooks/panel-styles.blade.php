<style>
    /* Attendee panel: improve form readability in light and dark mode */
    /* Input borders - visible in both modes for easy identification and clicking */
    .fi-panel-attendee .fi-input-wrp,
    .fi-panel-attendee .fi-select-input-wrapper,
    .fi-panel-attendee [data-slot="input-wrapper"] {
        border: 1px solid #111827;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }
    .dark .fi-panel-attendee .fi-input-wrp,
    .dark .fi-panel-attendee .fi-select-input-wrapper,
    .dark .fi-panel-attendee [data-slot="input-wrapper"] {
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.2);
    }
    .fi-panel-attendee .fi-input-wrp:focus-within,
    .fi-panel-attendee .fi-select-input-wrapper:focus-within,
    .fi-panel-attendee [data-slot="input-wrapper"]:focus-within {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.2);
    }
    .dark .fi-panel-attendee .fi-input-wrp:focus-within,
    .dark .fi-panel-attendee .fi-select-input-wrapper:focus-within,
    .dark .fi-panel-attendee [data-slot="input-wrapper"]:focus-within {
        border-color: #38bdf8;
        box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
    }
    .fi-panel-attendee .fi-fo-field-wrp-helper-text {
        color: #4b5563;
    }
    .dark .fi-panel-attendee .fi-fo-field-wrp-helper-text {
        color: #d1d5db;
    }
    .fi-panel-attendee .fi-input-wrp-label {
        color: #374151;
    }
    .dark .fi-panel-attendee .fi-input-wrp-label {
        color: #e5e7eb;
    }
    .fi-panel-attendee .fi-input-wrp-input input,
    .fi-panel-attendee .fi-select-input {
        color: #111827;
    }
    .dark .fi-panel-attendee .fi-input-wrp-input input,
    .dark .fi-panel-attendee .fi-select-input {
        color: #f3f4f6;
    }
    .fi-panel-attendee .fi-input-wrp-input input::placeholder,
    .fi-panel-attendee .fi-select-input::placeholder {
        color: #6b7280;
    }
    .dark .fi-panel-attendee .fi-input-wrp-input input::placeholder,
    .dark .fi-panel-attendee .fi-select-input::placeholder {
        color: #9ca3af;
    }
    .fi-panel-attendee .fi-section-header-description,
    .fi-panel-attendee .fi-section-content p {
        color: #4b5563;
    }
    .dark .fi-panel-attendee .fi-section-header-description,
    .dark .fi-panel-attendee .fi-section-content p {
        color: #d1d5db;
    }
</style>
