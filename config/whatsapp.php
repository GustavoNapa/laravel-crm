<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Evolution API Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com Evolution API do WhatsApp
    |
    */

    'evolution_base_url' => env('EVOLUTION_BASE_URL', 'http://localhost:8080'),
    'evolution_token'    => env('EVOLUTION_TOKEN', ''),
    'instance_name'      => env('WHATSAPP_INSTANCE_NAME', 'quarkions_instance'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com OpenAI (agentes IA)
    |
    */

    'openai_api_key' => env('OPENAI_API_KEY', ''),
    'openai_model'   => env('OPENAI_MODEL', 'gpt-3.5-turbo'),

    /*
    |--------------------------------------------------------------------------
    | ElevenLabs Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para síntese de voz com ElevenLabs
    |
    */

    'elevenlabs_api_key'  => env('ELEVENLABS_API_KEY', ''),
    'elevenlabs_voice_id' => env('ELEVENLABS_VOICE_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Agent Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações dos agentes IA
    |
    */

    'agents' => [
        'isis' => [
            'name'        => 'Isis SDR',
            'type'        => 'sdr_professional',
            'voice_id'    => env('ISIS_VOICE_ID', ''),
            'prompt_base' => 'Você é Isis, uma agente de vendas especializada em SPIN Selling...',
        ],
        'bruna' => [
            'name'        => 'Bruna BDR',
            'type'        => 'bdr_followup',
            'voice_id'    => env('BRUNA_VOICE_ID', ''),
            'prompt_base' => 'Você é Bruna, especialista em disparos em massa e follow-up...',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações de mensagens e follow-up
    |
    */

    'message_delay'           => env('MESSAGE_DELAY_SECONDS', 15),
    'followup_interval_hours' => env('FOLLOWUP_INTERVAL_HOURS', 24),
    'max_followup_attempts'   => env('MAX_FOLLOWUP_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Business Hours
    |--------------------------------------------------------------------------
    |
    | Horários de funcionamento para envio de mensagens
    |
    */

    'business_hours' => [
        'start' => env('BUSINESS_START_HOUR', 8),
        'end'   => env('BUSINESS_END_HOUR', 20),
        'days'  => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Limites de envio de mensagens
    |
    */

    'rate_limits' => [
        'messages_per_day'  => env('MESSAGES_PER_DAY', 100),
        'messages_per_hour' => env('MESSAGES_PER_HOUR', 20),
        'bulk_send_limit'   => env('BULK_SEND_LIMIT', 100),
    ],
];
