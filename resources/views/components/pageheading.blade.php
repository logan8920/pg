<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-2 text-gray-800">{{ $heading }}</h1>
    <ul class="d-flex m-0 p-0 navigator">
        <li>Home</li>
        @foreach (($navigation ?? []) as $a)
            <li>{{ $a }}</li>
        @endforeach
    </ul>
    @if ($description)
        <p class="mb-4">{{ $description }}</p>
    @endif
</div>