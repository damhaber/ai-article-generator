<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('AIG_Provider_Groq')) {
    class AIG_Provider_Groq extends AIG_Provider_OpenAI_Compat_Base {
        protected string $id = 'groq';
        protected string $label = 'Groq';
        protected string $defaultBaseUrl = 'https://api.groq.com/openai/v1';
    }
}
