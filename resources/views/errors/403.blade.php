<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied — W.R Soysa Lottery ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 font-sans antialiased flex items-center justify-center p-6">

<div class="w-full max-w-md text-center">
    <div class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-red-100 mb-6">
        <svg class="h-10 w-10 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
    </div>

    <h1 class="text-6xl font-bold text-gray-900 mb-2">403</h1>
    <h2 class="text-xl font-semibold text-gray-800 mb-3">Access Denied</h2>
    <p class="text-gray-500 text-sm mb-8">
        You don't have permission to view this page.<br>
        This area is restricted to Administrators only.
    </p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
           class="rounded-xl border border-gray-300 text-gray-700 text-sm font-medium px-5 py-2.5 hover:bg-gray-100 transition-colors">
            Go Back
        </a>
        <a href="{{ route('dashboard') }}"
           class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 transition-colors">
            Back to Dashboard
        </a>
    </div>

    <p class="mt-10 text-xs text-gray-400">W.R Soysa Lottery ERP &mdash; Girandurukotte &amp; Mahiyanganya</p>
</div>

</body>
</html>
