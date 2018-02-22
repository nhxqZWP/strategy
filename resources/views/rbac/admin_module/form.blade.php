@extends('layouts.layout')

@section('main_content')

    <section class="content">
        <div class="row">
            <div class="col-sm-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Admin Module</h3>
                    </div>
                    @include('layouts.form_errors')
                    <!-- form start -->
                    <form role="form" method="POST" action="">
                        <div class="box-body">
                            <input name="id" type="hidden" value="{{$item->id}}" />
                            <div class="form-group">
                                <label for="exampleInputEmail1">Name</label>
                                <input type="text" name="name" value="{{$item->name}}" class="form-control" id="" placeholder="">
                            </div>
                            <div class="form-group">
                                <label>Parent Module</label>
                                <select name="parent_id" class="form-control select2" style="width: 100%;">
                                    <option value="0">Root Module</option>
                                    @foreach($rootModules as $module)
                                        <option @if($module->id == $item->parent_id) selected @endif
                                        value="{{$module->id}}">{{$module->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Allow Controls</label>
                                <textarea class="form-control" name="priv_list" cols="40" rows="5" >{{preg_replace('/[,]/', "\n",$item->priv_list)}}</textarea>
                                如:Article.getList，一行一条记录
                            </div>

                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>

@endsection