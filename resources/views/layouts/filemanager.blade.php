<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>File Manager</title>
    <link rel="icon" href=" {{ asset('assets/apple-touch-icon-precomposed.png') }}" sizes="16x16" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-5.0.2/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/jstree/dist/themes/default/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/swetalert/sweetalert2.min.css') }}">

    <style>
        .swal2-timer-progress-bar {
            background: #28a745 !important;
            /* Hijau */
        }
    </style>
</head>

<body style="zoom: 80%;">

    @include('layouts.filemanager-navigation')

    <div class="container-fluid mt-2">
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Modal   -->
    @include('filemanager/modal.buat-folder')
    @include('filemanager/modal.upload-file')

    <script src="{{ asset('assets/jstree/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/bootstrap-5.0.2/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/jstree/dist/jstree.min.js') }}"></script>
    <script src="{{ asset('assets/swetalert/sweetalert2.all.min.js') }}"></script>

    @include('filemanager/script.ajax')

</body>

</html>
