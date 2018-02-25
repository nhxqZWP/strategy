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
                        <h3 class="box-title">Binance ETH/USDT</h3>
                        <span class="">
                            钱包余额 ETH:{{$coin1['available']}}({{$coin1['onOrder']}}) &nbsp;
                            USDT:{{$coin2['available']}}({{$coin2['onOrder']}})&nbsp;
                            总USDT估值：{{intval(($coin1['available']+$coin1['onOrder'])*$lastPrice+$coin2['available']+$coin2['onOrder'])}}USDT
                            &nbsp;&nbsp;{{intval(($coin1['available']+$coin1['onOrder'])*$lastPrice+$coin2['available']+$coin2['onOrder'])*$usdtCny}}CNY
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
                                    <td> 最长挂买单时间:</td>
                                    <td>
                                        <form action="/timelimit?plat=binance" method="post">
                                            <input type="text" name="limit" value="{{$timeLimit}}" size="10">s
                                            <input type="submit" name="提交">
                                        </form>
                                    </td>
                                    <td width="30px"> </td>
                                    <td> 每笔净利润:(用BNB)</td>
                                    <td>
                                        <form action="/binance/profit" method="post">
                                            <input type="text" name="profit" value="{{$profit}}" size="10">
                                            <input type="hidden" name="pair" value="{{$pair}}">
                                            <input type="submit" name="提交">
                                        </form>
                                    </td>
                                    <td width="30px"> </td>
                                    <td> 卖单取消时间:</td>
                                    <td>
                                        <form action="/binance/cancelSell" method="post">
                                            <input type="text" name="time" value="{{$sellCancelTime}}" size="10">h
                                            <input type="submit" name="提交">
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="box-body">
                            <form action="/binance/params" method="post">
                                <table border="1px" width="800px">
                                    <tr>
                                        <th></th>
                                        <th class="text-center">第一组</th>
                                        <th class="text-center">第二组</th>
                                        <th class="text-center">第三组</th>
                                        <th class="text-center">第四组</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <td>ETH成交量</td>
                                        <td>
                                            <input type="text" name="group1_coin1" value="{{$param['1coin']}}">ETH
                                        </td>
                                        <td>
                                            <input type="text" name="group2_coin1" value="{{$param['2coin']}}">ETH
                                        </td>
                                        <td>
                                            <input type="text" name="group3_coin1" value="{{$param['3coin']}}">ETH
                                        </td>
                                        <td>
                                            <input type="text" name="group4_coin1" value="{{$param['4coin']}}">ETH
                                        </td>
                                        <td rowspan="2">
                                            <input type="hidden" name="pair" value="ETH_USDT">
                                            <input type="submit" name="提交">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>深度偏移量</td>
                                        <td>
                                            <input type="text" name="group1_offset" value="{{$param['1offset']}}">
                                        </td>
                                        <td>
                                            <input type="text" name="group2_offset" value="{{$param['2offset']}}">
                                        </td>
                                        <td>
                                            <input type="text" name="group3_offset" value="{{$param['3offset']}}">
                                        </td>
                                        <td>
                                            <input type="text" name="group4_offset" value="{{$param['4offset']}}">
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
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
                                @if(!empty($openOrders))
                                    @foreach($openOrders as $oo)
                                        <tr>
                                            <td>{{$oo['orderId']}}</td>
                                            <td>{{$oo['side']}}</td>
                                            <td>{{$oo['price']}} usdt</td>
                                            <td>{{$oo['origQty']}}</td>
                                            <td>{{$oo['price'] * $oo['origQty']}} usdt</td>
                                            <td>{{date('Y-m-d H:i:s', (int)substr($oo['time'],0,10))}}</td>
                                            <td>{{$oo['status']}}</td>
                                            <td>
                                                <a href="/cancel/order?number={{$oo['orderId']}}&plat=binance&pair={{$pair}}"
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
                                    <th>手续费</th>
                                    <th>成交时间</th>
                                </tr>
                                @if(!empty($tradeHistory))
                                    @foreach($tradeHistory as $th)
                                        <tr>
                                            <td>{{$th['orderId']}}</td>
                                            <td>
                                                @if($th['isBuyer']) 买单
                                                    @elseif($th['isMaker']) 卖单
                                                    @else 市价单
                                                    @endif
                                            </td>
                                            <td>{{$th['price']}} usdt</td>
                                            <td>{{$th['qty']}}</td>
                                            <td>{{$th['price'] * $th['qty']}} usdt</td>
                                            <td>{{$th['commission']}} {{$th['commissionAsset']}}</td>
                                            <td>{{date('Y-m-d H:i:s', (int)substr($th['time'],0,10))}}</td>
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