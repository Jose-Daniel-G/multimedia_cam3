@if (session('success'))
<div class="bg-green-200 border-green-600 p-4 mb-3 rounded-sm shadow-sm">
    {{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="bg-red-200 border-red-600 p-4 mb-3 rounded-sm shadow-sm">
    {{ session('error') }}
</div>
@endif