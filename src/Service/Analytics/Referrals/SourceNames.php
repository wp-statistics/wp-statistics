<?php
namespace WP_Statistics\Service\Analytics\Referrals;

class SourceNames
{
    public static function getLlmSources()
    {
        return [
            'chatgpt'     => esc_html__('ChatGPT', 'wp-statistics'),
            'perplexity'  => esc_html__('Perplexity', 'wp-statistics'),
            'gemini'      => esc_html__('Gemini', 'wp-statistics'),
            'copilot'     => esc_html__('Copilot', 'wp-statistics'),
            'openai'      => esc_html__('OpenAI', 'wp-statistics'),
            'claude'      => esc_html__('Claude', 'wp-statistics'),
            'writesonic'  => esc_html__('WriteSonic', 'wp-statistics'),
            'copy.ai'     => esc_html__('Copy.ai', 'wp-statistics'),
            'deepseek'    => esc_html__('DeepSeek', 'wp-statistics'),
            'huggingface' => esc_html__('Hugging Face', 'wp-statistics'),
            'bard'        => esc_html__('Bard', 'wp-statistics'),
        ];
    }
}