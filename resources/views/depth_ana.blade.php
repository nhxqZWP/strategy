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
                <div class="dataTables_wrapper form-inline dt-bootstrap">
                    <table class="table table-bordered table-hover table-striped">
                        <tbody>
                        <tr>
                            <th scope="col">交易对</th>
                            <th scope="col">买单高位占比
                                <a class="glyphicon glyphicon-sort-by-order-alt btn btn-default btn-xs" href="/huobi/depth/all?type=1" role="button"></a>
                            </th>
                            <th scope="col">卖单低位占比
                                <a class="glyphicon glyphicon-sort-by-order btn btn-default btn-xs" href="/huobi/depth/all?type=2" role="button"></a>
                            </th>
                            <th scope="col">买单与卖单量差比
                                <a class="glyphicon glyphicon-sort-by-order-alt btn btn-default btn-xs" href="/huobi/depth/all?type=3" role="button"></a>
                            </th>
                        </tr>
                        @foreach($analysis as $item)
                            <tr>
                                <td>{{$item['ticker']}}</td>
                                <td @if(intval($item['buy']*100) > 80)style="color: #9f191f" @endif>{{$item['buy']*100}}%</td>
                                <td @if(intval($item['ask']*100) < 25)style="color: #9f191f" @endif>{{$item['ask']*100}}%</td>
                                <td @if(intval($item['del']*100) > 10000)style="color: #9f191f" @endif>{{$item['del']*100}}%</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                    <div class="box-footer clearfix">
                        {{--买单高量:{{$buyOne}}  低量:{{$buyTwo}} 高量占比 {{$buyOne/($buyOne+$buyTwo)*100}}%--}}
                    </div>

            </section>
        </div>

    </section>
    <!-- /.content -->

@endsection
