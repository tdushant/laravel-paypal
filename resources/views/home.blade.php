@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div>
                        {{ __('You are logged in!') }}
                    </div>

                    Your wallet balance is $ {{( $balance->balance)? $balance->balance : 0; }}
                    <br>
                    
                    <input type="number" id="amount">
                    <a class="btn btn-primary m-3" href="javascript:void(0)" id="addfund"> Add Fund</a>
    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready( function(){
        $("#addfund").on("click", function(){
            var amount = $("#amount").val();
            var token = '<?php echo csrf_token() ?>';
            $.ajax({
               type:'POST',
               url:'/process-transaction',
               data:{_token:token, return_url: 'http://127.0.0.1:8000/create-transaction', cancel_url:'http://127.0.0.1:8000/cancel-transaction', amount:amount },
               success:function(data) {
                    console.log(data.links);
                    if (data.id !== undefined ) {
                        $.each(data.links, function(key,val) {
                            if(val.rel == 'approve'){
                                window.location.href = val.href;
                            }
                        });

                    }
                }
            });
        });
    });
</script>
