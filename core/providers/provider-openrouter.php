<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('AIG_Provider_Openrouter')) {
    class AIG_Provider_Openrouter extends AIG_Provider_OpenAI_Compat_Base {
        protected string $id = 'openrouter';
        protected string $label = 'OpenRouter';
        protected string $defaultBaseUrl = 'https://openrouter.ai/api/v1';
    }
}
