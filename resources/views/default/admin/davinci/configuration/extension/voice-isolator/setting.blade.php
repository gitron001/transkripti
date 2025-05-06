@extends('layouts.app')

@section('page-header')
	<!-- PAGE HEADER -->
	<div class="page-header mt-5-7 justify-content-center">
		<div class="page-leftheader text-center">
			<h4 class="page-title mb-0">{{ __('Voice Isolator') }}</h4>
			<ol class="breadcrumb mb-2 justify-content-center">
				<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-microchip-ai mr-2 fs-12"></i>{{ __('Admin') }}</a></li>
				<li class="breadcrumb-item" aria-current="page"><a href="{{ route('admin.davinci.configs')}}"> {{ __('AI Settings') }}</a></li>
				<li class="breadcrumb-item active" aria-current="page"><a href="#"> {{ __('Extensions') }}</a></li>
			</ol>
		</div>
	</div>
	<!-- END PAGE HEADER -->
@endsection

@section('content')						
	<div class="row justify-content-center">
		<div class="col-lg-6 col-md-12 col-sm-12">
			<div class="card border-0">
				<div class="card-body pt-7 pl-7 pr-7 pb-6">									
					<form action="{{ route('admin.davinci.configs.voice.isolator.store') }}" method="post" enctype="multipart/form-data">
						@csrf

						<div class="card shadow-0 mb-7">							
							<div class="card-body">
								<div class="row">
									<div class="col-sm-12">
										<div class="input-box mb-3">								
											<h6>{{ __('Elevenlabs API Key') }} <span class="text-required"><i class="fa-solid fa-asterisk"></i></span></h6>
											<div class="form-group">							    
												<input type="text" class="form-control" id="voice_isolator_elevenlabs_api" name="voice_isolator_elevenlabs_api" value="{{ $extension->voice_isolator_elevenlabs_api }}" autocomplete="off">
											</div> 												
										</div> 
									</div>
								</div>
											
							</div>
						</div>
						
						<div class="card shadow-0 mt-0 mb-6 pt-3">							
							<div class="card-body">
								<div class="row">

									<div class="col-lg-6 col-md-6 col-sm-12">
										<div class="input-box mb-3">
											<h6>{{ __('Voice Isolator Feature') }}</h6>
											<div class="form-group mt-3">
												<label class="custom-switch">
													<input type="checkbox" name="voice_isolator_feature" class="custom-switch-input" @if ($extension->voice_isolator_feature) checked @endif>
													<span class="custom-switch-indicator"></span>
												</label>
											</div>
										</div>
									</div>

									<div class="col-lg-6 col-md-6 col-sm-12">
										<div class="input-box mb-3">
											<h6>{{ __('Voice Isolator Free Tier Access') }}</h6>
											<div class="form-group mt-3">
												<label class="custom-switch">
													<input type="checkbox" name="voice_isolator_free_tier" class="custom-switch-input" @if ($extension->voice_isolator_free_tier) checked @endif>
													<span class="custom-switch-indicator"></span>
												</label>
											</div>
										</div>
									</div>	
								</div>		
							</div>
						</div>

						<!-- ACTION BUTTON -->
						<div class="border-0 text-center mb-2 mt-1">
							<button type="submit" class="btn ripple btn-primary pl-8 pr-8 pt-2 pb-2">{{ __('Save') }}</button>							
						</div>				

					</form>					
				</div>
			</div>
		</div>
	</div>
@endsection


