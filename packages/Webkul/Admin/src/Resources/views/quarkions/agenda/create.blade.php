<x-admin::layouts>
    <x-slot:title>
        Novo Agendamento - Quarkions IA
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex flex-col gap-2">
            <div class="flex cursor-pointer items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Novo Agendamento
                </h1>
            </div>

            <p class="text-base text-gray-600 dark:text-gray-300">
                Criar um novo agendamento
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.quarkions.agenda.index') }}"
                class="transparent-button"
            >
                @lang('admin::app.layouts.back')
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <quarkions-agenda-create></quarkions-agenda-create>
        </div>
    </div>

@push('scripts')
    <script type="text/x-template" id="quarkions-agenda-create-template">
        <form 
            method="POST" 
            action="{{ route('admin.quarkions.agenda.store') }}"
            @submit.prevent="onSubmit"
        >
            @csrf
            
            <div class="panel">
                <div class="panel-header">
                    <span class="title">Informações do Agendamento</span>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="lead_id" class="required">Lead</label>
                        <select 
                            id="lead_id" 
                            name="lead_id" 
                            class="control" 
                            v-model="agendamento.lead_id"
                            required
                        >
                            <option value="">Selecione um lead</option>
                            <option 
                                v-for="lead in leads" 
                                :key="lead.id" 
                                :value="lead.id"
                            >
                                @{{ lead.nome }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="data" class="required">Data</label>
                        <input 
                            type="date" 
                            id="data" 
                            name="data" 
                            class="control" 
                            v-model="agendamento.data"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="horario" class="required">Horário</label>
                        <input 
                            type="time" 
                            id="horario" 
                            name="horario" 
                            class="control" 
                            v-model="agendamento.horario"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="status" class="required">Status</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="control" 
                            v-model="agendamento.status"
                            required
                        >
                            <option value="agendado">Agendado</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="concluido">Concluído</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="observacoes">Observações</label>
                        <textarea 
                            id="observacoes" 
                            name="observacoes" 
                            class="control" 
                            v-model="agendamento.observacoes"
                            rows="4"
                        ></textarea>
                    </div>
                </div>

                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Salvar Agendamento
                    </button>
                </div>
            </div>
        </form>
    </script>

    <script type="module">
        app.component('quarkions-agenda-create', {
            template: '#quarkions-agenda-create-template',
            
            data() {
                return {
                    agendamento: {
                        lead_id: '',
                        data: '',
                        horario: '',
                        status: 'agendado',
                        observacoes: ''
                    },
                    leads: [],
                    loading: false
                };
            },

            mounted() {
                this.loadLeads();
            },

            methods: {
                loadLeads() {
                    // Aqui você pode carregar os leads via API
                    // Por enquanto, dados fictícios
                    this.leads = [
                        { id: 1, nome: 'João Silva' },
                        { id: 2, nome: 'Maria Santos' },
                        { id: 3, nome: 'Pedro Costa' }
                    ];
                },

                onSubmit() {
                    this.loading = true;
                    
                    this.$http.post("{{ route('admin.quarkions.agenda.store') }}", this.agendamento)
                        .then(response => {
                            this.$emitter.emit('add-flash', { type: 'success', message: 'Agendamento criado com sucesso!' });
                            window.location.href = "{{ route('admin.quarkions.agenda.index') }}";
                        })
                        .catch(error => {
                            console.error('Erro ao criar agendamento:', error);
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Erro ao criar agendamento!' });
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                }
            }
        });
    </script>
@endpush

</x-admin::layouts>
