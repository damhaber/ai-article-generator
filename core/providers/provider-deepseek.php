<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('AIG_Provider_Deepseek')) {
    class AIG_Provider_Deepseek extends AIG_Provider_OpenAI_Compat_Base {
        protected string $id = 'deepseek';
        protected string $label = 'DeepSeek';
        protected string $defaultBaseUrl = 'https://api.deepseek.com';
    }
}
