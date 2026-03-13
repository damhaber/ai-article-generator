<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('AIG_Provider_Ollama')) {
    class AIG_Provider_Ollama extends AIG_Provider_OpenAI_Compat_Base {
        protected string $id = 'ollama';
        protected string $label = 'Ollama';
        protected string $defaultBaseUrl = 'http://127.0.0.1:11434/v1';
    }
}
