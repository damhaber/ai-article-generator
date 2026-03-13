<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('AIG_Provider_Gemini')) {
    class AIG_Provider_Gemini extends AIG_Provider_OpenAI_Compat_Base {
        protected string $id = 'gemini';
        protected string $label = 'Google Gemini';
        protected string $defaultBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai';
    }
}
