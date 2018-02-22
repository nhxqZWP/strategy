@extends('layouts.layout')

@section('main_content')

    <section class="content">
        <div class="row">
            <div class="col-sm-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Admin Role</h3>
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
                                @foreach($rootModules as $module)
                                    <div class="checkbox">
                                        <label>{{$module->name}}</label>
                                        @foreach($module->children as $child)
                                        <label><input @if(in_array($child->id, $currentModIds)) checked @endif
                                            name="privileges[]" value="{{$child->id}}" type="checkbox">{{$child->name}}</label>
                                        @endforeach
                                    </div>
                                @endforeach
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