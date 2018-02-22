<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Wincoin.pro | 管理后台</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="http://cdn.staticfile.org/twitter-bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://cdn.staticfile.org/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="http://cdn.staticfile.org/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="/bower_components/AdminLTE/dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="/bower_components/AdminLTE/dist/css/skins/skin-blue.min.css">
    <link rel="stylesheet" href="/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="/css/common.css">
    @yield('css')

    <!--[if lt IE 9]>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <!-- Main Header -->
    @include('layouts.header')

    <!-- Left side column. contains the logo and sidebar -->
    @include('layouts.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        @yield('main_content')
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    @include('layouts.footer')

    <!-- Control Sidebar -->
    @include('layouts.tabs')

    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->

<script src="http://cdn.staticfile.org/jquery/2.1.4/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="http://cdn.staticfile.org/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="/js/bootstrap-datetimepicker.min.js"></script>
<!-- AdminLTE App -->
<script src="/bower_components/AdminLTE/dist/js/app.js"></script>
<script src="/bower_components/Bootbox/bootbox.js"></script>
<script src="/js/common.js"></script>
<script src="/js/plupload.full.min.js"></script>
<script src="/js/qiniu.min.js"></script>
@yield('js')
</body>
</html>
