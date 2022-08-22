{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')

<div class="container-fluid">
	<div class="form-head mb-sm-5 mb-3 d-flex flex-wrap align-items-center">
		<h2 class="font-w600 title mb-2 me-auto ">{{__('locale.adminwalletlist')}}</h2>
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
                    <h4 class="card-title">{{__('locale.adminwalletlist')}}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example7" class="display" style="min-width: 845px">
                            <thead>
                                <tr>
                                    <th>{{__('locale.wallet_list_id')}}</th>
                                    <th>{{__('locale.wallet_list_internal_treasury_account')}}</th>
                                    <th>{{__('locale.wallet_list_type')}}</th>
                                    <th>{{__('locale.wallet_list_chainstack')}}</th>
                                    <th>{{__('locale.wallet_list_internal_wallet_address')}}</th>
                                    <th>{{__('locale.wallet_list_cold_storage_address')}}</th>
                                    <th>{{__('locale.wallet_list_market_price')}}</th>
                                    <th>{{__('locale.wallet_list_withdraw')}}</th>
                                    <th>{{__('locale.wallet_list_in_house_balance')}}</th>
                                    <th>{{__('locale.wallet_list_cold_storage_balance')}}</th>
                                    <th>{{__('locale.wallet_list_history')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($internal_wallet as $key => $value)
                                <tr>
                                    <td>{{$value['id']}}</td>
									<td>{{$value['internal_treasury_account']}}</td>
									<td>{{$value['wallet_type']}}</td>
									<td>{{$value['wallet_type']}}</td>
									<td>{{$value['address']}}</td>
									<td><a href="/admin/internal_wallet/setup_cold_storage_address/{{$value['id']}}">{{$value['cold_storage_address']}}</a></td>
									<td>25775</td>
									<td><a href="/admin/internal_wallet/withdraw/{{$value['id']}}">Withdraw</a></td>
									<td>25</td>
									<td>{{$value['cold_storage_balance']}}</td>
									<td><a href="/admin/internal_wallet/history/{{$value['id']}}">History</a></td>
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