<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('AIG_Provider_Mistral')) {
    class AIG_Provider_Mistral extends AIG_Provider_OpenAI_Compat_Base {
        protected string $id = 'mistral';
        protected string $label = 'Mistral';
        protected string $defaultBaseUrl = 'https://api.mistral.ai/v1';
    }
}
