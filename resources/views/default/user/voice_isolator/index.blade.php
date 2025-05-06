@extends('layouts.app')
@section('css')
	<!-- Green Audio Players CSS -->
	<link href="{{ URL::asset('plugins/audio-player/green-audio-player.css') }}" rel="stylesheet" />
	<!-- Sweet Alert CSS -->
	<link href="{{URL::asset('plugins/sweetalert/sweetalert2.min.css')}}" rel="stylesheet" />
@endsection

@section('content')
	<div class="row mt-24 justify-content-center">

		<div class="row no-gutters justify-content-center">
			<div class="col-lg-9 col-md-11 col-sm-12 text-center">
				<h3 class="card-title mt-2 fs-20"><i class="fa-solid fa-record-vinyl mr-2 text-primary"></i></i>{{ __('Voice Isolator') }}</h3>
				<h6 class="text-muted mb-7">{{ __('Isolate voice from background noise with the help of AI') }}</h6>
			</div>
		</div>
		<div class="col-lg-5 col-md-5 col-sm-12">
			<div class="card border-0">
				<div class="card-header pt-4 border-0">
					<p class="fs-11 text-muted mb-0 text-left"><i class="   fa-solid fa-bolt-lightning mr-2 text-primary"></i>{{ __('Your Balance is') }} <span class="font-weight-semibold" id="balance-number">@if (auth()->user()->available_chars == -1) {{ __('Unlimited') }} @else {{ number_format(auth()->user()->available_chars + auth()->user()->available_chars_prepaid) }}@endif {{ __('characters') }}</span></p>
				</div>
				<form id="voice-form" action="" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="card-body pt-0 pl-6 pr-6 pb-5" id="">
	
						<div class="input-box" style="position: relative">
							<div id="image-drop-box">
								<div class="image-drop-area text-center mt-2 file-drop-border">
									<input type="file" class="main-image-input" name="audio" id="audio" accept=".mp3, .mp4, .ogg, .mpeg, .mpga, .m4a, .wav, .webm" required>
									<div class="image-upload-icon mt-2">
										<i class="fa-solid fa-waveform-lines fs-30 text-muted"></i>
									</div>
									<p class="text-dark fw-bold mb-3 mt-3">
										{{ __('Drag and drop your audio file or') }}
										<a href="javascript:void(0);" class="text-primary">{{ __('Browse') }}</a>
									</p>
									<p class="mb-5 file-name fs-12 text-muted">
										<small>{{ __('Audio Formats') }}: {{__('mp3, mp4, ogg, mpeg, mpga, m4a, wav and webm')}}</small><br>
										<small>{{ __('Max Audio File size: 500MB') }}</small>
									</p>
								</div>

								<img id="source-image-variations" class="mb-4">
							</div>
						</div>						

						<div class="text-center mt-4 mb-2">
							<button type="submit" class="btn btn-primary ripple main-action-button" id="generate" style="text-transform: none; min-width: 200px;">{{ __('Process') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>

		<div class="col-lg-6 col-md-8 col-xm-12">
			@if ($results->count())
				<div id="photo-studio-result">		
					<div class="row" id="results-container">
						@foreach ($results as $result)
							<div class="col-md-6 col-sm-12">
								<div class="card mb-5 border-0 p-4 avatar-voice-samples-box">
									<div class="d-flex avatar-voice-samples">
										<div class="flex">
											<button type="button" class="result-play text-center mr-2" title="{{__('Play Audio')}}" onclick="resultPlay(this)" src="{{ URL::asset($result->url) }}" id="{{ $result->id}}"><i class="fa fa-play table-action-buttons view-action-button"></i></button>											
										</div>
										<div class="flex mt-auto mb-auto">
											<p class="mb-2 font-weight-bold fs-12">{{ $result->file_name }}</p>
											<p class="mb-0 fs-11 text-muted">{{__('Cost')}} {{ $result->cost }} {{__('characters')}}</p>
										</div>
										<div class="btn-group dashboard-menu-button flex" style="top:1.4rem">
											<button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" id="export" data-bs-display="static" aria-expanded="false"><i class="fa-solid fa-ellipsis  table-action-buttons table-action-buttons-big edit-action-button" style="background: none"></i></button>
											<div class="dropdown-menu" aria-labelledby="export" data-popper-placement="bottom-start">								
												<a class="dropdown-item" href="{{ URL::asset($result->url) }}" download>{{ __('Download') }}</a>	
											</div>
										</div>											
									</div>							
								</div>
							</div>
						@endforeach
						
					</div>									
				</div>
			@else
				<div id="photo-studio-placeholder" class="text-center">
					<span><i class="fa-solid fa-waveform-lines fs-40 mb-4 text-muted"></i></span>
					<h6 class="text-muted">{{ __('Isolate voice from background noise') }}</h6>
				</div>
				<div id="photo-studio-result" class="hidden">		
					<div id="results-container">
						
					</div>									
				</div>
			@endif
			
		</div>	

	</div>
</div>
@endsection
@section('js')
	<script src="{{URL::asset('plugins/sweetalert/sweetalert2.all.min.js')}}"></script>
	<script src="{{ URL::asset('plugins/audio-player/green-audio-player.js') }}"></script>
	<script src="{{ theme_url('js/audio-player.js') }}"></script>
	<script type="text/javascript">
		let loading = `<span class="loading">
						<span style="background-color: #fff;"></span>
						<span style="background-color: #fff;"></span>
						<span style="background-color: #fff;"></span>
						</span>`;
		

		// SUBMIT FORM
		$('#voice-form').on('submit', function(e) {

			e.preventDefault();

			let form = new FormData(this);

			$.ajax({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				method: 'POST',
				url: '/app/user/voice-isolator/create',
				data: form,
				contentType: false,
				processData: false,
				cache: false,
				beforeSend: function() {
					$('#generate').prop('disabled', true);
					let btn = document.getElementById('generate');					
					btn.innerHTML = loading;  
					document.querySelector('#loader-line')?.classList?.remove('hidden');      
				},
				complete: function() {
					document.querySelector('#loader-line')?.classList?.add('hidden'); 
					$('#generate').prop('disabled', false);
					$('#generate').html('{{ __("Process") }}');            
				},
				success: function (data) {		
						
					if (data['status'] == 'success') {		

						$('#photo-studio-placeholder').addClass('hidden');
						$('#photo-studio-result').removeClass('hidden');

						$('#results-container').prepend(data['result']);

						toastr.success(data['message']);	
						
					} else {						
						Swal.fire('{{ __('Video Generation Error') }}', data['message'], 'warning');
					}
				},
				error: function(data) {
					$('#generate').prop('disabled', false);
					$('#generate').html('{{ __("Process") }}'); 
				}
			});
		});


		$(document).on('click', '.result-delete', function(e) {

			e.preventDefault();

			let formData = new FormData();
			formData.append("id", $(this).attr('data-id'));
			$.ajax({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				method: 'post',
				url: '/app/user/voice-isolator/delete',
				data: formData,
				processData: false,
				contentType: false,
				success: function (data) {
					console.log(data)
					if (data == 200) {
						toastr.success('{{ __('Result has been deleted successfully') }}');	
						window.location.reload();							
					} else {
						toastr.warning('{{ __('There was an error deleting the result file') }}');
					}      
				},
				error: function(data) {
					toastr.warning('{{ __('There was an error deleting the result file') }}');
				}
			})
		});

		
	</script>
@endsection