<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('AIG_Provider_Openai')) {
    class AIG_Provider_Openai extends AIG_Provider_OpenAI_Compat_Base {
        protected string $id = 'openai';
        protected string $label = 'OpenAI';
        protected string $defaultBaseUrl = 'https://api.openai.com/v1';
    }
}
