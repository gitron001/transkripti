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
        Schema::create('api_credits', function (Blueprint $table) {
            $table->id();
            $table->boolean('o3_mini_new')->nullable()->default(false);
            $table->string('o3_mini_model')->nullable()->default('o3-mini');
            $table->string('o3_mini_title')->nullable()->default('o3 mini');
            $table->string('o3_mini_description')->nullable()->default('The OpenAI O3-mini model is a compact, efficient AI designed to perform natural language tasks with a focus on versatility and resource optimization.');
            $table->decimal('o3_mini_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('o3_mini_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('o1_new')->nullable()->default(false);
            $table->string('o1_model')->nullable()->default('o1');
            $table->string('o1_title')->nullable()->default('o1');
            $table->string('o1_description')->nullable()->default('The OpenAI O1 model is a cutting-edge artificial intelligence system designed for advanced natural language understanding and generation tasks.');
            $table->decimal('o1_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('o1_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('o1_mini_new')->nullable()->default(false);
            $table->string('o1_mini_model')->nullable()->default('o1-mini');
            $table->string('o1_mini_title')->nullable()->default('o1 mini');
            $table->string('o1_mini_description')->nullable()->default('Faster and cheaper reasoning model particularly good at coding, math, and science');
            $table->decimal('o1_mini_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('o1_mini_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gpt_45_new')->nullable()->default(false);
            $table->string('gpt_45_model')->nullable()->default('gpt-4.5-preview');
            $table->string('gpt_45_title')->nullable()->default('GPT 4.5');
            $table->string('gpt_45_description')->nullable()->default('Most advanced system from OpenAI. Can solve difficult problems with greater accuracy. Has 8k context window.');
            $table->decimal('gpt_45_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gpt_45_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gpt_4_new')->nullable()->default(false);
            $table->string('gpt_4_model')->nullable()->default('gpt-4');
            $table->string('gpt_4_title')->nullable()->default('GPT 4');
            $table->string('gpt_4_description')->nullable()->default('Most advanced system from OpenAI. Can solve difficult problems with greater accuracy. Has 8k context window.');
            $table->decimal('gpt_4_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gpt_4_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gpt_4_turbo_new')->nullable()->default(false);
            $table->string('gpt_4_turbo_model')->nullable()->default('gpt-4-0125-preview');
            $table->string('gpt_4_turbo_title')->nullable()->default('GPT 4 Turbo');
            $table->string('gpt_4_turbo_description')->nullable()->default('The latest GPT-3 model with improved performance. Has 128k context window. Trained up to December 2023 data.');
            $table->decimal('gpt_4_turbo_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gpt_4_turbo_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gpt_4o_new')->nullable()->default(false);
            $table->string('gpt_4o_model')->nullable()->default('gpt-4o');
            $table->string('gpt_4o_title')->nullable()->default('GPT 4o');
            $table->string('gpt_4o_description')->nullable()->default('The latest most advanced model of Openai. It can solve difficult problems with greater accuracy than any of our previous models');
            $table->decimal('gpt_4o_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gpt_4o_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gpt_4o_mini_new')->nullable()->default(false);
            $table->string('gpt_4o_mini_model')->nullable()->default('gpt-4o-mini');
            $table->string('gpt_4o_mini_title')->nullable()->default('GPT 4o mini');
            $table->string('gpt_4o_mini_description')->nullable()->default('The most newest and advanced model of Openai. It can solve difficult problems with greater accuracy than any of our previous models');
            $table->decimal('gpt_4o_mini_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gpt_4o_mini_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gpt_4o_realtime_new')->nullable()->default(false);
            $table->string('gpt_4o_realtime_model')->nullable()->default('gpt-4o-realtime-preview');
            $table->string('gpt_4o_realtime_title')->nullable()->default('GPT 4o Realtime');
            $table->string('gpt_4o_realtime_description')->nullable()->default('The latest most advanced model of Openai. It can solve difficult problems with greater accuracy than any of our previous models');
            $table->decimal('gpt_4o_realtime_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gpt_4o_realtime_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gpt_4o_mini_realtime_new')->nullable()->default(false);
            $table->string('gpt_4o_mini_realtime_model')->nullable()->default('gpt-4o-mini-realtime-preview');
            $table->string('gpt_4o_mini_realtime_title')->nullable()->default('GPT 4o mini Realtime');
            $table->string('gpt_4o_mini_realtime_description')->nullable()->default('The most newest and advanced model of Openai. It can solve difficult problems with greater accuracy than any of our previous models');
            $table->decimal('gpt_4o_mini_realtime_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gpt_4o_mini_realtime_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gpt_35_turbo_new')->nullable()->default(false);
            $table->string('gpt_35_turbo_model')->nullable()->default('gpt-3.5-turbo-0125');
            $table->string('gpt_35_turbo_title')->nullable()->default('GPT 3.5 Turbo');
            $table->string('gpt_35_turbo_description')->nullable()->default('Very fast. Great for most use cases. Has 16k context length');
            $table->decimal('gpt_35_turbo_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gpt_35_turbo_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('claude_3_opus_new')->nullable()->default(false);
            $table->string('claude_3_opus_model')->nullable()->default('claude-3-opus-20240229');
            $table->string('claude_3_opus_title')->nullable()->default('Claude 3 Opus');
            $table->string('claude_3_opus_description')->nullable()->default('Most powerful model for highly complex tasks. Top-level performance, intelligence, fluency, and understanding. Has 200k context window. Trained till August 2023 data.');
            $table->decimal('claude_3_opus_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('claude_3_opus_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('claude_35_sonnet_new')->nullable()->default(false);
            $table->string('claude_35_sonnet_model')->nullable()->default('claude-3-5-sonnet-20241022');
            $table->string('claude_35_sonnet_title')->nullable()->default('Claude 3.5 Sonnet');
            $table->string('claude_35_sonnet_description')->nullable()->default('Ideal balance of intelligence and speed for enterprise workloads. Has 200k context window. Trained till August 2023 data');
            $table->decimal('claude_35_sonnet_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('claude_35_sonnet_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('claude_37_sonnet_new')->nullable()->default(false);
            $table->string('claude_37_sonnet_model')->nullable()->default('claude-3-7-sonnet-20250219');
            $table->string('claude_37_sonnet_title')->nullable()->default('Claude 3.7 Sonnet');
            $table->string('claude_37_sonnet_description')->nullable()->default('Ideal balance of intelligence and speed for enterprise workloads. Has 200k context window. Trained till August 2023 data');
            $table->decimal('claude_37_sonnet_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('claude_37_sonnet_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('claude_35_haiku_new')->nullable()->default(false);
            $table->string('claude_35_haiku_model')->nullable()->default('claude-3-5-haiku-20241022');
            $table->string('claude_35_haiku_title')->nullable()->default('Claude 3.5 Haiku');
            $table->string('claude_35_haiku_description')->nullable()->default('Fastest and most compact model for near-instant responsiveness. Quick and accurate targeted performance');
            $table->decimal('claude_35_haiku_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('claude_35_haiku_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gemini_20_flash_new')->nullable()->default(false);
            $table->string('gemini_20_flash_model')->nullable()->default('gemini-2.0-flash');
            $table->string('gemini_20_flash_title')->nullable()->default('Gemini 2.0 Flash');
            $table->string('gemini_20_flash_description')->nullable()->default('Fast and versatile multimodal model for scaling across diverse tasks.');
            $table->decimal('gemini_20_flash_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gemini_20_flash_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gemini_15_flash_new')->nullable()->default(false);
            $table->string('gemini_15_flash_model')->nullable()->default('gemini-1.5-flash');
            $table->string('gemini_15_flash_title')->nullable()->default('Gemini 1.5 Flash');
            $table->string('gemini_15_flash_description')->nullable()->default('Fast and versatile multimodal model for scaling across diverse tasks.');
            $table->decimal('gemini_15_flash_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gemini_15_flash_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('gemini_15_pro_new')->nullable()->default(false);
            $table->string('gemini_15_pro_model')->nullable()->default('gemini-1.5-pro');
            $table->string('gemini_15_pro_title')->nullable()->default('Gemini 1.5 Pro');
            $table->string('gemini_15_pro_description')->nullable()->default('Googles largest and most capable AI model. Last updated in April 2023. Has 1m context window');
            $table->decimal('gemini_15_pro_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('gemini_15_pro_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('deepseek_r1_new')->nullable()->default(false);
            $table->string('deepseek_r1_model')->nullable()->default('deepseek-reasoner');
            $table->string('deepseek_r1_title')->nullable()->default('DeepSeek R1');
            $table->string('deepseek_r1_description')->nullable()->default('State-of-the-art large language model optimized with reinforcement learning and cold-start data for exceptional reasoning, math, and code performance');
            $table->decimal('deepseek_r1_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('deepseek_r1_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('deepseek_v3_new')->nullable()->default(false);
            $table->string('deepseek_v3_model')->nullable()->default('deepseek-chat');
            $table->string('deepseek_v3_title')->nullable()->default('DeepSeek V3');
            $table->string('deepseek_v3_description')->nullable()->default('Chinese open-source model that outperforms industry giants like OpenAI and Meta in code generation and understanding');
            $table->decimal('deepseek_v3_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('deepseek_v3_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('grok_2_new')->nullable()->default(false);
            $table->string('grok_2_model')->nullable()->default('grok-2-1212');
            $table->string('grok_2_title')->nullable()->default('Grok 2');
            $table->string('grok_2_description')->nullable()->default("xAIs witty AI assistant inspired by The Hitchhikers Guide to the Galaxy. Excels at answering questions with humor and suggesting insightful questions to ask.");
            $table->decimal('grok_2_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('grok_2_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('grok_2_vision_new')->nullable()->default(false);
            $table->string('grok_2_vision_model')->nullable()->default('grok-2-vision-1212');
            $table->string('grok_2_vision_title')->nullable()->default('Grok 2 Vision');
            $table->string('grok_2_vision_description')->nullable()->default("xAIs witty AI assistant inspired by The Hitchhikers Guide to the Galaxy. Excels at answering questions with humor and suggesting insightful questions to ask.");
            $table->decimal('grok_2_vision_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('grok_2_vision_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('sonar_reasoning_pro_new')->nullable()->default(false);
            $table->string('sonar_reasoning_pro_model')->nullable()->default('sonar-reasoning-pro');
            $table->string('sonar_reasoning_pro_title')->nullable()->default('Sonar Reasoning Pro');
            $table->string('sonar_reasoning_pro_description')->nullable()->default('State-of-the-art large language model optimized with reinforcement learning and cold-start data for exceptional reasoning, math, and code performance');
            $table->decimal('sonar_reasoning_pro_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('sonar_reasoning_pro_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('sonar_reasoning_new')->nullable()->default(false);
            $table->string('sonar_reasoning_model')->nullable()->default('sonar-reasoning');
            $table->string('sonar_reasoning_title')->nullable()->default('Sonar Reasoning');
            $table->string('sonar_reasoning_description')->nullable()->default('State-of-the-art large language model optimized with reinforcement learning and cold-start data for exceptional reasoning, math, and code performance');
            $table->decimal('sonar_reasoning_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('sonar_reasoning_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('sonar_pro_new')->nullable()->default(false);
            $table->string('sonar_pro_model')->nullable()->default('sonar-pro');
            $table->string('sonar_pro_title')->nullable()->default('Sonar Pro');
            $table->string('sonar_pro_description')->nullable()->default('State-of-the-art large language model optimized with reinforcement learning and cold-start data for exceptional reasoning, math, and code performance');
            $table->decimal('sonar_pro_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('sonar_pro_output_token', 10, 3)->nullable()->default(1.0);

            $table->boolean('sonar_new')->nullable()->default(false);
            $table->string('sonar_model')->nullable()->default('sonar');
            $table->string('sonar_title')->nullable()->default('Sonar');
            $table->string('sonar_description')->nullable()->default('State-of-the-art large language model optimized with reinforcement learning and cold-start data for exceptional reasoning, math, and code performance');
            $table->decimal('sonar_input_token', 10, 3)->nullable()->default(1.0);
            $table->decimal('sonar_output_token', 10, 3)->nullable()->default(1.0);

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
        Schema::dropIfExists('api_credits');
    }
};
