<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('job_role')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status')->nullable();
            $table->string('group')->nullable();
            $table->string('workbook')->nullable();
            $table->integer('plan_id')->nullable();
            $table->string('plan_type')->nullable();
            $table->string('company')->nullable();
            $table->string('website')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->float('balance')->default(0.0);           
            $table->text('profile_photo_path')->nullable();
            $table->string('oauth_id')->nullable();
            $table->string('oauth_type')->nullable();     
            $table->timestamp('last_seen')->nullable();
            $table->text('google2fa_secret')->nullable();
            $table->boolean('google2fa_enabled')->nullable()->default(0);
            $table->string('referral_id')->nullable();
            $table->string('referred_by')->nullable();
            $table->string('referral_payment_method')->nullable();
            $table->string('referral_paypal')->nullable();
            $table->text('referral_bank_requisites')->nullable();
            $table->string('default_voiceover_language')->nullable();
            $table->string('default_voiceover_voice')->nullable();
            $table->string('default_template_language')->default('en-US')->nullable();
            $table->integer('member_of')->nullable(); 
            $table->integer('member_limit')->default(0)->nullable(); 
            $table->boolean('member_use_credits_template')->default(1)->nullable(); 
            $table->boolean('member_use_credits_chat')->default(1)->nullable(); 
            $table->boolean('member_use_credits_code')->default(1)->nullable(); 
            $table->boolean('member_use_credits_voiceover')->default(1)->nullable(); 
            $table->boolean('member_use_credits_speech')->default(1)->nullable(); 
            $table->boolean('member_use_credits_image')->default(1)->nullable(); 
            $table->string('personal_openai_key')->nullable();
            $table->string('personal_claude_key')->nullable();
            $table->string('personal_gemini_key')->nullable();
            $table->string('personal_sd_key')->nullable();
            $table->boolean('hidden_plan')->nullable()->default(false);
            $table->boolean('used_free_tier')->nullable()->default(false);
            $table->string('theme')->default('light');
            $table->integer('plagiarism_pages')->default(0);
            $table->integer('ai_detector_pages')->default(0);
            $table->boolean('subscription_required')->default(false);
            $table->string('verification_code')->nullable();
            $table->boolean('email_opt_in')->default(true);           
            $table->string('default_model_template')->default('gpt-4o-mini');
            $table->string('default_model_chat')->default('gpt-4o-mini');
            $table->integer('images')->default(0);
            $table->integer('images_prepaid')->default(0);
            $table->integer('characters')->default(0);
            $table->integer('characters_prepaid')->default(0);
            $table->integer('tokens')->default(0);
            $table->integer('tokens_prepaid')->default(0);
            $table->decimal('minutes', 15, 3)->default(0);
            $table->decimal('minutes_prepaid', 15, 3)->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
