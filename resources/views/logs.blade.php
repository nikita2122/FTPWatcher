@extends('layouts.app')

@section('content')


    <section role="main" class="content-body">
        <!-- start: page -->
        <section class="panel">

            <div class="panel-body">
                <div class="pl-sm">
                    <h2>Logs</h2>
                    <div class="row mt-md m-none">
                        <div class="col-md-2 logs p-none">
                            @foreach($files as $file)
                                <div class="log text-md p-sm">
                                    <span class="icon icon-lg">
                                        <i class="fa fa-file"></i>
                                    </span>
                                    <a class="text-weight-bold ml-xs btn-logfile" href="/log/{{$file['name']}}">
                                        {{ $file['name'] }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-7">
                            <div class="logbox">
                                @if(count($contents) == 0)
                                    log content ...
                                @else
                                    @foreach($contents as $content)
                                        <span class="log-section">Local Time:</span>
                                        <span class="log-text">{{ $content['time'] }}</span> <br/>
                                        <span class="log-section text-danger">Hack Detect:</span>
                                        <span class="log-text">{{ $content['detect'] }}</span> <br/>
                                        <span class="log-section text-success">Full Path:</span>
                                        <span class="log-text">{{ $content['path'] }}</span> <br/>
                                        <span class="log-section text-warning">HardwareID:</span>
                                        <span class="log-text">{{ $content['hardware'] }}</span>
                                        @if(!$content['isbanned'])
                                            <button class="btn btn-default btn-sm ml-md btn-ban" data-hardware="{{$content['hardware']}}">
                                                Ban
                                            </button>
                                        @else
                                            <button class="btn btn-default btn-sm ml-md btn-un-ban" data-hardware="{{$content['hardware']}}">
                                                Un-Ban
                                            </button>
                                        @endif
                                        <br/>
                                        --------------------------------------------------- <br/>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 pt-sm bannedlist">
                            <span class="text-weight-bold text-md">Banned List</span>
                            <br/>
                            @foreach($banlist as $ban)
                                <div class="banitem">
                                    <span>{{$ban}}</span>
                                    <a class="btn-delete-ban mr-xs float-right " data-hardware="{{ $ban }}">
                                        <span class="icon icon-lg">
                                            <i class="fa fa-trash"></i>
                                        </span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </section>
        <!-- end: page -->
    </section>
@endsection

@section('script')
    <script type="text/javascript">
        (function($) {
            'use strict';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(".btn-ban").click(function() {
                var hardware = $(this).data('hardware');

                $.ajax({
                    url: '/ban/add',
                    method: 'POST',
                    data: {
                        hardware: hardware,
                    },
                    success: function(resp) {
                        if (resp == 'success') {
                            window.history.go();
                        } else {
                            toastr.warning("failed");
                        }
                    }
                });
            });


            $(".btn-un-ban").click(function() {
                var hardware = $(this).data('hardware');

                $.ajax({
                    url: '/ban/delete',
                    method: 'POST',
                    data: {
                        hardware: hardware,
                    },
                    success: function(resp) {
                        if (resp == 'success') {
                            window.history.go();
                        } else {
                            toastr.warning("failed");
                        }
                    }
                });
            });

            $(".btn-delete-ban").click(function() {
                var hardware = $(this).data('hardware');
                $.ajax({
                    url: '/ban/delete',
                    method: 'POST',
                    data: {
                        hardware: hardware,
                    },
                    success: function(resp) {
                        if (resp == 'success') {
                            window.history.go();
                        } else {
                            toastr.warning("failed");
                        }
                    }
                });
            });

        }).apply(this, [jQuery]);
    </script>
@endsection