

<?php $__env->startSection('content'); ?>
	<div class="row mt-24">
		<div class="col-sm-12">
			<div class="card border-0 p-5 pt-4">
				<div class="card-body">
					<div class="row">	
						<h3 class="card-title mb-3 fs-20 font-weight-bold"><?php echo e($theme['name']); ?> <?php echo e(__('Extension')); ?></h3>										
						<a href="<?php echo e(route('admin.extensions')); ?>" class="mb-5 fs-12 text-muted"><i class="fa-solid fa-objects-column mr-2 text-muted"></i><?php echo e(__('View All Extensions')); ?></a>		

						<div class="col-lg-8 col-md-7 col-sm-12">
							<div class="card shadow-0 theme p-6">
								<div class="pl-5 pr-5 pt-5 pb-4 extension-icon">
									<span><?php echo $theme['banner']; ?></span>
								</div>

								<div class="theme-name">
									<h6 class="mb-5 mt-3 pl-5 fs-24 super-strong"><?php echo e($theme['name']); ?> </h6>
								</div>
																	
								<div class="card-body">
									<div class="row">
										<div class="col-lg-3 col-md-6 col-sm-12">
											<div class="card shadow-0 text-center" style="height: 50px;">
												<h6 class="mt-auto mb-auto fs-13 font-weight-semibold"><i class="fa-solid fa-objects-column mr-2 text-primary"></i><?php echo e(ucfirst($theme['type'])); ?></h6>
											</div>
										</div>
										<div class="col-lg-3 col-md-6 col-sm-12">
											<div class="card shadow-0 text-center" style="height: 50px;">
												<h6 class="mt-auto mb-auto fs-13 font-weight-semibold"><i class="fa-solid fa-badge-check mr-2 text-primary"></i><?php echo e(__('Tested with DaVinci AI')); ?></h6>
											</div>
										</div>
										<div class="col-lg-3 col-md-6 col-sm-12">
											<div class="card shadow-0 text-center" style="height: 50px;">
												<h6 class="mt-auto mb-auto fs-13 font-weight-semibold"><i class="fa-solid fa-star mr-1 fs-11" style="vertical-align: top"></i>5.0</h6>
											</div>
										</div>
										<div class="col-lg-3 col-md-6 col-sm-12">
											<div class="card shadow-0 text-center" style="height: 50px;">
												<h6 class="mt-auto mb-auto fs-13 font-weight-semibold"><i class="fa-solid fa-timer mr-2"></i><?php echo e(__('Recently Updated')); ?></h6>
											</div>
										</div>
									</div>									
									<div class="theme-info">
										<h6 class="font-weight-bold fs-16 mb-5 mt-3"><?php echo e(__('Extension Description')); ?></h6>
										<p class="fs-13 mb-2"><?php echo $theme['main_description']; ?></p>
									</div>	
									<div class="theme-info mt-7 mb-7">
										<?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<p class="fs-14 font-weight-semibold mb-5"><i class="fa-solid fa-circle-check fs-20 text-primary mr-2" style="vertical-align: middle"></i> <?php echo e(trim($tag)); ?></p>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</div>	
									<div class="theme-faq">
										<h6 class="mb-5 mt-6 fs-15 font-weight-bold"><?php echo e(__('Got Questions?')); ?></h6>
										<div id="faqs" class="pb-6">
											<div id="accordion">
												<div class="card">
													<div class="card-header" id="heading1">
														<h5 class="mb-0">
														<span class="btn btn-link faq-button" data-bs-toggle="collapse" data-bs-target="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
															<i class="fa-solid fa-messages-question fs-14 text-muted mr-2"></i> <?php echo e(__('How to install the entension?')); ?>

														</span>
														</h5>
														<i class="fa-solid fa-plus fs-10"></i>
													</div>
												
													<div id="collapse-1" class="collapse" aria-labelledby="heading1" data-bs-parent="#accordion">
														<div class="card-body">
															<?php echo e(__('After successfully purchasing your target theme, you can click on the install button that will appear after the purchase process is completed. It will start the download process of your new theme and it will be ready for activation and usage within seconds.')); ?>

														</div>
													</div>
												</div>
											</div>
											<div id="accordion">
												<div class="card">
													<div class="card-header" id="heading2">
														<h5 class="mb-0">
														<span class="btn btn-link faq-button" data-bs-toggle="collapse" data-bs-target="#collapse-2" aria-expanded="false" aria-controls="collapse-2">
															<i class="fa-solid fa-messages-question fs-14 text-muted mr-2"></i> <?php echo e(__('How to remove the extension?')); ?>

														</span>
														</h5>
														<i class="fa-solid fa-plus fs-10"></i>
													</div>
												
													<div id="collapse-2" class="collapse" aria-labelledby="heading2" data-bs-parent="#accordion">
														<div class="card-body">
															<?php echo e(__('In case if you purchased multiple themes, you can click on the activate button, it will automatically set it as your default system theme either for frontend or dashboard depending on which theme you purchased and activated.')); ?>

														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

								</div>
							</div>						
							
						</div>

						<div class="col-lg-4 col-md-5 col-sm-12">
							<form id="payment-form" action="<?php echo e(route('admin.extension.purchase', $theme['slug'])); ?>" install="<?php echo e(route('admin.extension.install', $theme['slug'])); ?>" method="POST" enctype="multipart/form-data">
								<?php echo csrf_field(); ?>
								<div class="card shadow-0 theme">								
									<div class="card-body text-center">
										<div class="theme-name mt-5">
											<h6 class="mb-4 fs-13 text-muted"><?php echo e(__('For a limited time only')); ?></h6>
										</div>
										<div class="theme-info">
											<?php if($theme['is_free']): ?>
												<h6 class="mb-4 fs-40 number-font" style="opacity: 0.8"><?php echo e(__('Free')); ?></h6>
											<?php else: ?>
												<?php if($type == 'Regular License' || $type == ''): ?>
													<?php if($theme['only_for_extended']): ?>
														<h6 class="mb-4 fs-20 number-font" style="opacity: 0.8"><?php echo e(__('Extended License Required')); ?></h6>
													<?php else: ?>
														<?php if(!is_null($theme['discount_price'])): ?>
															<h6 class="mb-4 fs-40 number-font" style="opacity: 0.8">$<?php echo e($theme['discount_price']); ?> <span class="fs-20"><strike>$<?php echo e($theme['price_placeholder']); ?></strike></span></h6>
														<?php else: ?>
															<h6 class="mb-4 fs-40 number-font" style="opacity: 0.8">$<?php echo e($theme['price']); ?></h6>
														<?php endif; ?>
													<?php endif; ?>			
												<?php else: ?>
													<?php if($theme['free_for_extended']): ?>
														<h6 class="mb-4 fs-40 number-font" style="opacity: 0.8">$0 <span class="fs-20"><strike>$<?php echo e($theme['price']); ?></strike></span></h6>
													<?php else: ?>
														<?php if(!is_null($theme['discount_price'])): ?>
															<h6 class="mb-4 fs-40 number-font" style="opacity: 0.8">$<?php echo e($theme['discount_price']); ?> <span class="fs-20"><strike>$<?php echo e($theme['price_placeholder']); ?></strike></span></h6>
														<?php else: ?>
															<h6 class="mb-4 fs-40 number-font" style="opacity: 0.8">$<?php echo e($theme['price']); ?></h6>
														<?php endif; ?>
													<?php endif; ?>
												<?php endif; ?>												
											<?php endif; ?>
											
										</div>	
										<div class="theme-name mt-3">																						
											<?php if($theme['free_for_extended']): ?>
												<h6 class="mb-2 fs-13 text-muted"><?php echo e(__('Price is in US dollar. Tax included.')); ?></h6>
												<h6 class="mb-4 fs-11 text-muted">*<?php echo e(__('Free for Extended License users')); ?></h6>
											<?php elseif($theme['only_for_extended']): ?>
												<h6 class="mb-2 fs-13 text-muted"><?php echo e(__('Price is in US dollar. Tax included.')); ?></h6>
												<h6 class="mb-4 fs-11 text-muted">*<?php echo e(__('Only for Extended License users')); ?></h6>
											<?php else: ?>
												<h6 class="mb-4 fs-13 text-muted"><?php echo e(__('Price is in US dollar. Tax included.')); ?></h6>
											<?php endif; ?>
										</div>
										<input type="hidden" name="value" id="hidden_value" value="<?php echo e($theme['price']); ?>">
										<input type="hidden" name="type" value="extension">
										<input type="hidden" name="extension_name" value="<?php echo e($theme['name']); ?>">
										<div class="theme-action text-center mt-4 mb-4">

											<?php if($approved): ?>
												<?php if($theme['is_free']): ?>
													<?php if($extension->installed): ?>
														<?php if((float)$extension->version < (float)$theme['version']): ?>
															<a href="#" id="update-button" class="btn btn-primary ripple" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Update Extension')); ?></a>	
														<?php else: ?>
															<a href="#" class="btn btn-primary ripple disabled" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Installed')); ?></a>	
														<?php endif; ?>
													<?php else: ?>
														<a href="#" id="install-button" class="btn btn-primary ripple" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Install Extension')); ?></a>	
													<?php endif; ?>												
												<?php else: ?>

													<?php if($extension->purchased && $extension->installed): ?>
														<?php if((float)$extension->version < (float)$theme['version']): ?>
															<a href="#" id="update-button" class="btn btn-primary ripple" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Update Extension')); ?></a>	
														<?php else: ?>
															<a href="#" class="btn btn-primary ripple disabled" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Installed')); ?></a>	
														<?php endif; ?>
													<?php else: ?>
														<?php if($extension->purchased && !$extension->installed): ?>
															<a href="#" id="install-button" class="btn btn-primary ripple" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Install Extension')); ?></a>	
														<?php else: ?>
															<?php if($type == 'Regular License' || $type == ''): ?>
																<?php if($theme['only_for_extended']): ?>
																	<a href="#" class="btn btn-primary ripple disabled" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Extended License is Required')); ?></a>	
																<?php else: ?>
																	<button type="submit" id="payment-button" class="btn btn-primary ripple" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Buy Extension')); ?></button>
																<?php endif; ?>
																
															<?php else: ?>
																<?php if($theme['free_for_extended']): ?>
																	<a href="#" id="install-button" class="btn btn-primary ripple" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Install Extension')); ?></a>	
																<?php else: ?>
																	<button type="submit" id="payment-button" class="btn btn-primary ripple" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Buy Extension')); ?></button>
																<?php endif; ?>
															<?php endif; ?>
														<?php endif; ?>	
													<?php endif; ?>	
												<?php endif; ?>
											<?php else: ?>
												<a href="#" class="btn btn-primary ripple disabled" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><?php echo e(__('Minimum required app version is ')); ?> v<?php echo e($approved_version); ?></a>	
											<?php endif; ?>							
									
										</div>	
									</div>
								</div>	
							</form>
							
							<div class="card shadow-0 theme">
								<div class="card-body p-6">
									<p class="card-title mb-4 font-weigth-semibold pb-3" style="border-bottom: 1px solid #dbe2eb"><?php echo e(__('Details')); ?></p>
									<div class="row">
										<div class="col-md-6 col-sm-12">
											<div class="card shadow-0 p-4" style="height: 75px;">
												<h6 class="mb-4 fs-10 text-muted" style="text-transform: uppercase; letter-spacing: 1px"><?php echo e(__('Released Date')); ?></h6>
												<h6 class="fs-13 font-weight-semibold"><?php echo e($theme['released_date']); ?></h6>
											</div>
										</div>
										<div class="col-md-6 col-sm-12">
											<div class="card shadow-0 p-4" style="height: 75px;">
												<h6 class="mb-4 fs-10 text-muted" style="text-transform: uppercase; letter-spacing: 1px"><?php echo e(__('Updated Date')); ?></h6>
												<h6 class="fs-13 font-weight-semibold"><?php echo e($theme['updated_date']); ?></h6>
											</div>
										</div>
										<div class="col-md-6 col-sm-12">
											<div class="card shadow-0 p-4" style="height: 75px;">
												<h6 class="mb-4 fs-10 text-muted" style="text-transform: uppercase; letter-spacing: 1px"><?php echo e(__('Version')); ?></h6>
												<h6 class="fs-13 font-weight-semibold"><?php echo e($theme['version']); ?></h6>
											</div>
										</div>
										<div class="col-md-6 col-sm-12">
											<div class="card shadow-0 p-4" style="height: 75px;">
												<h6 class="mb-4 fs-10 text-muted" style="text-transform: uppercase; letter-spacing: 1px"><?php echo e(__('Installation')); ?></h6>
												<h6 class="fs-13 font-weight-semibold"><?php echo e(__('One Click')); ?></h6>
											</div>
										</div>
										<div class="col-md-6 col-sm-12">
											<div class="card shadow-0 p-4" style="height: 75px;">
												<h6 class="mb-4 fs-10 text-muted" style="text-transform: uppercase; letter-spacing: 1px"><?php echo e(__('License Required')); ?></h6>
												<h6 class="fs-13 font-weight-semibold"><?php echo e($theme['license_required']); ?></h6>
											</div>
										</div>
										<div class="col-md-6 col-sm-12">
											<div class="card shadow-0 p-4" style="height: 75px;">
												<h6 class="mb-4 fs-10 text-muted" style="text-transform: uppercase; letter-spacing: 1px"><?php echo e(__('Free Updates')); ?></h6>
												<h6 class="fs-13 font-weight-semibold"><?php echo e(__('Lifetime')); ?></h6>
											</div>
										</div>
									</div>
								</div>
							</div>	

							<div class="card shadow-0 card-body-gradient">
								<div class="card-body p-6 ">
									<p class="card-title fs-20 text-center card-header-gradient" ><?php echo e(__('Premium VIP Support')); ?></p>
									<div class="text-center">
										<h6 class="mb-2 fs-40 number-font" style="opacity: 0.8">$299</h6>
										<h6 class="mb-4 fs-12 text-muted"><?php echo e(__('Monthly cost. Price is in US dollar.')); ?></h6>
									</div>
									
									<div class="row">
										<div class="col-sm-12 text-center">
											<h6 class="fs-14"><i class="fa-solid fa-circle-check card-header-gradient mr-1"></i> <?php echo e(__('Priority support in support ticket queue')); ?></h6>
											<h6 class="fs-14"><i class="fa-solid fa-circle-check card-header-gradient mr-1"></i> <?php echo e(__('Support during Weekends')); ?></h6>
											<h6 class="fs-14"><i class="fa-solid fa-circle-check card-header-gradient mr-1"></i> <?php echo e(__('Maximum few hours of SLA time')); ?></h6>
											<h6 class="fs-14"><i class="fa-solid fa-circle-check card-header-gradient mr-1"></i> <?php echo e(__('Access to Extensions while on Premium Support')); ?></h6>
											<a href="<?php echo e(route('admin.extension.purchase.package', 'support')); ?>" id="buy-package" class="btn btn-primary ripple premier-button mt-3" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><span class="card-header-gradient"><?php echo e(__('Buy Premium Support')); ?></span></a>										
										</div>										
									</div>
								</div>
							</div>	
							
							<div class="card shadow-0 card-body-gradient">
								<div class="card-body p-6 ">
									<p class="card-title fs-20 text-center card-header-gradient" ><?php echo e(__('Premier Package Bundle')); ?></p>
									<div class="text-center">
										<h6 class="mb-2 fs-40 number-font" style="opacity: 0.8">$999</h6>
										<h6 class="mb-2 fs-12 text-muted"><?php echo e(__('One-time cost. Price is in US dollar.')); ?></h6>
										<h6 class="mb-4 fs-12 text-muted"><?php echo e(__('Includes released and upcoming Extensions & Themes.')); ?></h6>										
									</div>
									
									<div class="row">
										<div class="col-sm-12 text-center">
											<h6 class="fs-14"><i class="fa-solid fa-circle-check card-header-gradient mr-1"></i> <?php echo e(__('Full access to all paid Themes')); ?></h6>
											<h6 class="fs-14"><i class="fa-solid fa-circle-check card-header-gradient mr-1"></i> <?php echo e(__('Full access to all paid Extensions')); ?></h6>
											<h6 class="fs-14"><i class="fa-solid fa-circle-check card-header-gradient mr-1"></i> <?php echo e(__('Forever access to Extension updates')); ?></h6>
											<h6 class="fs-14"><i class="fa-solid fa-circle-check card-header-gradient mr-1"></i> <?php echo e(__('Forever access to Theme updates')); ?></h6>
											<a href="<?php echo e(route('admin.extension.purchase.package', 'premier')); ?>" class="btn btn-primary ripple premier-button mt-3" style="width: 250px; text-transform: none; font-size: 11px; padding-top: 10px; padding-bottom: 10px;"><span class="card-header-gradient"><?php echo e(__('Buy Premier Bundle')); ?></span></a>										
										</div>	
										
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>

	</div>
	<!-- END USER PROFILE PAGE -->
<?php $__env->stopSection(); ?>


<?php $__env->startSection('js'); ?>
	<script type="text/javascript">
		let loading = `<span class="loading">
					<span style="background-color: #fff;"></span>
					<span style="background-color: #fff;"></span>
					<span style="background-color: #fff;"></span>
					</span>`;

		$('#install-button').on('click',function(e) {

			const form = document.getElementById("payment-form");
			let data = new FormData(form);

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: "POST",
				url: $('#payment-form').attr('install'),
				data: data,
				processData: false,
				contentType: false,
				beforeSend: function() {
					$('#install-button').prop('disabled', true);
					let btn = document.getElementById('install-button');					
					btn.innerHTML = loading;  
					document.querySelector('#loader-line')?.classList?.remove('hidden');         
				},	

				success: function(data) {

					if (data['status']) {
						let btn = document.getElementById('install-button');					
						btn.innerHTML = '<?php echo e(__('Installed')); ?>';
						toastr.success(data['message']);
						document.querySelector('#loader-line')?.classList?.add('hidden');
					} else {
						$('#install-button').prop('disabled', false);
						let btn = document.getElementById('install-button');					
						btn.innerHTML = '<?php echo e(__('Install Extension')); ?>';
						toastr.error(data['message']);
						document.querySelector('#loader-line')?.classList?.add('hidden');
					}

				},
				error: function(data) {
					$('#install-button').prop('disabled', false);
					let btn = document.getElementById('install-button');					
					btn.innerHTML = '<?php echo e(__('Install Extension')); ?>';
					toastr.error(data['message']);
					document.querySelector('#loader-line')?.classList?.add('hidden');
				}
			}).done(function(data) {})
		});


		$('#update-button').on('click',function(e) {

			const form = document.getElementById("payment-form");
			let data = new FormData(form);

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: "POST",
				url: $('#payment-form').attr('install'),
				data: data,
				processData: false,
				contentType: false,
				beforeSend: function() {
					$('#update-button').prop('disabled', true);
					let btn = document.getElementById('update-button');					
					btn.innerHTML = loading;  
					document.querySelector('#loader-line')?.classList?.remove('hidden');         
				},	

				success: function(data) {

					if (data['status']) {
						let btn = document.getElementById('update-button');					
						btn.innerHTML = '<?php echo e(__('Updated')); ?>';
						toastr.success(data['message']);
						document.querySelector('#loader-line')?.classList?.add('hidden');
					} else {
						$('#update-button').prop('disabled', false);
						let btn = document.getElementById('update-button');					
						btn.innerHTML = '<?php echo e(__('Update Extension')); ?>';
						toastr.error(data['message']);
						document.querySelector('#loader-line')?.classList?.add('hidden');
					}

				},
				error: function(data) {
					$('#update-button').prop('disabled', false);
					let btn = document.getElementById('update-button');					
					btn.innerHTML = '<?php echo e(__('Update Extension')); ?>';
					toastr.error(data['message']);
					document.querySelector('#loader-line')?.classList?.add('hidden');
				}
			}).done(function(data) {})
		});

	</script>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\transkripti\resources\views/default/admin/marketplace/checkout.blade.php ENDPATH**/ ?>