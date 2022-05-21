@push('head')
    <link
        href="{{ asset('img/logo.svg') }}"
        id="favicon"
        rel="icon"
    >
@endpush

<p class="h2 n-m font-thin v-center">
    <x-orchid-icon path="{{ asset('img/logo.svg') }}"/>
    <img src="{{ asset("img/logo.svg") }}" style="width: 40px; margin-right: 5px;">
    <span class="m-l d-none d-sm-block">
        Youngeek
        <small class="v-top opacity">Platform</small>
    </span>
</p>
