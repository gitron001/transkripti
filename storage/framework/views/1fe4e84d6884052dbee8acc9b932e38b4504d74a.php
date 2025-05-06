

<?php $__env->startSection('css'); ?>
	<!-- Sweet Alert CSS -->
	<link href="<?php echo e(URL::asset('plugins/sweetalert/sweetalert2.min.css')); ?>" rel="stylesheet" />
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-header'); ?>
	<!-- PAGE HEADER -->
	<div class="page-header mt-5-7">
		<div class="page-leftheader">
			<h4 class="page-title mb-0"><?php echo e(__('Dashboard')); ?></h4>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?php echo e(route('user.dashboard')); ?>"><i class="fa-solid fa-chart-tree-map mr-2 fs-12"></i><?php echo e(__('AI Panel')); ?></a></li>
				<li class="breadcrumb-item active" aria-current="page"><a href="<?php echo e(url('#')); ?>"> <?php echo e(__('Dashboard')); ?></a></li>
			</ol>
		</div>
	</div>
	<!-- END PAGE HEADER -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
	<!-- USER PROFILE PAGE -->
	<div class="row">
		<div class="<?php if(App\Services\HelperService::extensionSaaS()): ?> col-lg-5 <?php else: ?> col-lg <?php endif; ?> col-md-12">
			<div class="card border-0">
				<div class="card-body pt-4 pb-4 pl-6 pr-6 custom-banner-bg" <?php if(!App\Services\HelperService::extensionSaaS()): ?> style="height: 165px" <?php endif; ?>>
					<div class="custom-banner-bg-image"></div>
					<div class="row">
						<div class="col-md-8 col-sm-12">
							<span class="fs-10"><i class="fa-solid fa-calendar mr-2"></i> <?php echo e(now()->format('M d, Y H:i A')); ?></span>
							<h4 class="mb-4 mt-2 font-weight-800 fs-24"><?php echo e(__('Welcome')); ?>, <?php echo e(auth()->user()->name); ?></h4>
							<?php if(App\Services\HelperService::extensionSaaS()): ?>
								<span class="fs-10 custom-span"><?php echo e(__('Current Plan')); ?></span>
								<?php if(is_null(auth()->user()->plan_id)): ?>
									<h4 class="mb-2 mt-2 font-weight-800 fs-24"><?php echo e(__('No Active Plan')); ?></h4>						
									<h6 class="fs-12"><?php echo e(__('You do not have an active subscription')); ?></h6>
								<?php else: ?>
									<h4 class="mb-2 mt-2 font-weight-800 fs-24"><?php echo e($subscription); ?> <?php echo e(__('Plan')); ?></h4>
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<?php if(App\Services\HelperService::extensionSaaS()): ?>
							<div class="col-md-4 col-sm-12 d-flex align-items-end justify-content-end">
								<div class="text-center">
									<?php if(!is_null($price)): ?>
										<?php if($term == 'lifetime'): ?>
											<h4 class="mb-2 mt-2 font-weight-bold fs-18"><?php echo e(__('Lifetime Plan')); ?></h4>		
										<?php else: ?>
											<h4 class="mb-2 mt-2 font-weight-bold fs-18"><?php echo config('payment.default_system_currency_symbol'); ?><?php echo e($price); ?> / <?php if($term == 'monthly'): ?><?php echo e(__('month')); ?> <?php else: ?> <?php echo e(__('year')); ?> <?php endif; ?></h4>		
										<?php endif; ?>									
									<?php else: ?>
										<h4 class="mb-2 mt-2 font-weight-bold fs-18"><?php echo config('payment.default_system_currency_symbol'); ?>0 / <?php echo e(__('month')); ?></h4>
									<?php endif; ?>								
									<a href="<?php echo e(route('user.plans')); ?>" class="btn btn-primary yellow mt-2 custom-pricing-plan-button mb-2"><?php echo e(__('See Pricing Plans')); ?> <i class="fa-regular fa-chevron-right fs-8 ml-1"></i></a>
								</div>								
							</div>
						<?php endif; ?>
					</div>
					
					
					
				</div>
			</div>
		</div>

		<?php if(App\Services\HelperService::extensionSaaS()): ?>
			<?php if(config('payment.referral.enabled') == 'on'): ?>
				<div class="col-lg col-md-12 d-flex align-items-stretch">
					<div class="card border-0">
						<div class="card-body p-6 align-items-center">
							<div class="row" style="height: 100%">
								<div class="col-md-6 col-sm-12 text-left mt-auto">
									<h6 class="fs-14 text-muted"><i class="fa-solid fa-badge-dollar mr-2"></i><?php echo e(__('Your balance')); ?></h6>
									<h4 class="mt-4 mb-5 font-weight-bold text-muted fs-30"><?php echo config('payment.default_system_currency_symbol'); ?><?php echo e(number_format(auth()->user()->balance)); ?></h4>
									<h6 class="fs-14 text-muted"><?php echo e(__('Current referral earnings')); ?></h6>	
								</div>
								<div class="col-md-6 col-sm-12 d-flex align-items-end justify-content-end">
									<a href="<?php echo e(route('user.referral')); ?>" class="btn btn-primary yellow mt-2 mb-0 pl-6 pr-6" style="text-transform: none;"><?php echo e(__('Invite & Earn')); ?> <i class="fa-regular fa-chevron-right fs-8 ml-1"></i></a>
								</div>
							</div>		
						</div>
					</div>
				</div>
			<?php endif; ?>		
		<?php endif; ?>		

		<!-- <div class="col-lg col-md-12 d-flex align-items-stretch">
			<div class="card border-0">
				<div class="card-body pr-0 pb-0">
					<div class="row" style="height: 100%">
						<div class="col-md-6 col-sm-12 justify-content-center mt-auto mb-auto">
							<h6 class="fs-14 text-muted"><i class="fa-solid fa-clock mr-2"></i><?php echo e(__('Time Saved')); ?></h6>
							<h4 class="mt-4 mb-5 font-weight-bold text-muted fs-30"><?php echo e(number_format($total_words)); ?></h4>
							<h6 class="fs-14 text-muted"><?php echo e(__('Total hours you saved')); ?></h6>
						</div>
						<div class="col-md-6 col-sm-12 d-flex align-items-end justify-content-end" style="margin-bottom: -5px">
							<canvas id="hoursSavedChart" style="max-height: 130px"></canvas>
						</div>
					</div>					
				</div>
			</div>
		</div> -->
	</div>

	<div class="row">

		<div class="col-lg-12 col-md-12 mt-3">
			<div class="card border-0">
				<div class="card-body pt-5 pb-5 pl-6 pr-6">
					<div class="row text-center mb-4">
						<div class="col-lg col-md-6 col-sm-6 dashboard-border-right mt-auto mb-auto">
							<h6 class="fs-12 mt-3 font-weight-bold"><?php if($configs->model_credit_name == 'words'): ?> <?php echo e(__('Words Left')); ?> <?php else: ?> <?php echo e(__('Tokens Left')); ?> <?php endif; ?></h6>
							<h4 class="mb-0 font-weight-800 text-primary fs-20"><?php if(auth()->user()->tokens == -1): ?> <?php echo e(__('Unlimited')); ?> <?php else: ?> <?php echo e(number_format(auth()->user()->tokens + auth()->user()->tokens_prepaid)); ?> <?php endif; ?></h4>
							<div class="view-credits mb-3"><a class=" fs-11 text-muted" href="javascript:void(0)" id="view-credits" data-bs-toggle="modal" data-bs-target="#creditsModel"> <?php echo e(__('View All Credits')); ?></a></div> 										
						</div>
						<?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'user|subscriber|admin')): ?>
							<?php if(config('settings.image_feature_user') == 'allow'): ?>
								<!-- <div class="col-lg col-md-6 col-sm-6 dashboard-border-right mt-auto mb-auto">
									<h6 class="fs-12 mt-3 font-weight-bold"><?php echo e(__('Media Credits Left')); ?></h6>
									<h4 class="mb-3 font-weight-800 text-primary fs-20"><?php if(auth()->user()->images == -1): ?> <?php echo e(__('Unlimited')); ?> <?php else: ?> <?php echo e(number_format(auth()->user()->images + auth()->user()->images_prepaid)); ?> <?php endif; ?></h4>										
								</div>	 -->
							<?php endif; ?>
						<?php endif; ?>	
						<?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'user|subscriber|admin')): ?>
							<?php if(config('settings.voiceover_feature_user') == 'allow'): ?>				
								<div class="col-lg col-md-6 col-sm-6 dashboard-border-right mt-auto mb-auto">
									<h6 class="fs-12 mt-3 font-weight-bold"><?php echo e(__('Characters Left')); ?></h6>
									<h4 class="mb-3 font-weight-800 text-primary fs-20"><?php if(auth()->user()->characters == -1): ?> <?php echo e(__('Unlimited')); ?> <?php else: ?> <?php echo e(number_format(auth()->user()->characters + auth()->user()->characters_prepaid)); ?> <?php endif; ?></h4>										
								</div>
							<?php endif; ?>
						<?php endif; ?>
						<?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'user|subscriber|admin')): ?>
							<?php if(config('settings.whisper_feature_user') == 'allow'): ?>
								<div class="col-lg col-md-6 col-sm-6 mt-auto mb-auto">
									<h6 class="fs-12 mt-3 font-weight-bold"><?php echo e(__('Minutes Left')); ?></h6>
									<h4 class="mb-3 font-weight-800 text-primary fs-20"><?php if(auth()->user()->minutes == -1): ?> <?php echo e(__('Unlimited')); ?> <?php else: ?> <?php echo e(number_format(auth()->user()->minutes + auth()->user()->minutes_prepaid)); ?> <?php endif; ?></h4>										
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<div class="row mb-6">
						<div class="col-md-12">
							<h6 class="fs-12 font-weight-semibold text-muted"><?php echo e(__('Your Documents')); ?></h6>
							<div class="progress">
								<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo e($content_documents * 100); ?>%; border-top-left-radius: 10px; border-bottom-left-radius: 10px" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
								<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: <?php echo e($content_images * 100); ?>%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
								<div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: <?php echo e($content_voiceovers * 100); ?>%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
								<div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: <?php echo e($content_transcripts * 100); ?>%; border-top-right-radius: 10px; border-bottom-right-radius: 10px" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
							  </div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-lg col-md-4 col-sm-12">
							<div class="card overflow-hidden user-dashboard-special-box">
								<div class="card-body d-flex">
									<div class="usage-info w-100">
										<p class=" mb-3 fs-12 font-weight-bold"><?php echo e(__('Words Generated')); ?></p>
										<h2 class="mb-2 fs-14 font-weight-semibold text-muted"><?php echo e(number_format($data['words'])); ?> <span class="text-muted fs-14"><?php echo e(__('words')); ?></span></h2>
									</div>
									<div class="usage-icon-dashboard text-muted text-right">
										<i class="fa-solid fa-microchip-ai"></i>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg col-md-4 col-sm-12">
							<div class="card overflow-hidden user-dashboard-special-box">
								<div class="card-body d-flex">
									<div class="usage-info w-100">
										<p class=" mb-3 fs-12 font-weight-bold"><?php echo e(__('Documents Saved')); ?></p>
										<h2 class="mb-2 fs-14 font-weight-semibold text-muted"><?php echo e(number_format($data['documents'])); ?> <span class="text-muted fs-14"><?php echo e(__('documents')); ?></span></h2>
									</div>
									<div class="usage-icon-dashboard text-primary text-right">
										<i class="fa-solid fa-folder-grid"></i>
									</div>
								</div>
							</div>
						</div>						
						<?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'user|subscriber|admin')): ?>
                    		<?php if(config('settings.image_feature_user') == 'allow'): ?>
								<!-- <div class="col-lg col-md-4 col-sm-12">
									<div class="card overflow-hidden user-dashboard-special-box">
										<div class="card-body d-flex">
											<div class="usage-info w-100">
												<p class=" mb-3 fs-12 font-weight-bold"><?php echo e(__('Images Created')); ?></p>
												<h2 class="mb-2 fs-14 font-weight-semibold text-muted"><?php echo e(number_format($data['images'])); ?> <span class="text-muted fs-14"><?php echo e(__('images')); ?></span></h2>
											</div>
											<div class="usage-icon-dashboard text-success text-right">
												<i class="fa-solid fa-image-landscape"></i>
											</div>
										</div>
									</div>
								</div> -->
							<?php endif; ?>
						<?php endif; ?>
						<?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'user|subscriber|admin')): ?>
                    		<?php if(config('settings.voiceover_feature_user') == 'allow'): ?>
								<!-- <div class="col-lg col-md-4 col-sm-12">
									<div class="card overflow-hidden user-dashboard-special-box">
										<div class="card-body d-flex">
											<div class="usage-info w-100">
												<p class=" mb-3 fs-12 font-weight-bold"><?php echo e(__('Voiceover Tasks')); ?></p>
												<h2 class="mb-2 fs-14 font-weight-semibold text-muted"><?php echo e(number_format($data['synthesized'])); ?> <span class="text-muted fs-14"><?php echo e(__('tasks')); ?></span></h2>
											</div>
											<div class="usage-icon-dashboard text-yellow text-right">
												<i class="fa-solid fa-waveform-lines"></i>
											</div>
										</div>
									</div>
								</div> -->
							<?php endif; ?>
						<?php endif; ?>
						<?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'user|subscriber|admin')): ?>
                    		<?php if(config('settings.whisper_feature_user') == 'allow'): ?>
								<div class="col-lg col-md-4 col-sm-12">
									<div class="card overflow-hidden user-dashboard-special-box">
										<div class="card-body d-flex">
											<div class="usage-info w-100">
												<p class=" mb-3 fs-12 font-weight-bold"><?php echo e(__('Audio Transcribed')); ?></p>
												<h2 class="mb-2 fs-14 font-weight-semibold text-muted"><?php echo e(number_format($data['transcribed'])); ?> <span class="text-muted fs-14"><?php echo e(__('audio files')); ?></span></h2>
											</div>
											<div class="usage-icon-dashboard text-danger text-right">
												<i class="   fa-solid fa-folder-music"></i>
											</div>
										</div>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- <div class="col-lg col-md-12 col-sm-12 mt-3">
			<div class="card border-0 pb-5" id="user-dashboard-panels">
				<div class="card-header pt-4 pb-4 border-0">
					<div class="mt-3">
						<h3 class="card-title mb-2"><i class="fa-solid fa-message-captions mr-2 text-muted"></i><?php echo e(__('Favorite AI Chat Assistants')); ?></h3>
						<h6 class="text-muted fs-13"><?php echo e(__('Have your favorite AI chat assistants handy anytime you need them')); ?></h6>
						<div class="btn-group dashboard-menu-button">
							<button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" id="export" data-bs-display="static" aria-expanded="false"><i class="fa-solid fa-ellipsis  table-action-buttons table-action-buttons-big edit-action-button"></i></button>
							<div class="dropdown-menu" aria-labelledby="export" data-popper-placement="bottom-start">								
								<a class="dropdown-item" href="<?php echo e(route('user.chat')); ?>"><?php echo e(__('View All')); ?></a>	
							</div>
						</div>
					</div>
				</div>
				<div class="card-body pt-2 favorite-dashboard-panel">

					<?php if($chat_quantity): ?>
						<div class="row" id="templates-panel">

							<?php $__currentLoopData = $favorite_chats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<div class="col-sm-12" id="<?php echo e($chat->chat_code); ?>">
									<div class="chat-boxes-dasboard text-center">
										<a id="<?php echo e($chat->chat_code); ?>" <?php if($chat->favorite): ?> data-tippy-content="<?php echo e(__('Remove from favorite')); ?>" <?php else: ?> data-tippy-content="<?php echo e(__('Select as favorite')); ?>" <?php endif; ?> onclick="favoriteChatStatus(this.id)"><i id="<?php echo e($chat->chat_code); ?>-icon" class="<?php if($chat->favorite): ?> fa-solid fa-stars <?php else: ?> fa-regular fa-star <?php endif; ?> star"></i></a>
										<div class="card <?php if($chat->category == 'professional'): ?> professional <?php elseif($chat->category == 'premium'): ?> premium <?php endif; ?>" id="<?php echo e($chat->chat_code); ?>-card" onclick="window.location.href='<?php echo e(url('app/user/chats')); ?>/<?php echo e($chat->chat_code); ?>'">
											<div class="card-body pt-2 pb-2 d-flex">
												<div class="widget-user-image overflow-hidden"><img alt="User Avatar" class="rounded-circle" src="<?php echo e(URL::asset($chat->logo)); ?>"></div>
												<div class="template-title mt-auto mb-auto d-flex justify-content-center">
													<h6 class="fs-13 font-weight-bold mb-0 ml-4 mt-auto mb-auto"><?php echo e(__($chat->name)); ?></h6> <h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <h6 class="fs-13 text-muted mb-0 mt-auto mb-auto"><?php echo e(__($chat->sub_name)); ?></h6> 
													<?php if($chat->category == 'professional'): ?> 
														<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-pro"><i class="   fa-solid fa-crown mr-2"></i><?php echo e(__('Pro')); ?></p> 
													<?php elseif($chat->category == 'free'): ?>
														<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-free"><i class="   fa-solid fa-gift mr-2"></i><?php echo e(__('Free')); ?></p> 
													<?php elseif($chat->category == 'premium'): ?>
														<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-premium"><i class="   fa-solid fa-gem mr-2"></i><?php echo e(__('Premium')); ?></p> 
													<?php endif; ?>
												</div>						
											</div>
										</div>
									</div>							
								</div>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

							<?php $__currentLoopData = $custom_chats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<div class="col-sm-12" id="<?php echo e($chat->chat_code); ?>">
									<div class="chat-boxes-dasboard text-center">
										<a id="<?php echo e($chat->chat_code); ?>" <?php if($chat->favorite): ?> data-tippy-content="<?php echo e(__('Remove from favorite')); ?>" <?php else: ?> data-tippy-content="<?php echo e(__('Select as favorite')); ?>" <?php endif; ?> onclick="favoriteChatStatus(this.id)"><i id="<?php echo e($chat->chat_code); ?>-icon" class="<?php if($chat->favorite): ?> fa-solid fa-stars <?php else: ?> fa-regular fa-star <?php endif; ?> star"></i></a>
										<div class="card <?php if($chat->category == 'professional'): ?> professional <?php elseif($chat->category == 'premium'): ?> premium <?php endif; ?>" id="<?php echo e($chat->chat_code); ?>-card" onclick="window.location.href='<?php echo e(url('app/user/chats/custom')); ?>/<?php echo e($chat->chat_code); ?>'">
											<div class="card-body pt-2 pb-2 d-flex">
												<div class="widget-user-image overflow-hidden"><img alt="User Avatar" class="rounded-circle" src="<?php echo e(URL::asset($chat->logo)); ?>"></div>
												<div class="template-title mt-auto mb-auto d-flex justify-content-center">
													<h6 class="fs-13 font-weight-bold mb-0 ml-4 mt-auto mb-auto"><?php echo e(__($chat->name)); ?></h6> <h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <h6 class="fs-13 text-muted mb-0 mt-auto mb-auto"><?php echo e(__($chat->sub_name)); ?></h6> 
													<?php if($chat->category == 'professional'): ?> 
														<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-pro"><i class="   fa-solid fa-crown mr-2"></i><?php echo e(__('Pro')); ?></p> 
													<?php elseif($chat->category == 'free'): ?>
														<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-free"><i class="   fa-solid fa-gift mr-2"></i><?php echo e(__('Free')); ?></p> 
													<?php elseif($chat->category == 'premium'): ?>
														<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-premium"><i class="   fa-solid fa-gem mr-2"></i><?php echo e(__('Premium')); ?></p> 
													<?php endif; ?>
												</div>						
											</div>
										</div>
									</div>							
								</div>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

						</div>
					<?php else: ?>
						<div class="row text-center mt-8">
							<div class="col-sm-12">
								<h6 class="text-muted"><?php echo e(__('To add AI chat assistant as your favorite ones, simply click on the start icon on desired')); ?> <a href="<?php echo e(route('user.chat')); ?>" class="text-primary internal-special-links font-weight-bold"><?php echo e(__('AI Chat Assistants')); ?></a></h6>
							</div>
						</div>
					<?php endif; ?>
					
				</div>
			</div>
		</div> -->
		
		<!-- <div class="col-lg col-md-12 col-sm-12 mt-3">
			<div class="card border-0 pb-5" id="user-dashboard-panels">
				<div class="card-header pt-4 pb-4 border-0">
					<div class="mt-3">
						<h3 class="card-title mb-2"><i class="fa-solid fa-microchip-ai mr-2 text-muted"></i><?php echo e(__('Favorite AI Writer Templates')); ?></h3>
						<h6 class="text-muted fs-13"><?php echo e(__('Always have your top favorite templates handy whenever you need them')); ?></h6>
						<div class="btn-group dashboard-menu-button">
							<button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" id="export" data-bs-display="static" aria-expanded="false"><i class="fa-solid fa-ellipsis  table-action-buttons table-action-buttons-big edit-action-button"></i></button>
							<div class="dropdown-menu" aria-labelledby="export" data-popper-placement="bottom-start">								
								<a class="dropdown-item" href="<?php echo e(route('user.templates')); ?>"><?php echo e(__('View All')); ?></a>	
							</div>
						</div>
					</div>
				</div>
				<div class="card-body pt-2 favorite-dashboard-panel">

					<?php if($template_quantity): ?>
						<div class="row" id="templates-panel">

							<?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<div class="col-sm-12" id="<?php echo e($template->template_code); ?>">
									<div class="template-dashboard">
										<a id="<?php echo e($template->template_code); ?>" <?php if($template->favorite): ?> data-tippy-content="<?php echo e(__('Remove from favorite')); ?>" <?php else: ?> data-tippy-content="<?php echo e(__('Select as favorite')); ?>" <?php endif; ?> onclick="favoriteStatus(this.id)"><i class="<?php if($template->favorite): ?> fa-solid fa-stars <?php else: ?> fa-regular fa-star <?php endif; ?> star"></i></a>
										<div class="card <?php if($template->package == 'professional'): ?> professional <?php elseif($template->package == 'premium'): ?> premium <?php endif; ?>" onclick="window.location.href='<?php echo e(url('app/user/templates/original-template')); ?>/<?php echo e($template->slug); ?>'">
											<div class="card-body d-flex">
												<div class="template-icon">
													<?php echo $template->icon; ?>													
												</div>
												<div class="template-title ml-4">
													<div class="d-flex">
														<h6 class="fs-13 number-font mt-auto mb-auto"><?php echo e(__($template->name)); ?></h6>
														<?php if($template->package == 'professional'): ?> 
															<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-pro mb-0 mt-0"><i class="   fa-solid fa-crown mr-2"></i><?php echo e(__('Pro')); ?></p> 
														<?php elseif($template->package == 'free'): ?>
															<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-free mb-0 mt-0"><i class="   fa-solid fa-gift mr-2"></i><?php echo e(__('Free')); ?></p> 
														<?php elseif($template->package == 'premium'): ?>
															<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-premium mb-0 mt-0"><i class="   fa-solid fa-gem mr-2"></i><?php echo e(__('Premium')); ?></p> 
														<?php endif; ?>
													</div>
													<p class="fs-12 mb-0 text-muted"><?php echo e(__($template->description)); ?></p>
												</div>
											</div>
										</div>
									</div>							
								</div>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

							<?php $__currentLoopData = $custom_templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<div class="col-sm-12" id="<?php echo e($template->template_code); ?>">
									<div class="template-dashboard">
										<a id="<?php echo e($template->template_code); ?>" <?php if($template->favorite): ?> data-tippy-content="<?php echo e(__('Remove from favorite')); ?>" <?php else: ?> data-tippy-content="<?php echo e(__('Select as favorite')); ?>" <?php endif; ?> onclick="favoriteStatusCustom(this.id)"><i class="<?php if($template->favorite): ?> fa-solid fa-stars <?php else: ?> fa-regular fa-star <?php endif; ?> star"></i></a>
										<div class="card <?php if($template->package == 'professional'): ?> professional <?php elseif($template->package == 'premium'): ?> premium <?php endif; ?>" onclick="window.location.href='<?php echo e(url('app/user/templates')); ?>/<?php echo e($template->slug); ?>/<?php echo e($template->template_code); ?>'">
											<div class="card-body d-flex">
												<div class="template-icon">
													<?php echo $template->icon; ?>													
												</div>
												<div class="template-title ml-4">
													<div class="d-flex">
														<h6 class="fs-13 number-font mt-auto mb-auto"><?php echo e(__($template->name)); ?></h6>
														<?php if($template->package == 'professional'): ?> 
															<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-pro mb-0 mt-0"><i class="   fa-solid fa-crown mr-2"></i><?php echo e(__('Pro')); ?></p> 
														<?php elseif($template->package == 'free'): ?>
															<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-free mb-0 mt-0"><i class="   fa-solid fa-gift mr-2"></i><?php echo e(__('Free')); ?></p> 
														<?php elseif($template->package == 'premium'): ?>
															<h6 class="mr-2 ml-2 text-muted mt-auto mb-auto">|</h6> <p class="fs-8 btn package-badge btn-premium mb-0 mt-0"><i class="   fa-solid fa-gem mr-2"></i><?php echo e(__('Premium')); ?></p> 
														<?php endif; ?>
													</div>
													<p class="fs-12 mb-0 text-muted"><?php echo e(__($template->description)); ?></p>
												</div>
											</div>
										</div>
									</div>							
								</div>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

						</div>
					<?php else: ?>
						<div class="row text-center mt-8">
							<div class="col-sm-12">
								<h6 class="text-muted"><?php echo e(__('To add templates as your favorite ones, simply click on the start icon on desired')); ?> <a href="<?php echo e(route('user.templates')); ?>" class="text-primary internal-special-links font-weight-bold"><?php echo e(__('templates')); ?></a></h6>
							</div>
						</div>
					<?php endif; ?>
					
				</div>
			</div>
		</div> -->

		<div class="col-lg-12 col-md-12 col-sm-12 mt-3">
			<div class="card border-0">
				<div class="card-header pt-4 pb-0 border-0">
					<div class="mt-3">
						<h3 class="card-title mb-2"><i class="fa-solid fa-folder-bookmark mr-2 text-muted"></i><?php echo e(__('Recent Documents')); ?></h3>
						<div class="btn-group dashboard-menu-button">
							<button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" id="export" data-bs-display="static" aria-expanded="false"><i class="fa-solid fa-ellipsis  table-action-buttons table-action-buttons-big edit-action-button"></i></button>
							<div class="dropdown-menu" aria-labelledby="export" data-popper-placement="bottom-start">								
								<a class="dropdown-item" href="<?php echo e(route('user.documents')); ?>"><?php echo e(__('View All')); ?></a>	
							</div>
						</div>
					</div>
				</div>
				<div class="card-body pt-2 responsive-dashboard-table">
					<table class="table table-hover" id="database-backup">
						<thead>
							<tr role="row">
								<th class="fs-12 font-weight-700 border-top-0"><?php echo e(__('Document Name')); ?></th>
								<th class="fs-12 font-weight-700 border-top-0 text-right"><?php echo e(__('Words')); ?></th>
								<th class="fs-12 font-weight-700 border-top-0 text-right"><?php echo e(__('Workbook')); ?></th>
								<th class="fs-12 font-weight-700 border-top-0 text-right"><?php echo e(__('Category')); ?></th>
								<th class="fs-12 font-weight-700 border-top-0 text-right"><?php echo e(__('Last Activity')); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<tr class="relative">
								<td><div class="d-flex">
										<div class="mr-2 rtl-small-icon"><?php echo $data->icon; ?></div>
										<div><a class="font-weight-bold document-title" href="<?php echo e(route("user.documents.show", $data->id )); ?>"><?php echo e(ucfirst($data->title)); ?></a><br><span class="text-muted"><?php echo e(ucfirst($data->template_name)); ?></span><div>
									</div>
								</td>
								<td class="text-right text-muted"><?php echo e($data->words); ?></td>
								<td class="text-right text-muted"><?php echo e(ucfirst($data->workbook)); ?></td>
								<td class="text-right"><span class="cell-box category-<?php echo e($data->group); ?>"><?php echo e(__(ucfirst($data->group))); ?></span></td>
								<td class="text-right text-muted"><?php echo e(\Carbon\Carbon::parse($data->updated_at)->diffForHumans()); ?></td>
								<td class="w-0 p-0" colspan="0">
									<a class="strage-things" style="position: absolute; inset: 0px; width: 100%" href="<?php echo e(route("user.documents.show", $data->id )); ?>"><span class="sr-only"><?php echo e(__('View')); ?></span></a>
								</td>
							</tr>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</tbody>
					</table>					
				</div>
			</div>
		</div>

		<div class="col-lg-12 col-sm-12 mt-3">
			<div class="row">
				<?php if(config('settings.user_support') == 'enabled'): ?>
					<!-- <div class="col-lg-2 col-md-2 col-sm-12 mt-5">                        
						<div class="title text-center dashboard-title">
							<h3 class="fs-24 super-strong"><?php echo e(__('Need Help?')); ?></h3>     
							<h6 class="text-muted fs-14 mb-4"><?php echo e(__('Got questions? We have you covered')); ?></h6>                    
							<a href="<?php echo e(route('user.support')); ?>" class="btn btn-primary pl-6 pr-6 mb-2" style="text-transform: none"><?php echo e(__('Create Support Ticket')); ?></a>
							<h6 class="text-muted fs-10 mb-4"><?php echo e(__('Available from')); ?> <span class="font-weight-bold"><?php echo e(__('9am till 5pm')); ?></span></h6> 
						</div>                                               
					</div> -->

					<!-- <div class="col-lg-5 col-md-5 col-sm-12 mb-5">
						<div class="card border-0 pb-4">
							<div class="card-header pt-4 pb-0 border-0">
								<div class="mt-3">
									<h3 class="card-title mb-2"><i class="fa-solid fa-headset mr-2 text-muted"></i><?php echo e(__('Support Tickets')); ?></h3>
									<div class="btn-group dashboard-menu-button">
										<button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" id="export" data-bs-display="static" aria-expanded="false"><i class="fa-solid fa-ellipsis  table-action-buttons table-action-buttons-big edit-action-button"></i></button>
										<div class="dropdown-menu" aria-labelledby="export" data-popper-placement="bottom-start">								
											<a class="dropdown-item" href="<?php echo e(route('user.support')); ?>"><?php echo e(__('View All')); ?></a>	
										</div>
									</div>
								</div>
							</div>
							<div class="card-body pt-2 dashboard-panel-500">
								<table class="table table-hover" id="database-backup">
									<thead>
										<tr role="row">
											<th class="fs-12 font-weight-700 border-top-0"><?php echo e(__('Ticket ID')); ?></th>
											<th class="fs-12 font-weight-700 border-top-0 text-left"><?php echo e(__('Subject')); ?></th>
											<th class="fs-12 font-weight-700 border-top-0 text-center"><?php echo e(__('Category')); ?></th>
											<th class="fs-12 font-weight-700 border-top-0 text-center"><?php echo e(__('Status')); ?></th>
											<th class="fs-12 font-weight-700 border-top-0 text-right"><?php echo e(__('Last Updated')); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<tr class="relative" style="height: 60px">
											<td><a class="font-weight-bold text-primary" href="<?php echo e(route("user.support.show", $data->ticket_id )); ?>"><?php echo e($data->ticket_id); ?></a>
											</td>
											<td class="text-left text-muted"><?php echo e(ucfirst($data->subject)); ?></td>
											<td class="text-center text-muted"><?php echo e(ucfirst($data->category)); ?></td>
											<td class="text-center"><span class="cell-box support-<?php echo e(strtolower($data->status)); ?>"><?php echo e(__(ucfirst($data->status))); ?></span></td>
											<td class="text-right text-muted"><?php echo e(\Carbon\Carbon::parse($data->updated_at)->diffForHumans()); ?></td>
											<td class="w-0 p-0" colspan="0">
												<a class="strage-things" style="position: absolute; inset: 0px; width: 100%" href="<?php echo e(route("user.support.show", $data->ticket_id )); ?>"><span class="sr-only"><?php echo e(__('View')); ?></span></a>
											</td>
										</tr>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</tbody>
								</table>					
							</div>
						</div>                      
					</div>     -->
				<?php endif; ?>
				 
				<!-- <div class="col-lg-5 col-md-5 col-sm-12 mb-5">
					<div class="card border-0 pb-4">
						<div class="card-header pt-4 pb-0 border-0">
							<div class="mt-3">
								<h3 class="card-title mb-2"><i class="fa-solid fa-solid fa-message-exclamation mr-2 text-muted"></i><?php echo e(__('News & Notifications')); ?></h3>
								<div class="btn-group dashboard-menu-button">
									<button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" id="export" data-bs-display="static" aria-expanded="false"><i class="fa-solid fa-ellipsis  table-action-buttons table-action-buttons-big edit-action-button"></i></button>
									<div class="dropdown-menu" aria-labelledby="export" data-popper-placement="bottom-start">								
										<a class="dropdown-item" href="<?php echo e(route('user.notifications')); ?>"><?php echo e(__('View All')); ?></a>	
									</div>
								</div>
							</div>
						</div>
						<div class="card-body pt-2 dashboard-timeline dashboard-panel-500">					
							<div class="vertical-timeline vertical-timeline--animate vertical-timeline--one-column">
								<?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<div class="vertical-timeline-item vertical-timeline-element">
										<div>
											<span class="vertical-timeline-element-icon">
												<?php if($notification->data['type'] == 'Warning'): ?>
													<i class="badge badge-dot badge-dot-xl badge-secondary"> </i>
												<?php elseif($notification->data['type'] == 'Info'): ?>
													<i class="badge badge-dot badge-dot-xl badge-primary"> </i>
												<?php elseif($notification->data['type'] == 'Announcement'): ?>
													<i class="badge badge-dot badge-dot-xl badge-success"> </i>
												<?php else: ?>
													<i class="badge badge-dot badge-dot-xl badge-warning"> </i>
												<?php endif; ?>
												
											</span>
											<div class="vertical-timeline-element-content">
												<h4 class="fs-13"><a href="<?php echo e(route("user.notifications.show", $notification->id)); ?>"><b><?php echo e(__($notification->data['type'])); ?>:</b></a> <?php echo e(__($notification->data['subject'])); ?></h4>
												<p><?php if($notification->data['action'] == 'Action Required'): ?> <span class="text-danger"><?php echo e(__('Action Required')); ?></span> <?php else: ?> <span class="text-muted fs-12"><?php echo e(__('No Action Required')); ?></span> <?php endif; ?></p>
												<span class="vertical-timeline-element-date text-center"><?php echo e(\Carbon\Carbon::parse($notification->created_at)->format('M d, Y')); ?> <br> <?php echo e(\Carbon\Carbon::parse($notification->created_at)->format('H:i A')); ?></span>
											</div>
										</div>
									</div>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</div>											  					
						</div>
					</div>                      
				</div>   -->
			</div>
		</div>

	</div>
	<!-- END USER PROFILE PAGE -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
	<!-- Chart JS -->
	
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
	<script src="<?php echo e(URL::asset('plugins/sweetalert/sweetalert2.all.min.js')); ?>"></script>
	<script>
		$(function() {
	
			'use strict';

			// Total New User Analysis Chart
			var userMonthlyData = JSON.parse(`<?php echo $chart_data['user_monthly_usage']; ?>`);
			var userMonthlyDataset = Object.values(userMonthlyData);

			let chartColor = "#FFFFFF";
			let gradientChartOptionsConfiguration = {
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false,
					},
					tooltip: {
						titleAlign: 'center',
						bodySpacing: 4,
						mode: "nearest",
						intersect: 0,
						position: "nearest",
						xPadding: 20,
						yPadding: 20,
						caretPadding: 20
					},
				},			
				responsive: true,
				scales: {
					y: {
						display: 0,
						grid: 0,
						ticks: {
							display: false,
							padding: 0,
							beginAtZero: true,
						},
						grid: {
							zeroLineColor: "transparent",
							drawTicks: false,
							display: false,
							drawBorder: false,
						}
					},
					x: {
						display: 0,
						grid: 0,
						ticks: {
							display: false,
							padding: 0,
							beginAtZero: true,
						},
						grid: {
							zeroLineColor: "transparent",
							drawTicks: false,
							display: false,
							drawBorder: false,
						}
					}
				},
				layout: {
					padding: {
						left: 0,
						right: -10,
						top: 0,
						bottom: -10
					}
				},
				elements: {
					line: {
						tension : 0.4
					},
				},
			};

			let ctx2 = document.getElementById('hoursSavedChart').getContext("2d");
			let gradientStroke = ctx2.createLinearGradient(500, 0, 100, 0);
			gradientStroke.addColorStop(0, '#18ce0f');
			gradientStroke.addColorStop(1, chartColor);
			let gradientFill = ctx2.createLinearGradient(0, 170, 0, 50);
			gradientFill.addColorStop(0, "rgba(128, 182, 244, 0)");
			gradientFill.addColorStop(1, "rgba(24,206,15, 0.4)");
			let myChart = new Chart(ctx2, {
				type: 'line',
				data: {
					labels: ['<?php echo e(__('Jan')); ?>', '<?php echo e(__('Feb')); ?>', '<?php echo e(__('Mar')); ?>', '<?php echo e(__('Apr')); ?>', '<?php echo e(__('May')); ?>', '<?php echo e(__('Jun')); ?>', '<?php echo e(__('Jul')); ?>', '<?php echo e(__('Aug')); ?>', '<?php echo e(__('Sep')); ?>', '<?php echo e(__('Oct')); ?>', '<?php echo e(__('Nov')); ?>', '<?php echo e(__('Dec')); ?>'],
					datasets: [{
						label: "<?php echo e(__('Words Generated')); ?>",
						borderColor: "#18ce0f",
						pointBorderColor: "#FFF",
						pointBackgroundColor: "#18ce0f",
						pointBorderWidth: 1,
						pointHoverRadius: 4,
						pointHoverBorderWidth: 1,
						pointRadius: 3,
						fill: true,
						backgroundColor: gradientFill,
						borderWidth: 2,
						data: userMonthlyDataset
					}]
				},
				options: gradientChartOptionsConfiguration
			});

		});

		function favoriteStatus(id) {

			let formData = new FormData();
			formData.append("id", id);

			$.ajax({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				method: 'post',
				url: 'dashboard/favorite',
				data: formData,
				processData: false,
				contentType: false,
				success: function (data) {

					if (data['status'] == 'success') {
						if (data['set']) {
							Swal.fire('<?php echo e(__('Template Removed from Favorites')); ?>', '<?php echo e(__('Selected template has been successfully removed from favorites')); ?>', 'success');
							document.getElementById(id).style.display = 'none';	
						} else {
							Swal.fire('<?php echo e(__('Template Added to Favorites')); ?>', '<?php echo e(__('Selected template has been successfully added to favorites')); ?>', 'success');
						}
														
					} else {
						Swal.fire('<?php echo e(__('Favorite Setting Issue')); ?>', '<?php echo e(__('There as an issue with setting favorite status for this template')); ?>', 'warning');
					}      
				},
				error: function(data) {
					Swal.fire('Oops...','Something went wrong!', 'error')
				}
			})

			return false;
		}

		function favoriteStatusCustom(id) {

			let formData = new FormData();
			formData.append("id", id);

			$.ajax({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				method: 'post',
				url: 'dashboard/favoritecustom',
				data: formData,
				processData: false,
				contentType: false,
				success: function (data) {

					if (data['status'] == 'success') {
						if (data['set']) {
							Swal.fire('<?php echo e(__('Template Removed from Favorites')); ?>', '<?php echo e(__('Selected template has been successfully removed from favorites')); ?>', 'success');
							document.getElementById(id).style.display = 'none';	
						} else {
							Swal.fire('<?php echo e(__('Template Added to Favorites')); ?>', '<?php echo e(__('Selected template has been successfully added to favorites')); ?>', 'success');
						}
														
					} else {
						Swal.fire('<?php echo e(__('Favorite Setting Issue')); ?>', '<?php echo e(__('There as an issue with setting favorite status for this template')); ?>', 'warning');
					}      
				},
				error: function(data) {
					Swal.fire('Oops...','Something went wrong!', 'error')
				}
			})

			return false;
		}

		function favoriteChatStatus(id) {

			let icon, card;
			let formData = new FormData();
			formData.append("id", id);

			$.ajax({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				method: 'post',
				url: 'chat/favorite',
				data: formData,
				processData: false,
				contentType: false,
				success: function (data) {

					if (data['status'] == 'success') {
						if (data['set']) {
							Swal.fire('<?php echo e(__('Chat Bot Removed from Favorites')); ?>', '<?php echo e(__('Selected chat bot has been successfully removed from favorites')); ?>', 'success');
							document.getElementById(id).style.display = 'none';
							icon = document.getElementById(id + '-icon');
							icon.classList.remove("fa-solid");
							icon.classList.remove("fa-stars");
							icon.classList.add("fa-regular");
							icon.classList.add("fa-star");

							card = document.getElementById(id + '-card');
							if(card.classList.contains("professional")) {
								// do nothing
							} else {
								card.classList.remove("favorite");
								card.classList.add('border-0');
							}							
						} else {
							Swal.fire('<?php echo e(__('Chat Bot Added to Favorites')); ?>', '<?php echo e(__('Selected chat bot has been successfully added to favorites')); ?>', 'success');
							icon = document.getElementById(id + '-icon');
							icon.classList.remove("fa-regular");
							icon.classList.remove("fa-star");
							icon.classList.add("fa-solid");
							icon.classList.add("fa-stars");

							card = document.getElementById(id + '-card');
							if(card.classList.contains("professional")) {
								// do nothing
							} else {
								card.classList.add('favorite');
								card.classList.remove('border-0');
							}
						}
														
					} else {
						Swal.fire('<?php echo e(__('Favorite Setting Issue')); ?>', '<?php echo e(__('There as an issue with setting favorite status for this chat bot')); ?>', 'warning');
					}      
				},
				error: function(data) {
					Swal.fire('Oops...','Something went wrong!', 'error')
				}
			})
		}

	</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\transkripti\resources\views/default/user/dashboard/index.blade.php ENDPATH**/ ?>