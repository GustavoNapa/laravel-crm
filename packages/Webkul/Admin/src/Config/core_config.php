<?php

return [
    /**
     * General.
     */
    [
        'key'  => 'general',
        'name' => 'admin::app.configuration.index.general.title',
        'info' => 'admin::app.configuration.index.general.info',
        'sort' => 1,
    ], [
        'key'  => 'general.general',
        'name' => 'admin::app.configuration.index.general.general.title',
        'info' => 'admin::app.configuration.index.general.general.info',
        'icon' => 'icon-setting',
        'sort' => 1,
    ], [
        'key'    => 'general.general.locale_settings',
        'name'   => 'admin::app.configuration.index.general.general.locale-settings.title',
        'info'   => 'admin::app.configuration.index.general.general.locale-settings.title-info',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'locale',
                'title'   => 'admin::app.configuration.index.general.general.locale-settings.title',
                'type'    => 'select',
                'default' => 'en',
                'options' => 'Webkul\Core\Core@locales',
            ],
        ],
    ], [
        'key'    => 'general.general.admin_logo',
        'name'   => 'admin::app.configuration.index.general.general.admin-logo.title',
        'info'   => 'admin::app.configuration.index.general.general.admin-logo.title-info',
        'sort'   => 2,
        'fields' => [
            [
                'name'          => 'logo_image',
                'title'         => 'admin::app.configuration.index.general.general.admin-logo.logo-image',
                'type'          => 'image',
                'validation'    => 'mimes:bmp,jpeg,jpg,png,webp,svg',
            ],
        ],
    ], [
        'key'    => 'general.settings',
        'name'   => 'admin::app.configuration.index.general.settings.title',
        'info'   => 'admin::app.configuration.index.general.settings.info',
        'icon'   => 'icon-configuration',
        'sort'   => 2,
    ], [
        'key'    => 'general.settings.footer',
        'name'   => 'admin::app.configuration.index.general.settings.footer.title',
        'info'   => 'admin::app.configuration.index.general.settings.footer.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'       => 'label',
                'title'      => 'admin::app.configuration.index.general.settings.footer.powered-by',
                'type'       => 'editor',
                'default'    => 'Powered by <span style="color: rgb(14, 144, 217);"><a href="http://www.krayincrm.com" target="_blank">Krayin</a></span>, an open-source project by <span style="color: rgb(14, 144, 217);"><a href="https://webkul.com" target="_blank">Webkul</a></span>.',
                'tinymce'    => true,
            ],
        ],
    ], [
        'key'    => 'general.settings.menu',
        'name'   => 'admin::app.configuration.index.general.settings.menu.title',
        'info'   => 'admin::app.configuration.index.general.settings.menu.info',
        'sort'   => 2,
        'fields' => [
            [
                'name'       => 'dashboard',
                'title'      => 'admin::app.configuration.index.general.settings.menu.dashboard',
                'type'       => 'text',
                'default'    => 'Dashboard',
                'validation' => 'max:20',
            ], [
                'name'       => 'leads',
                'title'      => 'admin::app.configuration.index.general.settings.menu.leads',
                'type'       => 'text',
                'default'    => 'Leads',
                'validation' => 'max:20',
            ], [
                'name'       => 'quotes',
                'title'      => 'admin::app.configuration.index.general.settings.menu.quotes',
                'type'       => 'text',
                'default'    => 'Quotes',
                'validation' => 'max:20',
            ], [
                'name'       => 'mail.mail',
                'title'      => 'admin::app.configuration.index.general.settings.menu.mail',
                'type'       => 'text',
                'default'    => 'Mail',
                'validation' => 'max:20',
            ], [
                'name'       => 'mail.inbox',
                'title'      => 'admin::app.configuration.index.general.settings.menu.inbox',
                'type'       => 'text',
                'default'    => 'Inbox',
                'validation' => 'max:20',
            ], [
                'name'       => 'mail.draft',
                'title'      => 'admin::app.configuration.index.general.settings.menu.draft',
                'type'       => 'text',
                'default'    => 'Draft',
                'validation' => 'max:20',
            ], [
                'name'       => 'mail.outbox',
                'title'      => 'admin::app.configuration.index.general.settings.menu.outbox',
                'type'       => 'text',
                'default'    => 'Outbox',
                'validation' => 'max:20',
            ], [
                'name'       => 'mail.sent',
                'title'      => 'admin::app.configuration.index.general.settings.menu.sent',
                'type'       => 'text',
                'default'    => 'Sent',
                'validation' => 'max:20',
            ], [
                'name'       => 'mail.trash',
                'title'      => 'admin::app.configuration.index.general.settings.menu.trash',
                'type'       => 'text',
                'default'    => 'Trash',
                'validation' => 'max:20',
            ], [
                'name'       => 'activities',
                'title'      => 'admin::app.configuration.index.general.settings.menu.activities',
                'type'       => 'text',
                'default'    => 'Activities',
                'validation' => 'max:20',
            ], [
                'name'       => 'contacts.contacts',
                'title'      => 'admin::app.configuration.index.general.settings.menu.contacts',
                'type'       => 'text',
                'default'    => 'Contacts',
                'validation' => 'max:20',
            ], [
                'name'       => 'contacts.persons',
                'title'      => 'admin::app.configuration.index.general.settings.menu.persons',
                'type'       => 'text',
                'default'    => 'Persons',
                'validation' => 'max:20',
            ], [
                'name'       => 'contacts.organizations',
                'title'      => 'admin::app.configuration.index.general.settings.menu.organizations',
                'type'       => 'text',
                'default'    => 'Organizations',
                'validation' => 'max:20',
            ], [
                'name'       => 'products',
                'title'      => 'admin::app.configuration.index.general.settings.menu.products',
                'type'       => 'text',
                'default'    => 'Products',
                'validation' => 'max:20',
            ], [
                'name'       => 'settings',
                'title'      => 'admin::app.configuration.index.general.settings.menu.settings',
                'type'       => 'text',
                'default'    => 'Settings',
                'validation' => 'max:20',
            ], [
                'name'       => 'configuration',
                'title'      => 'admin::app.configuration.index.general.settings.menu.configuration',
                'type'       => 'text',
                'default'    => 'Configuration',
                'validation' => 'max:20',
            ],
        ],
    ], [
        'key'    => 'general.settings.menu_color',
        'name'   => 'admin::app.configuration.index.general.settings.menu-color.title',
        'info'   => 'admin::app.configuration.index.general.settings.menu-color.info',
        'sort'   => 3,
        'fields' => [
            [
                'name'    => 'brand_color',
                'title'   => 'admin::app.configuration.index.general.settings.menu-color.brand-color',
                'type'    => 'color',
                'default' => '#0E90D9',
            ],
        ],
    ], [
        'key'  => 'general.magic_ai',
        'name' => 'admin::app.configuration.index.magic-ai.title',
        'info' => 'admin::app.configuration.index.magic-ai.info',
        'icon' => 'icon-setting',
        'sort' => 3,
    ], [
        'key'    => 'general.magic_ai.settings',
        'name'   => 'admin::app.configuration.index.magic-ai.settings.title',
        'info'   => 'admin::app.configuration.index.magic-ai.settings.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'enable',
                'title'         => 'admin::app.configuration.index.magic-ai.settings.enable',
                'type'          => 'boolean',
                'channel_based' => true,
            ], [
                'name'          => 'api_key',
                'title'         => 'admin::app.configuration.index.magic-ai.settings.api-key',
                'type'          => 'password',
                'depends'       => 'enable:1',
                'validation'    => 'required_if:enable,1',
                'info'          => 'admin::app.configuration.index.magic-ai.settings.api-key-info',
            ], [
                'name'          => 'model',
                'title'         => 'admin::app.configuration.index.magic-ai.settings.models.title',
                'type'          => 'select',
                'channel_based' => true,
                'depends'       => 'enable:1',
                'options'       => [
                    [
                        'title' => 'admin::app.configuration.index.magic-ai.settings.models.gpt-4o',
                        'value' => 'openai/chatgpt-4o-latest',
                    ], [
                        'title' => 'admin::app.configuration.index.magic-ai.settings.models.gpt-4o-mini',
                        'value' => 'openai/gpt-4o-mini',
                    ], [
                        'title' => 'admin::app.configuration.index.magic-ai.settings.models.gemini-2-0-flash-001',
                        'value' => 'google/gemini-2.0-flash-001',
                    ], [
                        'title' => 'admin::app.configuration.index.magic-ai.settings.models.deepseek-r1',
                        'value' => 'deepseek/deepseek-r1-distill-llama-8b',
                    ], [
                        'title' => 'admin::app.configuration.index.magic-ai.settings.models.llama-3-2-3b-instruct',
                        'value' => 'meta-llama/llama-3.2-3b-instruct',
                    ], [
                        'title' => 'admin::app.configuration.index.magic-ai.settings.models.grok-2-1212',
                        'value' => 'x-ai/grok-2-1212',
                    ],
                ],
            ], [
                'name'          => 'other_model',
                'title'         => 'admin::app.configuration.index.magic-ai.settings.other',
                'type'          => 'text',
                'info'          => 'admin::app.configuration.index.magic-ai.settings.other-model',
                'default'       => null,
                'depends'       => 'enable:1',
            ],
        ],
    ], [
        'key'    => 'general.magic_ai.doc_generation',
        'name'   => 'admin::app.configuration.index.magic-ai.settings.doc-generation',
        'info'   => 'admin::app.configuration.index.magic-ai.settings.doc-generation-info',
        'sort'   => 2,
        'fields' => [
            [
                'name'          => 'enabled',
                'title'         => 'admin::app.configuration.index.magic-ai.settings.enable',
                'type'          => 'boolean',
            ],
        ],
    ],

    /**
     * Email.
     */
    [
        'key'  => 'email',
        'name' => 'admin::app.configuration.index.email.title',
        'info' => 'admin::app.configuration.index.email.info',
        'sort' => 2,
    ], [
        'key'  => 'email.imap',
        'name' => 'admin::app.configuration.index.email.imap.title',
        'info' => 'admin::app.configuration.index.email.imap.info',
        'icon' => 'icon-setting',
        'sort' => 1,
    ], [
        'key'    => 'email.imap.account',
        'name'   => 'admin::app.configuration.index.email.imap.account.title',
        'info'   => 'admin::app.configuration.index.email.imap.account.title-info',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'host',
                'title'   => 'admin::app.configuration.index.email.imap.account.host',
                'type'    => 'text',
                'default' => config('imap.accounts.default.host'),
            ],
            [
                'name'    => 'port',
                'title'   => 'admin::app.configuration.index.email.imap.account.port',
                'type'    => 'text',
                'default' => config('imap.accounts.default.port'),
            ],
            [
                'name'    => 'encryption',
                'title'   => 'admin::app.configuration.index.email.imap.account.encryption',
                'type'    => 'text',
                'default' => config('imap.accounts.default.encryption'),
            ],
            [
                'name'    => 'validate_cert',
                'title'   => 'admin::app.configuration.index.email.imap.account.validate-cert',
                'type'    => 'boolean',
                'default' => config('imap.accounts.default.validate_cert'),
            ],
            [
                'name'    => 'username',
                'title'   => 'admin::app.configuration.index.email.imap.account.username',
                'type'    => 'text',
                'default' => config('imap.accounts.default.username'),
            ],
            [
                'name'    => 'password',
                'title'   => 'admin::app.configuration.index.email.imap.account.password',
                'type'    => 'password',
                'default' => config('imap.accounts.default.password'),
            ],
        ],
    ], [
        'key'  => 'general.whatsapp',
        'name' => 'admin::app.configuration.index.whatsapp.title',
        'info' => 'admin::app.configuration.index.whatsapp.info',
        'icon' => 'icon-setting',
        'sort' => 4,
    ], [
        'key'    => 'general.whatsapp.evolution_api',
        'name'   => 'admin::app.configuration.index.whatsapp.evolution-api.title',
        'info'   => 'admin::app.configuration.index.whatsapp.evolution-api.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'enable',
                'title'         => 'admin::app.configuration.index.whatsapp.evolution-api.enable',
                'type'          => 'boolean',
                'default'       => true,
            ], [
                'name'          => 'base_url',
                'title'         => 'admin::app.configuration.index.whatsapp.evolution-api.base-url',
                'type'          => 'text',
                'default'       => config('whatsapp.evolution_base_url'),
                'validation'    => 'required_if:enable,1|url',
                'depends'       => 'enable:1',
                'info'          => 'admin::app.configuration.index.whatsapp.evolution-api.base-url-info',
            ], [
                'name'          => 'token',
                'title'         => 'admin::app.configuration.index.whatsapp.evolution-api.token',
                'type'          => 'password',
                'default'       => config('whatsapp.evolution_token'),
                'validation'    => 'required_if:enable,1',
                'depends'       => 'enable:1',
                'info'          => 'admin::app.configuration.index.whatsapp.evolution-api.token-info',
            ], [
                'name'          => 'instance_name',
                'title'         => 'admin::app.configuration.index.whatsapp.evolution-api.instance-name',
                'type'          => 'text',
                'default'       => config('whatsapp.instance_name'),
                'validation'    => 'required_if:enable,1',
                'depends'       => 'enable:1',
                'info'          => 'admin::app.configuration.index.whatsapp.evolution-api.instance-name-info',
            ],
        ],
    ], [
        'key'    => 'general.whatsapp.ai_agents',
        'name'   => 'admin::app.configuration.index.whatsapp.ai-agents.title',
        'info'   => 'admin::app.configuration.index.whatsapp.ai-agents.info',
        'sort'   => 2,
        'fields' => [
            [
                'name'          => 'openai_api_key',
                'title'         => 'admin::app.configuration.index.whatsapp.ai-agents.openai-api-key',
                'type'          => 'password',
                'default'       => config('whatsapp.openai_api_key'),
                'info'          => 'admin::app.configuration.index.whatsapp.ai-agents.openai-api-key-info',
            ], [
                'name'          => 'openai_model',
                'title'         => 'admin::app.configuration.index.whatsapp.ai-agents.openai-model',
                'type'          => 'select',
                'default'       => config('whatsapp.openai_model'),
                'options'       => [
                    [
                        'title' => 'GPT-3.5 Turbo',
                        'value' => 'gpt-3.5-turbo',
                    ], [
                        'title' => 'GPT-4',
                        'value' => 'gpt-4',
                    ], [
                        'title' => 'GPT-4 Turbo',
                        'value' => 'gpt-4-turbo',
                    ], [
                        'title' => 'GPT-4o',
                        'value' => 'gpt-4o',
                    ], [
                        'title' => 'GPT-4o Mini',
                        'value' => 'gpt-4o-mini',
                    ],
                ],
            ], [
                'name'          => 'elevenlabs_api_key',
                'title'         => 'admin::app.configuration.index.whatsapp.ai-agents.elevenlabs-api-key',
                'type'          => 'password',
                'default'       => config('whatsapp.elevenlabs_api_key'),
                'info'          => 'admin::app.configuration.index.whatsapp.ai-agents.elevenlabs-api-key-info',
            ],
        ],
    ], [
        'key'    => 'general.whatsapp.message_settings',
        'name'   => 'admin::app.configuration.index.whatsapp.message-settings.title',
        'info'   => 'admin::app.configuration.index.whatsapp.message-settings.info',
        'sort'   => 3,
        'fields' => [
            [
                'name'          => 'message_delay',
                'title'         => 'admin::app.configuration.index.whatsapp.message-settings.message-delay',
                'type'          => 'number',
                'default'       => config('whatsapp.message_delay'),
                'validation'    => 'numeric|min:1|max:300',
                'info'          => 'admin::app.configuration.index.whatsapp.message-settings.message-delay-info',
            ], [
                'name'          => 'followup_interval_hours',
                'title'         => 'admin::app.configuration.index.whatsapp.message-settings.followup-interval',
                'type'          => 'number',
                'default'       => config('whatsapp.followup_interval_hours'),
                'validation'    => 'numeric|min:1|max:168',
                'info'          => 'admin::app.configuration.index.whatsapp.message-settings.followup-interval-info',
            ], [
                'name'          => 'max_followup_attempts',
                'title'         => 'admin::app.configuration.index.whatsapp.message-settings.max-followup-attempts',
                'type'          => 'number',
                'default'       => config('whatsapp.max_followup_attempts'),
                'validation'    => 'numeric|min:1|max:10',
                'info'          => 'admin::app.configuration.index.whatsapp.message-settings.max-followup-attempts-info',
            ],
        ],
    ], [
        'key'    => 'general.whatsapp.business_hours',
        'name'   => 'admin::app.configuration.index.whatsapp.business-hours.title',
        'info'   => 'admin::app.configuration.index.whatsapp.business-hours.info',
        'sort'   => 4,
        'fields' => [
            [
                'name'          => 'start_hour',
                'title'         => 'admin::app.configuration.index.whatsapp.business-hours.start-hour',
                'type'          => 'number',
                'default'       => config('whatsapp.business_hours.start'),
                'validation'    => 'numeric|min:0|max:23',
                'info'          => 'admin::app.configuration.index.whatsapp.business-hours.start-hour-info',
            ], [
                'name'          => 'end_hour',
                'title'         => 'admin::app.configuration.index.whatsapp.business-hours.end-hour',
                'type'          => 'number',
                'default'       => config('whatsapp.business_hours.end'),
                'validation'    => 'numeric|min:0|max:23',
                'info'          => 'admin::app.configuration.index.whatsapp.business-hours.end-hour-info',
            ],
        ],
    ],
];
