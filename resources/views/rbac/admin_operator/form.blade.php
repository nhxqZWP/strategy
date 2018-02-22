@extends('layouts.layout')

@section('main_content')

    <section class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">@if(empty($item->id)) 新建 @else 编辑 @endif 操作员</h3>
                    </div>
                    @include('layouts.form_errors')
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form role="form" method="POST" action="" class="form-horizontal">
                        <div class="box-body">
                            <input name="id" type="hidden" value="{{$item->id}}" />
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" value="{{$item->name}}" class=" form-control" id="" placeholder="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Password</label>
                                <div class="col-sm-10">
                                <input type="password" name="password" class="form-control ">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Password 2</label>
                                <div class="col-sm-10">
                                <input type="password" name="password2" class="form-control ">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Role</label>
                                <div class="col-sm-10">
                                    <select name="role_id" class="form-control select2 " style="width: 100%;">
                                        @foreach($roleList as $role)
                                            <option value="{{$role->id}}">{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right">提交</button>
                            <a href="/operator" class="btn pull-right">返回列表</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection