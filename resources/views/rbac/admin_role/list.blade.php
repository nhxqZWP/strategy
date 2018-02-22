@extends('layouts.layout')

@section('main_content')

    <section class="content-header">
        <h1>&nbsp;</h1>
        <ol class="breadcrumb">
            <li><a href="/role"><i class="fa fa-dashboard"></i>角色列表</a></li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">角色列表</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="">
                <label>
                <a href="/role/edit" class="btn btn-success">新建</a>
                </label>
            </div>
            <table class="table table-bordered">
                <tbody><tr>
                    <th style="width: 10px">ID</th>
                    <th>Name</th>
                    <th>Privileges</th>
                    <th>Created at</th>
                    <th style="width: 200px">Operations</th>
                </tr>
                @foreach($list as $item)
                <tr>
                    <td>{{$item->id}}</td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->privileges}}</td>
                    <td>{{$item->created_at->toIso8601String()}}</td>
                    <td>
                        <a href="/role/edit?id={{$item->id}}" class="btn btn-primary btn-sm">编辑</a>
                        <a href="/role/delete?id={{$item->id}}" class="btn btn-danger btn-sm">删除</a>
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
    </section>
@endsection