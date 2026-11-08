<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Atelier')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
    <div class="flex justify-center mb-8">
        <img src="/images/logo.svg" alt="Atelier" class="h-24 w-auto">
    </div>
    @yield('content')
</div>

<script src="/js/sweetalert2.all.min.js"></script>

@if(session('swal_success'))
<script>
Swal.fire({ icon: "success", title: "Berhasil", text: "{{ session('swal_success') }}", confirmButtonColor: "#F4A95A" });
</script>
@endif

@if(session('swal_error'))
<script>
Swal.fire({ icon: "error", title: "Gagal", text: "{{ session('swal_error') }}", confirmButtonColor: "#F4A95A" });
</script>
@endif

@if(session('swal_info'))
<script>
Swal.fire({ icon: "info", title: "Info", text: "{{ session('swal_info') }}", confirmButtonColor: "#F4A95A" });
</script>
@endif

@stack('scripts')
</body>
</html>
