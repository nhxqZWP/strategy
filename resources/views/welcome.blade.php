@extends('layouts.layout')

@section('main_content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            交易对
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> 管理后台</a></li>
            <li class="active">交易对</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>ETH/USDT</h3>
                        <p>{{$wallet1['coin1']['available'] + $wallet1['coin1']['onOrder']}}/{{$wallet1['coin2']['available'] + $wallet1['coin2']['onOrder']}}</p>
                    </div>
                    <div class="icon">
                        {{--<i class="ion ion-person-add"></i>--}}
                    </div>
                    <a href="/eth_usdt?pair=ETH_USDT" class="small-box-footer">详情<i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

        </div>
        <!-- Your Page Content Here -->
        <div class="row">
            <section class="col-lg-12 connectedSortable ui-sortable">
            </section>
        </div>

    </section>
    <!-- /.content -->

@endsection
