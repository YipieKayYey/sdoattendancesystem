@if(request()->routeIs('filament.admin.auth.login'))
<style>
    /* Enlarge logo on login page only */
    .fi-simple-main-ctn .fi-simple-brand,
    [data-simple-main-ctn] [data-simple-brand],
    .fi-simple-brand {
        height: 9rem !important;
        max-height: 9rem !important;
    }
    
    .fi-simple-main-ctn .fi-simple-brand img,
    [data-simple-main-ctn] [data-simple-brand] img,
    .fi-simple-brand img,
    .fi-simple-main-ctn img[src*="sdologo"],
    [data-simple-main-ctn] img[src*="sdologo"] {
        height: 9rem !important;
        width: auto !important;
        max-height: 9rem !important;
        max-width: none !important;
    }
</style>
@endif
