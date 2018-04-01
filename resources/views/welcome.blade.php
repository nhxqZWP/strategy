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
                    <a href="/eth_usdt_new?pair=ETH_USDT" class="small-box-footer">详情<i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

        </div>

        <!-- Your Page Content Here -->
        <div class="row">
            <section class="col-lg-12 connectedSortable ui-sortable">
                        <div class="box">
                            <div class="box-header with-border">
                                <h4 class="box-title">追涨杀跌 测试收益日志</h4>
                            </div>
                            <div class="box-body">
                                <div class="dataTables_wrapper form-inline dt-bootstrap">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <th style="width: 20px">记录日期</th>
                                            <th>用户id</th>
                                            <th>交易币(可用+冻结)</th>
                                            <th>USDT(可用+冻结)</th>
                                        </tr>
                                        @if(!empty($list))
                                            @foreach($list as $item)
                                                <tr>
                                                    <td>{{$item->created_at}}</td>
                                                    <td>{{$item->uid}}</td>
                                                    <td>{{$item->coin_avail + $item->coin_onorder}} ({{$item->coin_avail}}+{{$item->coin_onorder}})</td>
                                                    <td>{{$item->usdt_avail + $item->usdt_onorder}} ({{$item->usdt_avail}}+{{$item->usdt_onorder}})</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="box-footer clearfix">
                                @if(!empty($list)){!! $list->render() !!}@endif
                            </div>
                        </div>

            </section>
        </div>

    </section>
    <!-- /.content -->

@endsection
