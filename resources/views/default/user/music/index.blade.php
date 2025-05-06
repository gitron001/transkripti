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
				<h3 class="card-title mt-2 fs-20"><i class="fa-solid fa-list-music mr-2 text-primary"></i></i>{{ __('AI Music') }}</h3>
				<h6 class="text-muted mb-7">{{ __('Create your own music and any audio experience from your prompt and imagination') }}</h6>
			</div>
		</div>
		<div class="col-lg-5 col-md-5 col-sm-12">
			<div class="card border-0">
				<div class="card-header pt-4 border-0">
					<p class="fs-11 text-muted mb-0 text-left"><i class="fa-solid fa-bolt-lightning mr-2 text-primary"></i>{{ __('Your Balance is') }} <span class="font-weight-semibold" id="balance-number">@if (auth()->user()->image_credits == -1) {{ __('Unlimited') }} @else {{ number_format(auth()->user()->image_credits + auth()->user()->image_credits_prepaid) }}@endif {{ __('media credits') }}</span></p>
				</div>
				<form id="music-form" action="" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="card-body pt-1 pl-6 pr-6 pb-5" id="">

						
						<div class="row">
							<div class="col-sm-12">								
								<div class="input-box">								
									<h6 class="text-muted">{{ __('Title') }}</h6>
									<div class="form-group">							    
										<input type="text" class="form-control" id="title" name="title" value="{{__('Untitled Music')}}">
									</div> 
								</div> 
							</div>
							<div class="col-sm-12">	
								<div class="input-box">	
									<h6 class="text-muted">{{ __('Prompt') }} <span class="text-required"><i class="fa-solid fa-asterisk"></i></span></h6>							
									<textarea class="form-control" name="prompt" rows="5" id="prompt" placeholder="{{ __('Provide your music description...') }}" required></textarea>	
								</div>											
							</div>	
						</div>
	
						<div class="photo-studio-tools mb-5">
							<div class="nav-item dropdown w-100">
								<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
									<span class="dropdown-item-icon mr-3 ml-1" id="active-template-icon"><i class="fa-solid fa-music"></i></span>
									<h6 class="dropdown-item-title fs-13 font-weight-semibold" id="active-template-name">{{ __('Stable Audio') }}</h6>	
								</a>
								<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">										
									<a class="dropdown-item d-flex" href="#"  id="stable-audio" name="{{ __('Stable Audio') }}" icon="<i class='fa-solid fa-circle-video'></i>">
										<span class="dropdown-item-icon mr-3 ml-1 text-muted"><i class="fa-solid fa-music"></i></span>
										<h6 class="dropdown-item-title fs-12">{{ __('Stable Audio') }} <span class="fs-9 text-muted">({{ $credits->music_stable }} {{ __('credits per music') }})</span></h6>										
									</a>	
									<a class="dropdown-item d-flex" href="#"  id="minimax-music" name="{{ __('Minimax Music') }}" icon="<i class='fa-solid fa-circle-video'></i>">
										<span class="dropdown-item-icon mr-3 ml-1 text-muted"><i class="fa-solid fa-music"></i></span>
										<h6 class="dropdown-item-title fs-12">{{ __('Minimax Music') }} <span class="fs-9 text-muted">({{ $credits->music_minimax }} {{ __('credits per music') }})</span></h6>										
									</a>	
								</div>
							</div>
						</div>

						<div class="row" id="stable-audio-task">
							<div class="col-sm-12">								
								<div class="input-box">								
									<h6 class="text-muted">{{ __('Total Seconds') }} <i class="ml-1 text-dark fs-13 fa-solid fa-circle-info" data-tippy-content="{{ __('The duration of the audio clip to generate') }}."></i></h6>
									<div class="form-group">							    
										<input type="number" class="form-control" id="seconds_total" name="seconds_total" value="10">
									</div> 
								</div> 
							</div>
							<div class="col-sm-12">								
								<div class="input-box">								
									<h6 class="text-muted">{{ __('Steps') }} <i class="ml-1 text-dark fs-13 fa-solid fa-circle-info" data-tippy-content="{{ __('The number of steps to denoise the audio') }}."></i></h6>
									<div class="form-group">							    
										<input type="number" class="form-control" id="steps" name="steps" value="50">
									</div> 
								</div> 
							</div>
						</div>

						<div class="row hidden" id="minimax-music-task">						
							<div class="input-box" style="position: relative">
								<h6 class="text-muted">{{ __('Reference Audio') }} <i class="ml-1 text-dark fs-13 fa-solid fa-circle-info" data-tippy-content="{{ __('Reference song, should contain music and vocals. Must be a .wav or .mp3 file longer than 15 seconds') }}."></i></h6>
								<div id="image-drop-box">
									<div class="image-drop-area text-center mt-2 file-drop-border">
										<input type="file" class="main-image-input" name="reference_audio" id="reference_audio" accept=".mp3, .wav">
										<div class="image-upload-icon mt-2">
											<i class="fa-solid fa-waveform-lines fs-30 text-muted"></i>
										</div>
										<p class="text-dark fw-bold mb-3 mt-3">
											{{ __('Drag and drop your reference audio file or') }}
											<a href="javascript:void(0);" class="text-primary">{{ __('Browse') }}</a>
										</p>
										<p class="mb-5 file-name fs-12 text-muted">
											<small>{{ __('Audio Formats') }}: {{__('mp3, wav')}}</small><br>
										</p>
									</div>
								</div>
							</div>	
						</div>
						

						<div class="text-center mt-4 mb-2">
							<button type="submit" class="btn btn-primary ripple main-action-button" id="generate" style="text-transform: none; min-width: 200px;">{{ __('Generate') }}</button>
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
											<button type="button" class="result-play text-center mr-2" title="{{__('Play Audio')}}" onclick="resultPlay(this)" src="{{ URL::asset('storage/' . $result->result_url) }}" id="{{ $result->id}}"><i class="fa fa-play table-action-buttons view-action-button"></i></button>											
										</div>
										<div class="flex mt-auto mb-auto">
											<p class="mb-2 font-weight-bold fs-12">{{ $result->title }}</p>
										</div>
										<div class="btn-group dashboard-menu-button flex" style="top:1.4rem">
											<button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" id="export" data-bs-display="static" aria-expanded="false"><i class="fa-solid fa-ellipsis  table-action-buttons table-action-buttons-big edit-action-button" style="background: none"></i></button>
											<div class="dropdown-menu" aria-labelledby="export" data-popper-placement="bottom-start">								
												<a class="dropdown-item" href="{{ URL::asset('storage/' . $result->result_url) }}" download><i class="fa-solid fa-download mr-2"></i>{{ __('Download') }}</a>	
												<a class="dropdown-item result-delete" href="#" data-id="{{$result->id}}"><i class="fa-solid fa-trash-xmark mr-2"></i>{{ __('Delete') }}</a>	
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
					<h6 class="text-muted">{{ __('Generate your next music') }}</h6>
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
	let active_task = 'stable-audio';
		let loading = `<span class="loading">
						<span style="background-color: #fff;"></span>
						<span style="background-color: #fff;"></span>
						<span style="background-color: #fff;"></span>
						</span>`;


		$('.photo-studio-tools .dropdown .dropdown-menu .dropdown-item').click(function(e){
			e.preventDefault();

			let task = $(this).attr('id');
			let name = $(this).attr('name');
			let icon = $(this).attr('icon');
			let template_icon = document.getElementById('active-template-icon');
			let template_name = document.getElementById('active-template-name');
			active_task = task;
			template_name.innerHTML = name;
			template_icon.innerHTML = icon;

			if (task == 'stable-audio') {
				$('#stable-audio-task').removeClass('hidden');
				$('#minimax-music-task').addClass('hidden');
			}

			if (task == 'minimax-music') {
				$('#stable-audio-task').addClass('hidden');
				$('#minimax-music-task').removeClass('hidden');
			} 

		});
		

		// SUBMIT FORM
		$('#music-form').on('submit', function(e) {

			e.preventDefault();

			let form = new FormData(this);
			form.append('model', active_task);

			$.ajax({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				method: 'POST',
				url: '/app/user/music/create',
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
					$('#generate').html('{{ __("Generate") }}');            
				},
				success: function (data) {		
						
					if (data['status'] == 'success') {		

						$('#photo-studio-placeholder').addClass('hidden');
						$('#photo-studio-result').removeClass('hidden');

						$('#results-container').prepend(data['result']);

						toastr.success(data['message']);	
						
					} else {						
						Swal.fire('{{ __('Music Generation Error') }}', data['message'], 'warning');
					}
				},
				error: function(data) {
					$('#generate').prop('disabled', false);
					$('#generate').html('{{ __("Generate") }}'); 
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
				url: '/app/user/music/delete',
				data: formData,
				processData: false,
				contentType: false,
				success: function (data) {
					console.log(data)
					if (data == 200) {
						toastr.success('{{ __('Result has been deleted successfully') }}');	
						window.location.reload();							
					} else {
						toastr.warning('{{ __('There was an error deleting the result') }}');
					}      
				},
				error: function(data) {
					toastr.warning('{{ __('There was an error deleting the result') }}');
				}
			})
		});

		
	</script>
@endsection