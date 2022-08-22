{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')

<div class="container-fluid">
	<div class="form-head mb-sm-5 mb-3 d-flex flex-wrap align-items-center">
		<h2 class="font-w600 title mb-2 me-auto ">{{__('locale.global_user_list')}}</h2>
		<div class="weather-btn mb-2">
			<span class="me-3 font-w600 text-black"><i class="fa fa-cloud me-2"></i>21</span>
			<select class="form-control style-1 default-select  me-3 ">
				<option>Medan, IDN</option>
				<option>Jakarta, IDN</option>
				<option>Surabaya, IDN</option>
			</select>
		</div>
		<a href="javascript:void(0);" class="btn btn-secondary mb-2"><i class="las la-calendar scale5 me-3"></i>Filter Periode</a>
	</div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{__('locale.global_user_list')}}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example7" class="display" style="min-width: 845px">
                            <thead>
                                <tr>
                                    <th>{{__('locale.global_user_id')}}</th>
                                    <th>{{__('locale.global_user_email')}}</th>
                                    <th>{{__('locale.global_user_first_name')}}</th>
                                    <th>{{__('locale.global_user_last_name')}}</th>
                                    <th>{{__('locale.global_user_type_of_user')}}</th>
                                    <th>{{__('locale.global_user_buy_weight')}}</th>
                                    <th>{{__('locale.global_user_amount_allow_to_buy')}}</th>
                                    <th>{{__('locale.global_user_sell_weight')}}</th>
                                    <th>{{__('locale.global_user_amount_allow_to_sell')}}</th>
                                    <th>{{__('locale.global_user_cold_storage_address')}}</th>
                                    <th>{{__('locale.global_user_cold_wallet_address')}}</th>
                                    <th>{{__('locale.global_user_see_for_all_trading_pairs')}}</th>
                                    <th>{{__('locale.global_user_selected_exchanges_they_can_trade_on')}}</th>
                                    <th>{{__('locale.global_user_status')}}</th>
                                    <th>{{__('locale.global_user_history')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result as $key => $value)
								<tr>
									<td>{{$value['id']}}</td>
									<td>{{$value['email']}}</td>
									<td>{{$value['f_name']}}</td>
									<td>{{$value['l_name']}}</td>
									<td>{{$value['user_type']}}</td>
									<td>{{$value['buy_weight']}}</td>
									<td>{{$value['amount_allow_to_buy']}}</td>
									<td>{{$value['sell_weight']}}</td>
									<td>{{$value['amount_allow_to_sell']}}</td>
									<td>{{$value['cold_storage_address']}}</td>
									<td>{{$value['wallet_address']}}</td>
									<td>{{$value['set_for_all_trading_pairs']}}</td>
									<td>{{$value['selected_exchange_tehy_can_trade_on']}}</td>
									<td>{{$value['status']}}</td>
									<td>List</td>
								</tr>
								@endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection	

{{-- Scripts --}}
@section('scripts')
	<script>
		jQuery(document).ready(function(){
			dezSettingsOptions.version = 'dark';
			setTimeout(function() {
				dezSettingsOptions.version = 'dark';
				new dezSettings(dezSettingsOptions);
			}, 1500)
		});
	</script>
@endsection	