<?php
 
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use App\Services\Statistics\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Traits\VoiceToneTrait;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\FavoriteTemplate;
use App\Models\CustomTemplate;
use App\Models\SubscriptionPlan;
use App\Models\Template;
use App\Models\Content;
use App\Models\Workbook;
use App\Models\Language;
use App\Models\ApiKey;
use App\Models\User;
use App\Models\FineTuneModel;
use App\Services\HelperService;
use App\Services\AIService;
use Exception;

class SmartEditorController extends Controller
{
    use VoiceToneTrait;

    private $api;

    public function __construct()
    {
        $this->api = new LicenseController();
    }

    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $languages = Language::orderBy('languages.language', 'asc')->get();
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();

        $favorite_templates = Template::select('templates.*', 'favorite_templates.*')->where('favorite_templates.user_id', auth()->user()->id)->join('favorite_templates', 'favorite_templates.template_code', '=', 'templates.template_code')->where('status', true)->get();  
        $favorite_custom_templates = CustomTemplate::select('custom_templates.*', 'favorite_templates.*')->where('favorite_templates.user_id', auth()->user()->id)->join('favorite_templates', 'favorite_templates.template_code', '=', 'custom_templates.template_code')->where('status', true)->get();  
        $user_templates = FavoriteTemplate::where('user_id', auth()->user()->id)->pluck('template_code');     
        $other_templates = Template::whereNotIn('template_code', $user_templates)->where('status', true)->orderBy('group', 'asc')->get();   
        $custom_templates = CustomTemplate::whereNotIn('template_code', $user_templates)->where('status', true)->orderBy('group', 'asc')->get(); 

        # Apply proper model based on role and subsciption
        if (auth()->user()->group == 'user') {
            $models = explode(',', config('settings.free_tier_models'));
        } elseif (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $models = explode(',', $plan->model);
        } else {            
            $models = explode(',', config('settings.free_tier_models'));
        }

        $fine_tunes = FineTuneModel::all();

        return view('user.smart_editor.index', compact('languages', 'favorite_templates', 'other_templates', 'custom_templates', 'favorite_custom_templates', 'workbooks', 'models', 'fine_tunes'));
    }


    /**
	*
	* Show template details
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function show(Request $request) 
    {
        if ($request->ajax()) {
 
            if ($request->type == 'original') {
                $template = Template::where('template_code', $request->code)->first();
                $fields = json_decode($template->fields, true);
            } elseif ($request->type == 'custom') {
                $template = CustomTemplate::where('template_code', $request->code)->first();
                $fields = $template->fields;
            } else {
                $data['status'] = 'error';
                return $data;
            }   


            if (auth()->user()->group == 'user') {
                if (config('settings.templates_access_user') != 'all' && config('settings.templates_access_user') != 'premium') {
                    if (is_null(auth()->user()->member_of)) {
                        if ($template->package == 'professional' && config('settings.templates_access_user') != 'professional') {                       
                            $data['status'] = 'permission';
                            $data['message'] = __('This template is not available for your account, subscribe to get a proper access');
                            return $data;                        
                        } else if ($template->package == 'premium' && (config('settings.templates_access_user') != 'premium' && config('settings.templates_access_user') != 'all')) {
                            $data['status'] = 'permission';
                            $data['message'] = __('This template is not available for your account, subscribe to get a proper access');
                            return $data;
                        } else if (($template->package == 'standard' || $template->package == 'all') && (config('settings.templates_access_user') != 'professional' && config('settings.templates_access_user') != 'standard')) {
                            $data['status'] = 'permission';
                            $data['message'] = __('This template is not available for your account, subscribe to get a proper access');
                            return $data;
                        }
                    } else {
                        $user = User::where('id', auth()->user()->member_of)->first();
                        $plan = SubscriptionPlan::where('id', $user->plan_id)->first();
                        if ($plan) {
                            if ($plan->templates != 'all' && $plan->templates != 'premium') {          
                                if ($template->package == 'premium' && ($plan->templates != 'all' && $plan->templates != 'premium')) {
                                    $data['status'] = 'permission';
                                    $data['message'] = __('Your team subscription plan does not include support for this template category');
                                    return $data;
                                } else if ($template->package == 'professional' && $plan->templates != 'professional') {
                                    $data['status'] = 'permission';
                                    $data['message'] = __('Your team subscription plan does not include support for this template category');
                                    return $data;
                                } else if(($template->package == 'standard' || $template->package == 'all') && ($plan->templates != 'standard' && $plan->templates != 'professional')) {
                                    $data['status'] = 'permission';
                                    $data['message'] = __('Your team subscription plan does not include support for this template category');
                                    return $data;
                                }                     
                            }
                        } else {
                            $data['status'] = 'permission';
                            $data['message'] = __('Your team subscription plan does not include support for this template category');
                            return $data;
                        }
                       
                    }
        
                }
            } elseif (auth()->user()->group == 'admin') {
                if (is_null(auth()->user()->plan_id)) {
                    if (config('settings.templates_access_admin') != 'all' && config('settings.templates_access_admin') != 'premium') {
                        if ($template->package == 'professional' && config('settings.templates_access_admin') != 'professional') {                       
                            $data['status'] = 'permission';
                            $data['message'] = __('This template is not available for your account, subscribe to get a proper access');
                            return $data;                        
                        } else if(($template->package == 'standard' || $template->package == 'all') && (config('settings.templates_access_admin') != 'standard' || config('settings.templates_access_admin') != 'professional')) {
                            $data['status'] = 'permission';
                            $data['message'] = __('This template is not available for your account, subscribe to get a proper access');
                            return $data;
                        } else if ($template->package == 'premium' && (config('settings.templates_access_admin') != 'all' && config('settings.templates_access_admin') != 'premium')) {
                            $data['status'] = 'permission';
                            $data['message'] = __('This template is not available for your account, subscribe to get a proper access');
                            return $data;
                        } 
                    }
                } else {
                    $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                    if ($plan->templates != 'all' && $plan->templates != 'premium') {        
                        if ($template->package == 'professional' && $plan->templates != 'professional') {
                            $data['status'] = 'permission';
                            $data['message'] = __('Your current subscription plan does not include support for this template category');
                            return $data;
                        } else if(($template->package == 'standard' || $template->package == 'all') && ($plan->templates != 'standard' && $plan->templates != 'professional')) {
                            $data['status'] = 'permission';
                            $data['message'] = __('Your current subscription plan does not include support for this template category');
                            return $data;
                        } else if ($template->package == 'premium' && ($plan->templates != 'all' && $plan->templates != 'premium')) {
                            $data['status'] = 'permission';
                            $data['message'] = __('Your current subscription plan does not include support for this template category');
                            return $data;
                        }                 
                    }
                }
            } else {
                $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($plan->templates != 'all' && $plan->templates != 'premium') {        
                    if ($template->package == 'premium' && ($plan->templates != 'all' && $plan->templates != 'premium')) {
                        $data['status'] = 'permission';
                        $data['message'] = __('Your current subscription plan does not include support for this template category');
                        return $data;
                    } else if ($template->package == 'professional' && $plan->templates != 'professional') {
                        $data['status'] = 'permission';
                        $data['message'] = __('Your current subscription plan does not include support for this template category');
                        return $data;
                    } else if(($template->package == 'standard' || $template->package == 'all') && ($plan->templates != 'professional' && $plan->templates != 'standard')) {
                        $data['status'] = 'permission';
                        $data['message'] = __('Your current subscription plan does not include support for this template category');
                        return $data;
                    }                     
                }
            }

             
            $temp = '';

            foreach ($fields as $value) {

                $temp .= '<h6 class="fs-11 mb-2 text-muted font-weight-semibold">' . __($value['name']);
                
                if (isset($value['required'])) {
                    if ($value['required']) {
                        $temp .= '<span class="text-required"><i class="fa-solid fa-asterisk"></i></span>';
                    }      
                }          

                if (isset($value['status'])) {
                    if (($value['status'] == 'required')) {
                        $temp .= '<span class="text-required"><i class="fa-solid fa-asterisk"></i></span>';
                    }
                }

                $temp .= '</h6>';
                
                $temp .= '<div class="form-group mb-5">';

                    if ($value['input'] == 'input') {
                        $temp .= '<input type="text" class="form-control" id="'. $value['code'] .'" name="'. $value['code'] .'" placeholder="'. __($value['placeholder']);
                        if (isset($value['required'])) {
                            if ($value['required']) {
                                $temp .= '" required>';
                            } else {
                                $temp .= '">';
                            }
                        } else {
                            $temp .= '">';
                        }
                    } elseif ($value['input'] == 'textarea') {
                        $temp .= '<textarea type="text" rows=5 class="form-control" id="'. $value['code'] .'" name="'. $value['code'] .'" placeholder="'. __($value['placeholder']);
                        if (isset($value['required'])) {
                            if ($value['required']) {
                                $temp .= '" required></textarea>';
                            } else {
                                $temp .= '"></textarea>';
                            }
                        } else {
                            $temp .= '"></textarea>';
                        }
                    } elseif ($value['input'] == 'select') {									
                        $temp .= '<select id="'. $value['code'] .'" name="'. $value['code'] .'" class="form-select">';
                            foreach ($value['placeholder'] as $option) {
                                $temp .= '<option value="'. $option .'">'. __($option) .'</option>';
                            }														
                        $temp .= '</select>'; 																																										
                        
                    } elseif ($value['input'] == 'checkbox') {	
                        $temp .= '<div class="checkbox-wrapper d-flex">';
                            foreach ($value['placeholder'] as $option) {
                                $temp .= '<div class="form-check fs-12 mr-4">';
                                    $temp .= '<input class="form-check-input" type="checkbox" value="'. $option .'" name="'. $value['code'] .'" id="'. $option . '">';
                                    $temp .= '<label class="form-check-label" for="'. $option .'">'. __($option) .'</label>';
                                $temp .= '</div>';
                            }													
                        $temp .= '</div>';
                    } elseif ($value['input'] == 'radio') {	
                        $temp .= '<div class="radio-wrapper d-flex">';
                            foreach ($value['placeholder'] as $option) {
                                $temp .= '<div class="form-check fs-12 mr-4">';
                                    $temp .= '<input class="form-check-input" type="radio" value="'. $option .'" name="'. $value['code'] .'" id="'. $option .'">';
                                    $temp .= '<label class="form-check-label" for="'. $option .'">'. __($option) .'</label>';
                                $temp .= '</div>';
                            }
                    
                        $temp .= '</div>';
                    }

                $temp .= '</div>';
            }
            
            $final_template = '<div class=""><div class="input-box">' . $temp . '</div></div>';

            $favorite = FavoriteTemplate::where('template_code', $template->template_code)->where('user_id', auth()->user()->id)->first();
               
            $data['status'] = 'success';
            $data['template'] = $final_template;
            $data['favorite'] = ($favorite) ? true : false;
            return $data;

        }
	}


     /**
	*
	* Process Davinci
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function generate(Request $request) 
    {

        if ($request->ajax()) {
            $prompt = '';
            $max_tokens = '';
            $counter = 1;
            $input_title = '';
            $input_keywords = '';
            $input_description = '';

            # Check personal API keys
            if (config('settings.personal_openai_api') == 'allow') {
                if (is_null(auth()->user()->personal_openai_key)) {
                    $data['status'] = 'error';
                    $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                    return $data;
                }     
            } elseif (!is_null(auth()->user()->plan_id)) {
                $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($check_api->personal_openai_api) {
                    if (is_null(auth()->user()->personal_openai_key)) {
                        $data['status'] = 'error';
                        $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                        return $data;
                    } 
                }    
            } 

            # Verify if user has enough credits
            $verify = HelperService::creditCheck($request->model, 100);
            if (isset($verify['status'])) {
                if ($verify['status'] == 'error') {
                    return $verify;
                }
            }
            
            $flag = Language::where('language_code', $request->language)->first();

            if ($request->title) {
                $input_title = $request->title;
            }

            if ($request->keywords) {
                $input_keywords = $request->keywords;
            }

            if ($request->description) {
                $input_description = $request->description;
            }

            if ($request->type == 'original') {
                $template = Template::where('template_code', $request->code)->first();

                # Generate proper prompt in respective language
                switch ($request->code) {
                    case 'KPAQQ':                    
                        request()->validate(['title' => 'required']);
                        $prompt = $this->getArticleGeneratorPrompt(strip_tags($input_title), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'JXRZB':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getParagraphGeneratorPrompt(strip_tags($input_description), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'OPYAB':                                        
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getProsAndConsPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'VFWSQ':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getTalkingPointsPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'OMMEI':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getSummarizeTextPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'HXLNA':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getProductDescriptionPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'DJSVM':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getStartupNameGeneratorPrompt(strip_tags($input_keywords), strip_tags($input_description), $request->language, $max_tokens);
                        break;
                    case 'IXKBE':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getProductNameGeneratorPrompt(strip_tags($input_keywords), strip_tags($input_description), $request->language, $max_tokens);
                        break;
                    case 'JCDIK':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getMetaDescriptionPrompt(strip_tags($input_title), strip_tags($input_keywords), strip_tags($input_description), $request->language, $max_tokens);
                        break;
                    case 'SZAUF':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getFAQsPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'BFENK':                    
                        request()->validate(['title' => 'required', 'description' => 'required', 'question' => 'required']);
                        $prompt = $this->getFAQAnswersPrompt(strip_tags($input_title), strip_tags($request->question), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'XLGPP':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getTestimonialsPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'WGKYP':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getBlogTitlesPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'EEKZF':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getBlogSectionPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'KDGOX':                    
                        request()->validate(['title' => 'required']);
                        $prompt = $this->getBlogIdeasPrompt(strip_tags($input_title), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'TZTYR':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getBlogIntrosPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'ZGUKM':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getBlogConclusionPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'WCZGL':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getContentRewriterPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'CTMNI':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getFacebookAdsPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'ZLKSP':                    
                        request()->validate(['title' => 'required']);
                        $prompt = $this->getVideoDescriptionsPrompt(strip_tags($input_title), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'OJIOV':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getVideoTitlesPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'ECNVU':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getYoutubeTagsGeneratorPrompt(strip_tags($input_description), $request->language);
                        break;
                    case 'EOASR':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getInstagramCaptionsPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'IEMBM':                    
                        request()->validate(['title' => 'required']);
                        $prompt = $this->getInstagramHashtagsPrompt(strip_tags($input_title), $request->language, $max_tokens);
                        break;
                    case 'CKOHL':                  
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getSocialPostPersonalPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'ABWGU':                    
                        request()->validate(['description' => 'required', 'title' => 'required', 'post' => 'required']);
                        $prompt = $this->getSocialPostBusinessPrompt(strip_tags($input_description), strip_tags($input_title), strip_tags($request->post), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'HJYJZ':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getFacebookHeadlinesPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'SGZTW':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getGoogleHeadlinesPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'YQAFG':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getGoogleAdsPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'BGXJE':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getPASPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'SXQBT':                    
                        request()->validate(['title' => 'required']);
                        $prompt = $this->getAcademicEssayPrompt(strip_tags($input_title), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'RLXGB':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getWelcomeEmailPrompt(strip_tags($input_title), strip_tags($input_description), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'RDJEZ':                    
                        request()->validate(['description' => 'required', 'title' => 'required']);
                        $prompt = $this->getColdEmailPrompt(strip_tags($input_title), strip_tags($input_description), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'XVNNQ':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getFollowUpEmailPrompt(strip_tags($input_title), strip_tags($input_description), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'PAKMF':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getCreativeStoriesPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'OORHD':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getGrammarCheckerPrompt(strip_tags($input_description), $request->language, $max_tokens);
                        break;
                    case 'SGJLU':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getSummarize2ndGraderPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'WISHV':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getVideoScriptsPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'WISTT':                    
                        request()->validate(['title' => 'required']);
                        $prompt = $this->getAmazonProductPrompt(strip_tags($input_title), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'LMMPR':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getTextExtenderPrompt(strip_tags($input_description), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'NJLCK':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getRewriteTextPrompt(strip_tags($input_description), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'QJGQU':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getSongLyricsPrompt(strip_tags($input_description), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'IQWZV':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getBusinessIdeasPrompt(strip_tags($input_description), $request->language);
                        break;
                    case 'NEVUR':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getLinkedinPostPrompt(strip_tags($input_description), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'MQSHO':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getCompanyBioPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'TFYLZ':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getEmailSubjectPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'CPTXT':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getProductBenefitsPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'KMKBQ':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getSellingTitlesPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'UNOEP':                    
                        request()->validate(['title' => 'required']);
                        $prompt = $this->getProductComparisonPrompt(strip_tags($input_title), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'RKYNX':                    
                        request()->validate(['title' => 'required']);
                        $prompt = $this->getProductCharacteristicsPrompt(strip_tags($input_title), strip_tags($input_keywords), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'YVEFP':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getTwitterTweetsPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'PEVVE':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getTiktokScriptsPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'WMRJR':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getLinkedinHeadlinesPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'SSWNL':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getLinkedinAdDescriptionPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'HRXVL':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getSMSNotificationPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'SYVKG':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getToneChangerPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'ETEDT':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getAmazonProductFeaturesPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'SNINY':                    
                        request()->validate(['title' => 'required']);
                        $prompt = $this->getDictionaryPrompt(strip_tags($input_title),$request->language);
                        break;
                    case 'GUXCM':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getPrivacyPolicyPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'LWOKG':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getTermsAndConditionsPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'CHJGF':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getClickbaitTitlesPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'JKTUY':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getCompanyPressReleasePrompt(strip_tags($input_title), strip_tags($input_description), strip_tags($request->audience), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'XTABO':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getProductPressReleasePrompt(strip_tags($input_title), strip_tags($input_description), strip_tags($request->audience), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'WQJYP':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getAIDAPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'APUSA':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getBABPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'AEJJV':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getPPPPPrompt(strip_tags($input_title), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'FYKJD':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getBrandNamesPrompt(strip_tags($input_description), $request->language, $max_tokens);
                        break;
                    case 'DYNJE':                    
                        request()->validate(['title' => 'required', 'description' => 'required']);
                        $prompt = $this->getAdHeadlinesPrompt(strip_tags($input_title), strip_tags($request->audience), strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    case 'SXFVD':                    
                        request()->validate(['description' => 'required']);
                        $prompt = $this->getNewsletterGeneratorPrompt(strip_tags($input_description), $request->language, $request->tone, $max_tokens);
                        break;
                    default:
                        # code...
                        break;
                }

            } elseif ($request->type == 'custom') {
                $template = CustomTemplate::where('template_code', $request->code)->first();

                if ($request->language == 'en-US') {
                    $prompt = $template->prompt;
                } else {
                    $prompt = "Provide response in " . $flag->language . '.\n\n '. $template->prompt . '\n\n';
                }
    
                if (isset($request->tone)) {
                    $prompt = $prompt . ' \n\n Voice of tone of the response must be ' . $request->tone . '.';
                }                
        
                foreach ($request->all() as $key=>$value) {
                    if (str_contains($key, 'input-field')) {
                        $prompt = str_replace('###' . $key . '###', $value, $prompt);                     
                    } 
                }
            }

            $plan_type = (auth()->user()->plan_id) ? 'paid' : 'free';
            
            $content = new Content();
            $content->user_id = auth()->user()->id;
            $content->input_text = $prompt;
            $content->language = $request->language;
            $content->language_name = $flag->language;
            $content->language_flag = $flag->language_flag;
            $content->template_code = $request->code;
            $content->template_name = $template->name;
            $content->icon = $template->icon;
            $content->group = $template->group;
            $content->tokens = 0;
            $content->plan_type = $plan_type;
            $content->model = $request->model;
            $content->save();

            $data['status'] = 'success';     
            $data['temperature'] = $request->creativity;     
            $data['id'] = $content->id;
            $data['language'] = $request->language;
            return $data;            

        }
	}


     /**
	*
	* Process Davinci
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function process(Request $request) 
    {        
        $content_id = $request->content_id;
        $temperature = $request->temperature;

        return response()->stream(function () use($content_id, $temperature) {

            $content = Content::where('id', $content_id)->first();
            $prompt = $content->input_text;  
            $model = $content->model; 

            $openai_key = AIService::getOpenAIKey();
            $text = "";

            try {
                $openai_client = \OpenAI::client($openai_key);
                    
                if (in_array($model, ['o1', 'o1-mini', 'o3-mini'])) {
                    $stream = $openai_client->chat()->createStreamed([
                        'model' => $model,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'frequency_penalty' => 0,
                        'presence_penalty' => 0,
                        'stream_options'=>[
                            'include_usage' => true,
                        ]
                    ]);
                } else {
                    $stream = $openai_client->chat()->createStreamed([
                        'model' => $model,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'frequency_penalty' => 0,
                        'presence_penalty' => 0,
                        'temperature' => (float)$temperature,                        
                        'stream_options'=>[
                            'include_usage' => true,
                        ]
                    ]);
                }

                foreach ($stream as $result) {

                    if (isset($result->choices[0]->delta->content)) {
                        $raw = $result->choices[0]->delta->content;
                        $clean = str_replace(["\r\n", "\r", "\n"], "<br/>", $raw);
                        $text .= $raw;

                        if (connection_aborted()) {
                            break;
                        }

                        echo 'data: ' . $clean;
                        echo "\n\n";
                        ob_flush();
                        flush();
                    }

                    if(isset($result->usage)){
                        $input_tokens = $result->usage->promptTokens;
                        $output_tokens = $result->usage->completionTokens; 
                    }
                }
                echo "event: stop\n";
                echo 'data: [DONE]';
                echo "\n\n";
                ob_flush();
                flush();

            } catch (Exception $e) {
                Log::error('OpenAI API Error: ' . $e->getMessage());
                echo 'data: OpenAI Notification: <span class="font-weight-bold">' . $e->getMessage() . '</span>. Please contact support team.';
                echo "\n\n";
                echo 'data: [DONE]';
                echo "\n\n";
                ob_flush();
                flush();
            }
           
            
            if (!empty($text)) {
                # Update credit balance
                $words = count(explode(' ', ($text)));
                HelperService::updateBalance($words, $model, $input_tokens, $output_tokens);   

                $content->result_text = $text;
                $content->input_tokens = $input_tokens;
                $content->output_tokens = $output_tokens;
                $content->words = $words;
                $content->save();

            }

            
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',            
        ]);

	}


    public function custom(Request $request)
    {
        # Check API keys
        if (config('settings.personal_openai_api') == 'allow') {
            if (is_null(auth()->user()->personal_openai_key)) {
                return response()->json(["status" => "error", 'message' => __('You must include your personal Openai API key in your profile settings first')]);
            } else {
                config(['openai.api_key' => auth()->user()->personal_openai_key]); 
            } 
        } elseif (!is_null(auth()->user()->plan_id)) {
            $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($check_api->personal_openai_api) {
                if (is_null(auth()->user()->personal_openai_key)) {
                    return response()->json(["status" => "error", 'message' => __('You must include your personal Openai API key in your profile settings first')]);
                } else {
                    config(['openai.api_key' => auth()->user()->personal_openai_key]); 
                }
            } else {
                if (config('settings.openai_key_usage') !== 'main') {
                   $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                   array_push($api_keys, config('services.openai.key'));
                   $key = array_rand($api_keys, 1);
                   config(['openai.api_key' => $api_keys[$key]]);
               } else {
                    config(['openai.api_key' => config('services.openai.key')]);
               }
           }
        } else {
            if (config('settings.openai_key_usage') !== 'main') {
                $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                array_push($api_keys, config('services.openai.key'));
                $key = array_rand($api_keys, 1);
                config(['openai.api_key' => $api_keys[$key]]);
            } else {
                config(['openai.api_key' => config('services.openai.key')]);
            }
        }

        if (isset($request->model)) {
            $model = $request->model;
        } elseif (isset(auth()->user()->default_model_template)) {
            $model = auth()->user()->default_model_template;
        } else {
            $model = 'gpt-4o-mini';
        }

        # Verify if user has enough credits
        $verify = HelperService::creditCheck($model, 100);
        if (isset($verify['status'])) {
            if ($verify['status'] == 'error') {
                return $verify;
            }
        }

        if ($request->content == null || $request->content == "") {
            return response()->json(["status" => "success", "message" => ""]);
        }

        if (isset($request->language)) {
            $language = Language::where('language_code', $request->language)->first();
            $prompt = $request->prompt . '. Provide response in the following language: '. $language->language;
        } else {
            $prompt = $request->prompt . '. If the task is not related to translation, return response in the language of the content text.';
        }
        

        $completion = OpenAI::chat()->create([
            'model' => $model,
            'temperature' => 0.9,
            'messages' => [[
                'role' => 'user',
                'content' => "$prompt:\n\n$request->content"
            ]]
        ]);


        $words = count(explode(' ', ($completion->choices[0]->message->content)));
        $input_token = $completion->usage->promptTokens ?? 0;
        $output_token = $completion->usage->completionTokens ?? 0;
        HelperService::updateBalance($words, $model, $input_token, $output_token); 

        return response()->json(["status" => "success", "message" => $completion->choices[0]->message->content]);
    }


    /**
	*
	* Save changes
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function save(Request $request) 
    {
        if ($request->ajax()) {

            $uploading = new UserService();
            $upload = $uploading->prompt();
            if($upload['data']!=633855){return;}    

            if ($request->id != '') {
                $document = Content::where('id', request('id'))->first(); 
            } else {
                if ($request->language != 'cmn-CN' && $request->language != 'ja-JP') {
                    $words = count(explode(' ', ($request->text)));
                } else {
                    $words = mb_strlen($request->text,'utf8');
                }

                $flag = Language::where('language_code', $request->language)->first();
                $plan_type = (auth()->user()->plan_id) ? 'paid' : 'free';
                $title = strip_tags(substr($request->text, 0, 50)) . '...';

                $content = new Content();
                $content->user_id = auth()->user()->id;
                $content->input_text = '';
                $content->language = $request->language;
                $content->language_name = $flag->language;
                $content->language_flag = $flag->language_flag;
                $content->template_code = $request->code;
                $content->template_name = 'Smart Editor';
                $content->icon = '<i class="fa-solid fa-feather smart-icon"></i>';
                $content->group = 'editor';
                $content->tokens = 0;
                $content->plan_type = $plan_type;
                $content->result_text = $request->text;
                $content->title = $title;
                $content->workbook = $request->workbook;
                $content->tokens = $words;
                $content->words = $words;
                $content->save();

                $data['status'] = 'new';
                $data['id'] = $content->id;
                return $data;  
            }

            if ($document->user_id == Auth::user()->id){
                if ($request->language != 'cmn-CN' && $request->language != 'ja-JP') {
                    $words = count(explode(' ', ($request->text)));
                } else {
                    $words = mb_strlen($request->text,'utf8');
                }

                $title = strip_tags(substr($request->text, 0, 50)) . '...';
                $document->result_text = $request->text;
                $document->title = $title;
                $document->workbook = $request->workbook;
                $document->tokens = $words;
                $document->words = $words;
                $document->save();

                $data['status'] = 'updated';
                return $data;  
    
            } else{

                $data['status'] = 'error';
                return $data;
            }  
        }
	}


    /**
	*
	* Set favorite status
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function favorite(Request $request) 
    {
        if ($request->ajax()) { 

            $favorite = FavoriteTemplate::where('template_code', $request->code)->where('user_id', auth()->user()->id)->first();

            if ($favorite) {

                $favorite->delete();

                $data['status'] = 'success';
                $data['set'] = true;
                return $data;  
    
            } else{

                $new_favorite = new FavoriteTemplate();
                $new_favorite->user_id = auth()->user()->id;
                $new_favorite->template_code = $request->code;
                $new_favorite->save();

                $data['status'] = 'success';
                $data['set'] = false;
                return $data; 
            }  
        }
	}


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getArticleGeneratorPrompt($title, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate article about " . $title . ". Focus on following keywords in the article: " . $keywords . ". The maximum length of the article must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate article about " . $title . ". Focus on following keywords in the article: " . $keywords . ". Tone of the article must be " . $tone . ". The maximum length of the article must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Generate article about " . $title . ". Focus on following keywords in the article: " . $keywords . ". The maximum length of the article must be " . $words . " words.\n\n";
            } else {
                $prompt = "Generate article about " . $title . ". Focus on following keywords in the article: " . $keywords . ". Tone of the article must be " . $tone . ". The maximum length of the article must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getParagraphGeneratorPrompt($title, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long and meaningful paragraph about " . $title . ". Use following keywords in the paragraph: " . $keywords . ". The maximum length of the paragraph must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long and meaningful paragraph about " . $title . ". Use following keywords in the paragraph: " . $keywords . ". Tone of the paragraph must be " . $tone . ". The maximum length of the paragraph must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a long and meaningful paragraph about " . $title . ". Use following keywords in the paragraph: " . $keywords . ". The maximum length of the paragraph must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a long and meaningful paragraph about " . $title . ". Use following keywords in the paragraph: " . $keywords . ". Tone of the paragraph must be " . $tone . ". The maximum length of the paragraph must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProsAndConsPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write pros and cons of these products: " . $title . ". Use following product description: " . $description . ". The maximum length of the pros and cons must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write pros and cons of these products: " . $title . ". Use following product description: " . $description . ". Tone of voice of the pros and cons must be " . $tone . ". The maximum length of the pros and cons must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write pros and cons of these products: " . $title . ". Use following product description: " . $description . ". The maximum length of the pros and cons must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write pros and cons of these products: " . $title . ". Use following product description: " . $description . ". Tone of the pros and cons must be " . $tone . ". The maximum length of the pros and cons must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTalkingPointsPrompt($title, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write short, simple and informative talking points for " . $title . ". And also similar talking points for subheadings: " . $keywords . ". The maximum length of the talking points must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write short, simple and informative talking points for " . $title . ". And also similar talking points for subheadings: " . $keywords . ". Tone of the talking points must be " . $tone . ". The maximum length of the talking points must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write short, simple and informative talking points for " . $title . ". And also similar talking points for subheadings: " . $keywords . ". The maximum length of the talking points must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write short, simple and informative talking points for " . $title . ". And also similar talking points for subheadings: " . $keywords . ". Tone of the talking points must be " . $tone . ". The maximum length of the talking points must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSummarizeTextPrompt($title, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Summarize this text in a short concise way: " . $title . ". The maximum length of the summary must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Summarize this text in a short concise way: " . $title . ". Tone of the summary must be " . $tone . ". The maximum length of the summary must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Summarize this text in a short concise way: " . $title . ". The maximum length of the summary must be " . $words . " words.\n\n";
            } else {
                $prompt = "Summarize this text in a short concise way: " . $title . ". Tone of the summary must be " . $tone . ". The maximum length of the summary must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductDescriptionPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative product description for: " . $title . ". Target audience is: " . $audience . ". Use this description: " . $description . ". The maximum length of the product description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative product description for: " . $title . ". Target audience is: " . $audience . ". Use this description: " . $description . ". Tone of the product description must be " . $tone . ". The maximum length of the product description must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a long creative product description for: " . $title . ". Target audience is: " . $audience . ". Use this description: " . $description . ". The maximum length of the product description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a long creative product description for: " . $title . ". Target audience is: " . $audience . ". Use this description: " . $description . ". Tone of the product description must be " . $tone . ". The maximum length of the product description must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getStartupNameGeneratorPrompt($keywords, $description, $language, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate cool, creative, and catchy names for startup description: " . $description . "\n\nSeed words: " . $keywords . ". The maximum length of the startup names must be " . $words . " words.\n\n";
            return $prompt;
        } else {
            $prompt = "Generate cool, creative, and catchy names for startup description: " . $description . "\n\nSeed words: " . $keywords . ". The maximum length of the startup names must be " . $words . " words.\n\n";
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductNameGeneratorPrompt($keywords, $description, $language, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create 5 creative product names: " . $description . "\n\nSeed words: " . $keywords . ". The maximum length of the product names must be " . $words . " words.\n\n";
            return $prompt;
        } else {
            $prompt = "Create 5 creative product names: " . $description . "\n\nSeed words: " . $keywords . ". The maximum length of the product names must be " . $words . " words.\n\n";
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMetaDescriptionPrompt($title, $keywords, $description, $language, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write SEO meta description for: " . $description . "\n\nWebsite name is: " . $title . "\n\nSeed words: " . $keywords . ". The maximum length of the meta description must be " . $words . " words.\n\n";
            return $prompt;
        } else {
            $prompt = "Write SEO meta description for: " . $description . "\n\nWebsite name is: " . $title . "\n\nSeed words: " . $keywords . ". The maximum length of the meta description must be " . $words . " words.\n\n";
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFAQsPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate list of 10 frequently asked questions based this description: " . $description . ". Product name:" . $title . ". The maximum length of the frequently asked questions must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate list of 10 frequently asked questions based this description: " . $description . ". Product name:" . $title . ". Tone of voice of the frequently asked questions must be " . $tone . ". The maximum length of the frequently asked questions must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Generate list of 10 frequently asked questions based this description: " . $description . ". Product name:" . $title . ". The maximum length of the frequently asked questions must be " . $words . " words.\n\n";
            } else {
                $prompt = "Generate list of 10 frequently asked questions based this description: " . $description . ". Product name:" . $title . ". Tone of the frequently asked questions must be " . $tone . ". The maximum length of the frequently asked questions must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFAQAnswersPrompt($title, $question, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate creative 5 answers this question: " . $question . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the answers must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate creative 5 answers this question: " . $question . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the answers must be " . $tone . ". The maximum length of the answers must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Generate creative 5 answers this question: " . $question . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the answers must be " . $words . " words.\n\n";
            } else {
                $prompt = "Generate creative 5 answers this question: " . $question . ". Product name: " . $title . ". Product description: " . $description . ". Tone of the answers must be " . $tone . ". The maximum length of the answers must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTestimonialsPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create creative customer reviews for this product. Product name: " . $title . ". Product description: " . $description . ". The maximum length of the customer reviews must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create creative customer reviews for this product. Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the customer reviews must be " . $tone . ". The maximum length of the customer reviews must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Create creative customer reviews for this product. Product name: " . $title . ". Product description: " . $description . ". The maximum length of the customer reviews must be " . $words . " words.\n\n";
            } else {
                $prompt = "Create creative customer reviews for this product. Product name: " . $title . ". Product description: " . $description . ". Tone of the customer reviews must be " . $tone . ". The maximum length of the customer reviews must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBlogTitlesPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate 10 catchy blog titles for: " . $description . ". The maximum length of the titles must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate 10 catchy blog titles for: " . $description . ". Tone of voice of the blog titles must be " . $tone . ". The maximum length of the blog titles must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Generate 10 catchy blog titles for: " . $description . ". The maximum length of the blog titles must be " . $words . " words.\n\n";
            } else {
                $prompt = "Generate 10 catchy blog titles for: " . $description . ". Tone of the blog titles must be " . $tone . ". The maximum length of the blog titles must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBlogSectionPrompt($title, $subheadings, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a full blog section with at least 5 large paragraphs about: " . $title . ". Split by subheadings: " . $subheadings . ". The maximum length of the blog section must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a full blog section with at least 5 large paragraphs about: " . $title . ". Split by subheadings: " . $subheadings . ". Tone of voice of the blog section must be " . $tone . ". The maximum length of the blog section must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a full blog section with at least 5 large paragraphs about: " . $title . ". Split by subheadings: " . $subheadings . ". The maximum length of the blog section must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a full blog section with at least 5 large paragraphs about: " . $title . ". Split by subheadings: " . $subheadings . ". Tone of the blog section must be " . $tone . ". The maximum length of the blog section must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBlogIdeasPrompt($title, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write interesting blog ideas and outline about: " . $title . ". The maximum length of the blog ideas must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write interesting blog ideas and outline about: " . $title . ". Tone of voice of the blog ideas must be " . $tone . ". The maximum length of the blog ideas must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write interesting blog ideas and outline about: " . $title . ". The maximum length of the blog ideas must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write interesting blog ideas and outline about: " . $title . ". Tone of the blog ideas must be " . $tone . ". The maximum length of the blog ideas must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBlogIntrosPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an interesting blog post intro about: " . $description . ". Blog post title is: " . $title . ". The maximum length of the blog intro must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an interesting blog post intro about: " . $description . ". Blog post title is: " . $title . ". Tone of voice of the blog intro must be " . $tone . ". The maximum length of the blog intro must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write an interesting blog post intro about: " . $description . ". Blog post title is: " . $title . ". The maximum length of the blog intro must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write an interesting blog post intro about: " . $description . ". Blog post title is: " . $title . ". Tone of the blog intro must be " . $tone . ". The maximum length of the blog intro must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBlogConclusionPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a comprehensive blog article conclusion for: " . $description . ". Blog article title: " . $title . ". The maximum length of the blog conclusion must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a comprehensive blog article conclusion for: " . $description . ". Blog article title: " . $title . ". Tone of voice of the blog conclusion must be " . $tone . ". The maximum length of the blog conclusion must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a comprehensive blog article conclusion for: " . $description . ". Blog article title: " . $title . ". The maximum length of the blog conclusion must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a comprehensive blog article conclusion for: " . $description . ". Blog article title: " . $title . ". Tone of the blog conclusion must be " . $tone . ". The maximum length of the blog conclusion must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getContentRewriterPrompt($title, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Improve and rewrite the text in a creative and smart way: " . $title . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Improve and rewrite the text in a creative and smart way: " . $title . ". Tone of voice of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Improve and rewrite the text in a creative and smart way: " . $title . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Improve and rewrite the text in a creative and smart way: " . $title . ". Tone of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFacebookAdsPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a creative ad for the following product to run on Facebook aimed at: " . $audience . ". Product name is: " . $title . ". Product description is: " . $description . ". The maximum length of the ad must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a creative ad for the following product to run on Facebook aimed at: " . $audience . ". Product name is: " . $title . ". Product description is: " . $description . ". Tone of voice of the ad must be " . $tone . ". The maximum length of the ad must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a creative ad for the following product to run on Facebook aimed at: " . $audience . ". Product name is: " . $title . ". Product description is: " . $description . ". The maximum length of the ad must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a creative ad for the following product to run on Facebook aimed at: " . $audience . ". Product name is: " . $title . ". Product description is: " . $description . ". Tone of voice of the ad must be " . $tone . ". The maximum length of the ad must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getVideoDescriptionsPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write compelling YouTube description to get people interested in watching. Video description: " . $description . ". The maximum length of the video description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write compelling YouTube description to get people interested in watching. Video description: " . $description . ". Tone of voice of the video description must be " . $tone . ". The maximum length of the video description must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write compelling YouTube description to get people interested in watching. Video description: " . $description . ". The maximum length of the video description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write compelling YouTube description to get people interested in watching. Video description: " . $description . ". Tone of voice of the video description must be " . $tone . ". The maximum length of the video description must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getVideoTitlesPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write compelling YouTube video title for the provided video description to get people interested in watching. Video description: " . $description . ". The maximum length of the video title must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write compelling YouTube video title for the provided video description to get people interested in watching. Video description: " . $description . ". Tone of voice of the video title must be " . $tone . ". The maximum length of the video title must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write compelling YouTube video title for the provided video description to get people interested in watching. Video description: " . $description . ". The maximum length of the video title must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write compelling YouTube video title for the provided video description to get people interested in watching. Video description: " . $description . ". Tone of voice of the video title must be " . $tone . ". The maximum length of the video title must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getYoutubeTagsGeneratorPrompt($description, $language)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            $prompt = "Provide a response in " . $target_language->language . " language.\n\n Generate SEO-optimized YouTube tags and keywords for: " . $description . ".\n\n";
            return $prompt;
        } else {
            $prompt = "Generate SEO-optimized YouTube tags and keywords for: " . $description . ".\n\n";
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInstagramCaptionsPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Grab attention with catchy captions for this Instagram post: " . $description . ". The maximum length of the caption must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Grab attention with catchy captions for this Instagram post: " . $description . ". Tone of voice of the caption must be " . $tone . ". The maximum length of the caption must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Grab attention with catchy captions for this Instagram post: " . $description . ". The maximum length of the caption must be " . $words . " words.\n\n";
            } else {
                $prompt = "Grab attention with catchy captions for this Instagram post: " . $description . ". Tone of voice of the caption must be " . $tone . ". The maximum length of the caption must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInstagramHashtagsPrompt($keyword, $language, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create hashtags to use for these Instagram keywords: " . $keyword . ".\n\n";
            return $prompt;
        } else {
            $prompt = "Create hashtags to use for these Instagram keywords: " . $keyword . ".\n\n";
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSocialPostPersonalPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a personal social media post about: " . $description . ". The maximum length of the post must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a personal social media post about: " . $description . ". Tone of voice of the post must be " . $tone . ". The maximum length of the post must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a personal social media post about: " . $description . ". The maximum length of the post must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a personal social media post about: " . $description . ". Tone of voice of the post must be " . $tone . ". The maximum length of the post must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSocialPostBusinessPrompt($description, $title, $post, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create a large professional social media post for my company. Post description: " . $post . ". Company description: " . $description . ". Company name: " . $title . ". The maximum length of the post must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create a large professional social media post for my company. Post description: " . $post . ". Company description: " . $description . ". Company name: " . $title . ". Tone of voice of the post must be " . $tone . ". The maximum length of the post must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Create a large professional social media post for my company. Post description: " . $post . ". Company description: " . $description . ". Company name: " . $title . ". The maximum length of the post must be " . $words . " words.\n\n";
            } else {
                $prompt = "Create a large professional social media post for my company. Post description: " . $post . ". Company description: " . $description . ". Company name: " . $title . ". Tone of voice of the post must be " . $tone . ". The maximum length of the post must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFacebookHeadlinesPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative headline for the following product to run on Facebook aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the headline must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative headline for the following product to run on Facebook aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the headline must be " . $tone . ". The maximum length of the headline must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a long creative headline for the following product to run on Facebook aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the headline must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a long creative headline for the following product to run on Facebook aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the headline must be " . $tone . ". The maximum length of the headline must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoogleHeadlinesPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write catchy 30-character headlines to promote your product with Google Ads. Product name: " . $title . ". Product description: " . $description . ". Target audience for ad: " . $audience . ". The maximum length of the headline must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write catchy 30-character headlines to promote your product with Google Ads. Product name: " . $title . ". Product description: " . $description . ". Target audience for ad: " . $audience . ". Tone of voice of the headline must be " . $tone . ". The maximum length of the headline must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write catchy 30-character headlines to promote your product with Google Ads. Product name: " . $title . ". Product description: " . $description . ". Target audience for ad: " . $audience . ". The maximum length of the headline must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write catchy 30-character headlines to promote your product with Google Ads. Product name: " . $title . ". Product description: " . $description . ". Target audience for ad: " . $audience . ". Tone of voice of the headline must be " . $tone . ". The maximum length of the headline must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoogleAdsPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a Google Ads description that makes your ad stand out and generates leads. Target audience: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the ad description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a Google Ads description that makes your ad stand out and generates leads. Target audience: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the ad description must be " . $tone . ". The maximum length of the ad description must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a Google Ads description that makes your ad stand out and generates leads. Target audience: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the ad description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a Google Ads description that makes your ad stand out and generates leads. Target audience: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the ad description must be " . $tone . ". The maximum length of the ad description must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPASPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write problem-agitate-solution for the following product description: " . $description . ". Product name: " . $title . ". Target audience: " . $audience . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write problem-agitate-solution for the following product description: " . $description . ". Product name: " . $title . ". Target audience: " . $audience . ". Tone of voice of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write problem-agitate-solution for the following product description: " . $description . ". Product name: " . $title . ". Target audience: " . $audience . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write problem-agitate-solution for the following product description: " . $description . ". Product name: " . $title . ". Target audience: " . $audience . ". Tone of voice of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAcademicEssayPrompt($title, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an academic essay about: " . $title . ". Use following keywords in the essay: " . $keywords . ". The maximum length of the essay must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an academic essay about: " . $title . ". Use following keywords in the essay: " . $keywords . ". Tone of voice of the essay must be " . $tone . ". The maximum length of the essay must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write an academic essay about: " . $title . ". Use following keywords in the essay: " . $keywords . ". The maximum length of the essay must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write an academic essay about: " . $title . ". Use following keywords in the essay: " . $keywords . ". Tone of voice of the essay must be " . $tone . ". The maximum length of the essay must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getWelcomeEmailPrompt($title, $description, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a welcome email about: " . $description . ". Our company or product name is: " . $title . ". Target audience is: " . $keywords . ". The maximum length of the email must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a welcome email about: " . $description . ". Our company or product name is: " . $title . ". Target audience is: " . $keywords . ". Tone of voice of the email must be " . $tone . ". The maximum length of the email must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a welcome email about: " . $description . ". Our company or product name is: " . $title . ". Target audience is: " . $keywords . ". The maximum length of the email must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a welcome email about: " . $description . ". Our company or product name is: " . $title . ". Target audience is: " . $keywords . ". Tone of voice of the email must be " . $tone . ". The maximum length of the email must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getColdEmailPrompt($title, $description, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a cold email about: " . $description . ". Our company or product name is: " . $title . ". Context to include in the cold email: " . $keywords . ". The maximum length of the email must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a cold email about: " . $description . ". Our company or product name is: " . $title . ". Context to include in the cold email: " . $keywords . ". Tone of voice of the email must be " . $tone . ". The maximum length of the email must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a cold email about: " . $description . ". Our company or product name is: " . $title . ". Context to include in the cold email: " . $keywords . ". The maximum length of the email must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a cold email about: " . $description . ". Our company or product name is: " . $title . ". Context to include in the cold email: " . $keywords . ". Tone of voice of the email must be " . $tone . ". The maximum length of the email must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFollowUpEmailPrompt($title, $description, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a follow up email about: " . $description . ". Our company or product name is: " . $title . ". Following up after: " . $keywords . ". The maximum length of the email must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a follow up email about: " . $description . ". Our company or product name is: " . $title . ". Following up after: " . $keywords . ". Tone of voice of the email must be " . $tone . ". The maximum length of the email must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a follow up email about: " . $description . ". Our company or product name is: " . $title . ". Following up after: " . $keywords . ". The maximum length of the email must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a follow up email about: " . $description . ". Our company or product name is: " . $title . ". Following up after: " . $keywords . ". Tone of voice of the email must be " . $tone . ". The maximum length of the email must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCreativeStoriesPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative story about: " . $description . ". The maximum length of the story must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative story about: " . $description . ". Tone of voice of the story must be " . $tone . ". The maximum length of the story must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a long creative story about: " . $description . ". The maximum length of the story must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a long creative story about: " . $description . ". Tone of voice of the story must be " . $tone . ". The maximum length of the story must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getGrammarCheckerPrompt($description, $language)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            $prompt = "Provide a response in " . $target_language->language . " language.\n\n Check and correct grammar of this text: " . $description . "\n\n";
            return $prompt;
        } else {
            $prompt = "Check and correct grammar of this text: " . $description . "\n\n";
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSummarize2ndGraderPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Summarize this text for 2nd grader: " . $description . ". The maximum length of the summary must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Summarize this text for 2nd grader: " . $description . ". Tone of voice of the summary must be " . $tone . ". The maximum length of the summary must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Summarize this text for 2nd grader: " . $description . ". The maximum length of the summary must be " . $words . " words.\n\n";
            } else {
                $prompt = "Summarize this text for 2nd grader: " . $description . ". Tone of voice of the summary must be " . $tone . ". The maximum length of the summary must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getVideoScriptsPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an interesting video script about: " . $description . ". The maximum length of the video script must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an interesting video script about: " . $description . ". Tone of voice of the video script must be " . $tone . ". The maximum length of the video script must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write an interesting video script about: " . $description . ". The maximum length of the video script must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write an interesting video script about: " . $description . ". Tone of voice of the video script must be " . $tone . ". The maximum length of the video script must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAmazonProductPrompt($title, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write attention grabbing Amazon marketplace product description for: " . $title . ". Use following keywords in the product description: " . $keywords . ". The maximum length of the product description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write attention grabbing Amazon marketplace product description for: " . $title . ". Use following keywords in the product description: " . $keywords . ". Tone of voice of the product description must be " . $tone . ". The maximum length of the product description must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write attention grabbing Amazon marketplace product description for: " . $title . ". Use following keywords in the product description: " . $keywords . ". The maximum length of the product description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write attention grabbing Amazon marketplace product description for: " . $title . ". Use following keywords in the product description: " . $keywords . ". Tone of voice of the product description must be " . $tone . ". The maximum length of the product description must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTextExtenderPrompt($description, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Extend this text further with more creative content: " . $description . ". Use following keywords in the extended text: " . $keywords . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Extend this text further with more creative content: " . $description . ". Use following keywords in the extended text: " . $keywords . ". Tone of voice of the extended text must be " . $tone . ". The maximum length of the extended text must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Extend this text further with more creative content: " . $description . ". Use following keywords in the extended text: " . $keywords . ". The maximum length of the extended text must be " . $words . " words.\n\n";
            } else {
                $prompt = "Extend this text further with more creative content: " . $description . ". Use following keywords in the extended text: " . $keywords . ". Tone of voice of the extended text must be " . $tone . ". The maximum length of the extended text must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRewriteTextPrompt($description, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Rewrite this text in a more creative way: " . $description . ". Use following keywords in the text: " . $keywords . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Rewrite this text in a more creative way: " . $description . ". Use following keywords in the text: " . $keywords . ". Tone of voice of the extended text must be " . $tone . ". The maximum length of the extended text must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Rewrite this text in a more creative way: " . $description . ". Use following keywords in the text: " . $keywords . ". The maximum length of the text must be " . $words . " words.\n\n";
            } else {
                $prompt = "Rewrite this text in a more creative way: " . $description . ". Use following keywords in the text: " . $keywords . ". Tone of voice of the text must be " . $tone . ". The maximum length of the text must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSongLyricsPrompt($description, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a nice song lyrics that rhyme well about: " . $description . ". Use following keywords in the lyrics: " . $keywords . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a nice song lyrics that rhyme well about: " . $description . ". Use following keywords in the lyrics: " . $keywords . ". Tone of voice of the lyrics must be " . $tone . ". The maximum length of the extended lyrics must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a nice song lyrics that rhyme well about: " . $description . ". Use following keywords in the lyrics: " . $keywords . ". The maximum length of the lyrics must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a nice song lyrics that rhyme well about: " . $description . ". Use following keywords in the lyrics: " . $keywords . ". Tone of voice of the lyrics must be " . $tone . ". The maximum length of the lyrics must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBusinessIdeasPrompt($description, $language)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            $prompt = "Provide a response in " . $target_language->language . " language.\n\n Suggest innovative business ideas for this industry description: " . $description . "\n\n";
            return $prompt;
        } else {
            $prompt = "Suggest innovative business ideas for this industry description: " . $description . "\n\n";       
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLinkedinPostPrompt($description, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an inspiring linkedin post about: " . $description . ". Use following keywords in the post: " . $keywords . ". The maximum length of the post must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an inspiring linkedin post about: " . $description . ". Use following keywords in the post: " . $keywords . ". Tone of voice of the post must be " . $tone . ". The maximum length of the post must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write an inspiring linkedin post about: " . $description . ". Use following keywords in the post: " . $keywords . ". The maximum length of the post must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write an inspiring linkedin post about: " . $description . ". Use following keywords in the post: " . $keywords . ". Tone of voice of the post must be " . $tone . ". The maximum length of the post must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCompanyBioPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write clear and interesting company bio. Company name: " . $title . ". Company description: " . $description . ". The maximum length of the bio must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write clear and interesting company bio. Company name: " . $title . ". Company description: " . $description . ". Tone of voice of the post must be " . $tone . ". The maximum length of the post must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write clear and interesting company bio. Company name: " . $title . ". Company description: " . $description . ". The maximum length of the bio must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write clear and interesting company bio. Company name: " . $title . ". Company description: " . $description . ". Tone of voice of the bio must be " . $tone . ". The maximum length of the bio must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getEmailSubjectPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an attention grabbing email subject line for: " . $description . ". The maximum length of the subject line must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an attention grabbing email subject line for: " . $description . ". Tone of voice of the subject line must be " . $tone . ". The maximum length of the subject line must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write an attention grabbing email subject line for: " . $description . ". The maximum length of the subject line must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write an attention grabbing email subject line for: " . $description . ". Tone of voice of the subject line must be " . $tone . ". The maximum length of the subject line must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductBenefitsPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create 10 unique and intersting product benefits. Product name: " . $title . ". Product description: " . $description . ". The maximum length of the product benefits must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create 10 unique and intersting product benefits. Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the product benefits must be " . $tone . ". The maximum length of the product benefits must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Create 10 unique and intersting product benefits. Product name: " . $title . ". Product description: " . $description . ". The maximum length of the product benefits must be " . $words . " words.\n\n";
            } else {
                $prompt = "Create 10 unique and intersting product benefits. Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the product benefits must be " . $tone . ". The maximum length of the product benefits must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSellingTitlesPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write the most attention grabbing 5 selling titles. Product name: " . $title . ". Product description: " . $description . ". The maximum length of the titles must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write the most attention grabbing 5 selling titles. Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the titles must be " . $tone . ". The maximum length of the titles must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write the most attention grabbing 5 selling titles. Product name: " . $title . ". Product description: " . $description . ". The maximum length of the titles must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write the most attention grabbing 5 selling titles. Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the titles must be " . $tone . ". The maximum length of the titles must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductComparisonPrompt($title, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a detailed product comparison between these products: " . $title . ". The maximum length of the comparison must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a detailed product comparison between these products: " . $title . ". Tone of voice of the comparison must be " . $tone . ". The maximum length of the comparison must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a detailed product comparison between these products: " . $title . ". The maximum length of the comparison must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a detailed product comparison between these products: " . $title . ". Tone of voice of the comparison must be " . $tone . ". The maximum length of the comparison must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductCharacteristicsPrompt($title, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write detailed list of product characteristics for: " . $title . ". User following keywords: " . $keywords . ". The maximum length of the characteristics must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write detailed list of product characteristics for: " . $title . ". User following keywords: " . $keywords . ". Tone of voice of the characteristics must be " . $tone . ". The maximum length of the characteristics must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write detailed list of product characteristics for: " . $title . ". User following keywords: " . $keywords . ". The maximum length of the characteristics must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write detailed list of product characteristics for: " . $title . ". User following keywords: " . $keywords . ". Tone of voice of the characteristics must be " . $tone . ". The maximum length of the characteristics must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTwitterTweetsPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a trending tweet for a Twitter post about: " . $description . ". The maximum length of the tweet must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a trending tweet for a Twitter post about: " . $description . ". Tone of voice of the tweet must be " . $tone . ". The maximum length of the tweet must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a trending tweet for a Twitter post about: " . $description . ". The maximum length of the tweet must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a trending tweet for a Twitter post about: " . $description . ". Tone of voice of the tweet must be " . $tone . ". The maximum length of the tweet must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTiktokScriptsPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a creating step by stepvideo scripts  with actions for each step. Video is about: " . $description . ". The maximum length of the idea must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a creating step by stepvideo scripts  with actions for each step. Video is about: " . $description . ". Tone of voice of the idea must be " . $tone . ". The maximum length of the idea must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a creating step by step video scripts with actions for each step. Video is about: " . $description . ". The maximum length of the idea must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a creating step by step video scripts with actions for each step. Video is about: " . $description . ". Tone of voice of the idea must be " . $tone . ". The maximum length of the idea must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLinkedinHeadlinesPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative headline for the following product to run on Linkedin aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the headline must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative headline for the following product to run on Linkedin aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the headline must be " . $tone . ". The maximum length of the headline must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a long creative headline for the following product to run on Linkedin aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the headline must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a long creative headline for the following product to run on Linkedin aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the headline must be " . $tone . ". The maximum length of the headline must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLinkedinAdDescriptionPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a Linkedin Ads description that makes your ad stand out and generates leads. Target audience: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the ad description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a Linkedin Ads description that makes your ad stand out and generates leads. Target audience: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the ad description must be " . $tone . ". The maximum length of the ad description must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a Linkedin Ads description that makes your ad stand out and generates leads. Target audience: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the ad description must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a Linkedin Ads description that makes your ad stand out and generates leads. Target audience: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the ad description must be " . $tone . ". The maximum length of the ad description must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSMSNotificationPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create 10 eye catching notification messages about: " . $description . ". The maximum length of the messages must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create 10 eye catching notification messages about: " . $description . ". Tone of voice of the messages must be " . $tone . ". The maximum length of the messages must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Create 10 eye catching notification messages about: " . $description . ". The maximum length of the messages must be " . $words . " words.\n\n";
            } else {
                $prompt = "Create 10 eye catching notification messages about: " . $description . ". Tone of voice of the messages must be " . $tone . ". The maximum length of the messages must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getToneChangerPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Change tone of voice of this text: " . $description . "\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Change tone of voice of this text: " . $description . ". Tone of voice of must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Change tone of voice of this text: " . $description . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Change tone of voice of this text: " . $description . ". Tone of voice of must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAmazonProductFeaturesPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a interesting and detailed product descriptions to gain more sells on Amazon for the following product aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the product descriptions must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a interesting and detailed product descriptions to gain more sells on Amazon for the following product aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the product descriptions must be " . $tone . ". The maximum length of the product descriptions must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a interesting and detailed product descriptions to gain more sells on Amazon for the following product aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the product descriptions must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a interesting and detailed product descriptions to gain more sells on Amazon for the following product aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the product descriptions must be " . $tone . ". The maximum length of the product descriptions must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDictionaryPrompt($title, $language)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            $prompt = "Provide a response in " . $target_language->language . " language.\n\n Act as an advanced vocabulary dictionary. Provide full breakdown details of this word as a vocabulary dictionary. Target word: " . $title . "\n\n";
            return $prompt;
        } else {
            $prompt = "Act as an advanced vocabulary dictionary. Provide full breakdown details of this word as a vocabulary dictionary. Target word: " . $title . ".\n\n";       
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPrivacyPolicyPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long and detailed privacy policy with sub-sections for each points. Company name: " . $title . ". Use following description for creating a privacy policy: " . $description . ". The maximum length of the privacy policy must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long and detailed privacy policy with sub-sections for each points. Company name: " . $title . ". Use following description for creating a privacy policy: " . $description . ". Tone of voice of the privacy policy must be " . $tone . ". The maximum length of the privacy policy must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a long and detailed privacy policy with sub-sections for each points. Company name: " . $title . ". Use following company details for creating a privacy policy: " . $description . ". The maximum length of the privacy policy must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a long and detailed privacy policy with sub-sections for each points. Company name: " . $title . ". Use following company details for creating a privacy policy: " . $description . ". Tone of voice of the privacy policy must be " . $tone . ". The maximum length of the privacy policy must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTermsAndConditionsPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long and detailed terms and conditions page with a sub-sections for each points. Company name: " . $title . ". Use following description for creating a terms and conditions pages: " . $description . ". The maximum length of the terms and conditions page must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long and detailed terms and conditions page with a sub-sections for each points. Company name: " . $title . ". Use following description for creating a terms and conditions pages: " . $description . ". Tone of voice of the terms and conditions page must be " . $tone . ". The maximum length of the terms and conditions page must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a long and detailed terms and conditions page with a sub-sections for each points. Company name: " . $title . ". Use following company details for creating a terms and conditions pages: " . $description . ". The maximum length of the terms and conditions page must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a long and detailed terms and conditions page with a sub-sections for each points. Company name: " . $title . ". Use following company details for creating a terms and conditions pages: " . $description . ". Tone of voice of the terms and conditions page must be " . $tone . ". The maximum length of the terms and conditions page must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getClickbaitTitlesPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create 5 attention grabbing and sale generating clickbait titles for this product description: " . $description . ". The maximum length of the titles must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create 5 attention grabbing and sale generating clickbait titles for this product description: " . $description . ". Tone of voice of the titles must be " . $tone . ". The maximum length of the titles must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Create 5 attention grabbing and sale generating clickbait titles for this product description: " . $description . ". The maximum length of the titles must be " . $words . " words.\n\n";
            } else {
                $prompt = "Create 5 attention grabbing and sale generating clickbait titles for this product description: " . $description . ". Tone of voice of the titles must be " . $tone . ". The maximum length of the titles must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCompanyPressReleasePrompt($title, $description, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a detailed and interesting company press release about: " . $keywords . ". Company name: " . $title . ". Company information: " . $description . ". The maximum length of the press release must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a detailed and interesting company press release about: " . $keywords . ". Company name: " . $title . ". Company information: " . $description . ". Tone of voice of the press release must be " . $tone . ". The maximum length of the press release must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a detailed and interesting company press release about: " . $keywords . ". Company name: " . $title . ". Company information: " . $description . ". The maximum length of the press release must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a detailed and interesting company press release about: " . $keywords . ". Company name: " . $title . ". Company information: " . $description . ". Tone of voice of the press release must be " . $tone . ". The maximum length of the press release must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


    /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductPressReleasePrompt($title, $description, $keywords, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a detailed and interesting product press release about: " . $keywords . ". Product name: " . $title . ". Product information: " . $description . ". The maximum length of the press release must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a detailed and interesting product press release about: " . $keywords . ". Product name: " . $title . ". Product information: " . $description . ". Tone of voice of the press release must be " . $tone . ". The maximum length of the press release must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a detailed and interesting product press release about: " . $keywords . ". Product name: " . $title . ". Product information: " . $description . ". The maximum length of the press release must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a detailed and interesting product press release about: " . $keywords . ". Product name: " . $title . ". Product information: " . $description . ". Tone of voice of the press release must be " . $tone . ". The maximum length of the press release must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAIDAPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Use copywriting formula: Attention-Interest-Desire-Action (AIDA) Framework to write a clear user actions for this product: " . $title . ". Product description: " . $description . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Use copywriting formula: Attention-Interest-Desire-Action (AIDA) Framework to write a clear user actions for this product: " . $title . ". Product description: " . $description . ". Tone of voice of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Use copywriting formula: Attention-Interest-Desire-Action (AIDA) Framework to write a clear user actions for this product: " . $title . ". Product description: " . $description . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Use copywriting formula: Attention-Interest-Desire-Action (AIDA) Framework to write a clear user actions for this product: " . $title . ". Product description: " . $description . ". Tone of voice of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }

     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBABPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Use copywriting formula: Before–After–Bridge (BAB) Framework, to write a appealing marketing statement for this product: " . $title . ". Product description: " . $description . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Use copywriting formula: Before–After–Bridge (BAB) Framework, to write a appealing marketing statement for this product: " . $title . ". Product description: " . $description . ". Tone of voice of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Use copywriting formula: Before–After–Bridge (BAB) Framework, to write a appealing marketing statement for this product: " . $title . ". Product description: " . $description . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Use copywriting formula: Before–After–Bridge (BAB) Framework, to write a appealing marketing statement for this product: " . $title . ". Product description: " . $description . ". Tone of voice of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPPPPPrompt($title, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Use copywriting 4P formula: Promise–Picture–Proof–Push (PPPP) Framework, to to craft persuasive content that moves readers to action. Produt name: " . $title . ". Product description: " . $description . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Use copywriting 4P formula: Promise–Picture–Proof–Push (PPPP) Framework, to to craft persuasive content that moves readers to action. Produt name: " . $title . ". Product description: " . $description . ". Tone of voice of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Use copywriting 4P formula: Promise–Picture–Proof–Push (PPPP) Framework, to to craft persuasive content that moves readers to action. Produt name: " . $title . ". Product description: " . $description . ". The maximum length of the result must be " . $words . " words.\n\n";
            } else {
                $prompt = "Use copywriting 4P formula: Promise–Picture–Proof–Push (PPPP) Framework, to to craft persuasive content that moves readers to action. Produt name: " . $title . ". Product description: " . $description . ". Tone of voice of the result must be " . $tone . ". The maximum length of the result must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBrandNamesPrompt($description, $language, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            $prompt = "Provide a response in " . $target_language->language . " language.\n\n Create creative and unique brand names for: " . $description . ". The maximum length of the brand names must be " . $words . " words.\n\n";
            return $prompt;
        } else {
            $prompt = "Create creative and unique brand names for: " . $description . ". The maximum length of the brand names must be " . $words . " words.\n\n";
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAdHeadlinesPrompt($title, $audience, $description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative ad headline for the following product aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the headline must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write a long creative ad headline for the following product aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the headline must be " . $tone . ". The maximum length of the headline must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write a long creative ad headline for the following product aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". The maximum length of the headline must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write a long creative ad headline for the following product aimed at: " . $audience . ". Product name: " . $title . ". Product description: " . $description . ". Tone of voice of the headline must be " . $tone . ". The maximum length of the headline must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }


     /** 
     * Generate template prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function getNewsletterGeneratorPrompt($description, $language, $tone, $words)
    {   
        if ($language != 'en-US') {
            $target_language = Language::where('language_code', $language)->first();
            if ($tone == 'none') {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an interesting and long newsletter about: " . $description . ". The maximum length of the newsletter must be " . $words . " words.\n\n";
            } else {
                $prompt = "Provide a response in " . $target_language->language . " language.\n\n Write an interesting and long newsletter about: " . $description . ". Tone of voice of the newsletter must be " . $tone . ". The maximum length of the newsletter must be " . $words . " words.\n\n";
            }
            return $prompt;
        } else {
            if ($tone == 'none') {
                $prompt = "Write an interesting and long newsletter about: " . $description . ". The maximum length of the newsletter must be " . $words . " words.\n\n";
            } else {
                $prompt = "Write an interesting and long newsletter about: " . $description . ". Tone of voice of the newsletter must be " . $tone . ". The maximum length of the newsletter must be " . $words . " words.\n\n";
            }           
            return $prompt;
        }
    }

}
