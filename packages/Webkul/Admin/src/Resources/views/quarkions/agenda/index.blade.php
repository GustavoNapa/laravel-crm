<x-admin::layouts>
    <x-slot:title>
        Agenda - Quarkions IA
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex flex-col gap-2">
            <div class="flex cursor-pointer items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Agenda
                </h1>
            </div>

            <p class="text-base text-gray-600 dark:text-gray-300">
                Gerencie seus agendamentos e compromissos
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.quarkions.agenda.create') }}"
                class="primary-button"
            >
                @lang('admin::app.layouts.add')
                Agendamento
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <quarkions-agenda-index></quarkions-agenda-index>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="text/x-template" id="quarkions-agenda-index-template">
            <div class="table-container">
                <div class="shimmer">
                    <div class="table-responsive grid-container">
                        <datagrid-plus src="{{ route('admin.quarkions.agenda.index') }}"></datagrid-plus>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('quarkions-agenda-index', {
                template: '#quarkions-agenda-index-template',
                
                data() {
                    return {
                        agendamentos: [],
                        loading: false
                    };
                },

                mounted() {
                    this.loadAgendamentos();
                },

                methods: {
                    loadAgendamentos() {
                        this.loading = true;
                        
                        this.$http.get("{{ route('admin.quarkions.agenda.index') }}")
                            .then(response => {
                                this.agendamentos = response.data.data || [];
                            })
                            .catch(error => {
                                console.error('Erro ao carregar agendamentos:', error);
                            })
                            .finally(() => {
                                this.loading = false;
                            });
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
