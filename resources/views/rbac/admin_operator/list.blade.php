@extends('layouts.layout')

@section('main_content')
    <section class="content-header">
        <h1>&nbsp;</h1>
        <ol class="breadcrumb">
            <li><a href="/operator"><i class="fa fa-dashboard"></i>操作员列表</a></li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">操作员列表</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive">
                        <div class="input-group input-group-sm">
                            <label>
                            <a href="/operator/edit" class="btn btn-success">新建</a>
                            </label>
                        </div>
                        <table class="table table-bordered">
                            <tbody><tr>
                                <th style="width: 10px">ID</th>
                                <th>名称</th>
                                <th>角色</th>
                                <th>创建于</th>
                                <th style="width: 200px">操作</th>
                            </tr>
                            @foreach($list as $item)
                                <tr>
                                    <td>{{$item->id}}</td>
                                    <td>{{$item->name}}</td>
                                    <td>{{$item->role->name}}</td>
                                    <td>{{$item->created_at->toIso8601String()}}</td>
                                    <td>
                                        <a href="/operator/edit?id={{$item->id}}" class="btn btn-primary btn-sm">编辑</a>
                                        <a href="/operator/delete?id={{$item->id}}" class="btn btn-danger btn-sm">删除</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer clearfix">
                        {!! $list->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection