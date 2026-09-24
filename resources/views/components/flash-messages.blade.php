@if (session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-md text-sm" role="alert">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm" role="alert">
        {{ session('error') }}
    </div>
@endif