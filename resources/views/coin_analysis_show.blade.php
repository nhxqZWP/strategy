@extends('layouts.layout')

@section('main_content')
    <section class="content">
        {{--<div class="row">--}}
            {{--<div id="process-chart" style="width: 95%;height:400px;"></div>--}}
        {{--</div>--}}
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">gate.io GTC/USDT</h3>
                        <span class="col-sm-offset-1">
                            钱包余额 GTC:{{$wallet['coin1_total']}} &nbsp;
                            USDT:{{$wallet['coin2_total']}} &nbsp;
                            最新交易价:{{$lastPrice}} &nbsp;
                            现价总值:{{$walletTotal}}
                        </span>
                        {{--<span class="col-sm-offset-1">脚本运行状态:--}}
                            {{--@if($open == 2) <font color="red">close</font> <a href="/switch?pair={{$pair}}&status=1" class="btn btn-success btn-xs">开启</a>--}}
                            {{--@else <font color="green">run</font> <a href="/switch?pair={{$pair}}&status=2" class="btn btn-warning btn-xs">关闭</a> @endif--}}
                        {{--</span>--}}
                    </div>
                    @include('layouts.form_errors')
                    <div class="box-body">
                        <div>
                            <table>
                                <tr>
                                    <td>脚本运行状态:
                                        @if($open == 2) <font color="red">close</font> &nbsp;<a href="/switch?pair={{$pair}}&status=1" class="btn btn-success btn-xs">开启</a>
                                        @else <font color="green">run</font> &nbsp;<a href="/switch?pair={{$pair}}&status=2" class="btn btn-warning btn-xs">关闭</a> @endif
                                    </td>
                                    <td width="30px"> </td>
                                    <td> 最长挂单时间:</td>
                                    <td>
                                        <form action="/timelimit" method="post">
                                            <input type="text" name="limit" value="{{$timeLimit}}" size="10">s
                                            <input type="submit" name="提交">
                                        </form>
                                    </td>
                                    <td width="30px"> </td>
                                    <td> 每笔利润率:</td>
                                    <td>
                                        <form action="/getpercent" method="post">
                                            <input type="text" name="percent" value="{{$coinPercent}}" size="10">
                                            <input type="hidden" name="pair" value="{{$pair}}">
                                            <input type="submit" name="提交">
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <br>
                        <div>当前挂单列表</div>
                        <div class="dataTables_wrapper form-inline dt-bootstrap">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>订单号</th>
                                    <th>订单类型</th>
                                    <th>下单价格</th>
                                    <th>下单量</th>
                                    <th>总计</th>
                                    <th>创建时间</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                                @if(!empty($openOrders['orders']))
                                    @foreach($openOrders['orders'] as $oo)
                                        <tr>
                                            <td>{{$oo['orderNumber']}}</td>
                                            <td>{{$oo['type']}}</td>
                                            <td>{{$oo['initialRate']}} usdt</td>
                                            <td>{{$oo['initialAmount']}}</td>
                                            <td>{{$oo['initialAmount']}} usdt</td>
                                            <td>{{date('Y-m-d H:i:s', $oo['timestamp'])}}</td>
                                            <td>{{$oo['status']}}</td>
                                            <td>
                                                <a href="/cancel/order?number={{$oo['orderNumber']}}"
                                                   class="btn btn-warning btn-xs">取消</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div>24内成交记录</div>
                        <div class="dataTables_wrapper form-inline dt-bootstrap">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>订单号</th>
                                    <th>买卖类型</th>
                                    <th>价格</th>
                                    <th>数量</th>
                                    <th>总计</th>
                                    <th>成交时间</th>
                                </tr>
                                @if(!empty($tradeHistory['trades']))
                                    @foreach($tradeHistory['trades'] as $th)
                                        <tr>
                                            <td>{{$th['orderid']}}</td>
                                            <td>{{$th['type']}}</td>
                                            <td>{{$th['rate']}} usdt</td>
                                            <td>{{$th['amount']}}</td>
                                            <td>{{$th['rate'] * $th['amount']}} usdt</td>
                                            <td>{{date('Y-m-d H:i:s', $th['time_unix'])}}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="box-footer clearfix">
                        {{--@if(!empty($list)){!! $list->render() !!}@endif--}}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade bs-example-modal-sm" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">确认删除此商品?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <a href="" class="btn btn-danger">确认</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('whatever');
            var modal = $(this);
            modal.find('.modal-footer a').attr('href', '/product/delete?id=' + id);
        })
    </script>
    <script language="JavaScript">
        setTimeout(function(){location.reload()},60000); //指定60秒刷新一次
    </script>
@endsection