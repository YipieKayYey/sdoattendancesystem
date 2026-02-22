@props(['profile'])

@if($profile && $profile->hasSignature())
    <p class="text-sm text-gray-600 dark:text-gray-300">
        Signature provided — {{ $profile->signature_timestamp?->format('M j, Y g:i A') ?? '—' }}
    </p>
@else
    <p class="text-amber-600 dark:text-amber-400 text-sm font-medium">Signature not yet provided.</p>
@endif
