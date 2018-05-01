@extends('layouts.layout')

@section('main_content')

    <!-- Content Header (Page header) -->
    {{--<section class="content-header">--}}
        {{--<h1>--}}
            {{--交易对--}}
        {{--</h1>--}}
        {{--<ol class="breadcrumb">--}}
            {{--<li><a href="#"><i class="fa fa-dashboard"></i> 管理后台</a></li>--}}
            {{--<li class="active">交易对</li>--}}
        {{--</ol>--}}
    {{--</section>--}}

    <!-- Main content -->
    <section class="content">
    <!-- Your Page Content Here -->
        <div class="row">
            <section class="col-lg-12 connectedSortable ui-sortable">
                    {{--<div class="box-header with-border">--}}
                        {{--<h5 class="box-title">五日线法 测试日志</h5>--}}
                    {{--</div>--}}
                    <div class="box-body">
                        {{--<div class="dataTables_wrapper form-inline dt-bootstrap">--}}

                <div id="pop_div" style="width:100%; height: 800px;"></div>
                 <?= $lava->render('ColumnChart', 'Finances', 'pop_div') ?>

                {{--<div id="pop_div2" style="width:100%; height: 500px;"></div>--}}
            <!--                     --><?//= $lava2->render('ColumnChart', 'Finances2', 'pop_div2') ?>

                        </div>
                    {{--</div>--}}
                    <div class="box-footer clearfix">
                        买单高量:{{$buyOne}}  低量:{{$buyTwo}} 高量占比 {{$buyOne/($buyOne+$buyTwo)*100}}%
                    </div>

            </section>
        </div>

    </section>
    <!-- /.content -->

@endsection
