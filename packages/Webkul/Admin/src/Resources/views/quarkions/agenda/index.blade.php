<x-admin::layouts>
    <x-slot:title>
        Agenda - Quarkions IA
    </x-slot>

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

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
            <!-- Buttons moved to Vue component -->
        </div>
    </div>

    <!-- Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <quarkions-agenda-calendar 
                ref="agendaCalendar"
                @sync-google="syncWithGoogle"
                @open-create-modal="openCreateModal">
            </quarkions-agenda-calendar>
        </div>
    </div>

    @pushOnce('scripts')
        <!-- FullCalendar JS -->
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
        
        <script type="text/x-template" id="quarkions-agenda-calendar-template">
            <div class="calendar-container">
                <!-- Header Actions -->
                <div class="mb-4 flex items-center justify-end gap-x-2.5">
                    <button
                        @click="syncWithGoogle"
                        class="secondary-button"
                        :disabled="syncing"
                    >
                        <span v-if="syncing">Sincronizando...</span>
                        <span v-else>Sincronizar Google</span>
                    </button>
                    
                    <button
                        @click="openCreateModal"
                        class="primary-button"
                    >
                        Novo Agendamento
                    </button>
                </div>
            
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <!-- Calendar Toolbar -->
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button @click="calendar.prev()" class="secondary-button">
                                ‹ Anterior
                            </button>
                            <button @click="calendar.next()" class="secondary-button">
                                Próximo ›
                            </button>
                            <button @click="calendar.today()" class="secondary-button">
                                Hoje
                            </button>
                        </div>
                        
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                            @{{ calendarTitle }}
                        </h2>
                        
                        <div class="flex items-center gap-2">
                            <button @click="changeView('dayGridMonth')" 
                                    :class="currentView === 'dayGridMonth' ? 'primary-button' : 'secondary-button'">
                                Mês
                            </button>
                            <button @click="changeView('timeGridWeek')" 
                                    :class="currentView === 'timeGridWeek' ? 'primary-button' : 'secondary-button'">
                                Semana
                            </button>
                            <button @click="changeView('timeGridDay')" 
                                    :class="currentView === 'timeGridDay' ? 'primary-button' : 'secondary-button'">
                                Dia
                            </button>
                        </div>
                    </div>
                    
                    <!-- Calendar -->
                    <div id="calendar" class="min-h-[600px]"></div>
                </div>
                
                <!-- Modal de Criação/Edição -->
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
                    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-600">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                            <span v-if="$editingEvent">Editar Agendamento</span>
                            <span v-else>Novo Agendamento</span>
                        </h3>
                        
                        <form @submit.prevent="saveEvent">
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Título
                                </label>
                                <input v-model="eventForm.title" type="text" required
                                       class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                            </div>
                            
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Data
                                </label>
                                <input v-model="eventForm.date" type="date" required
                                       class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                            </div>
                            
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Horário
                                </label>
                                <input v-model="eventForm.time" type="time" required
                                       class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                            </div>
                            
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Status
                                </label>
                                <select v-model="eventForm.status" required
                                        class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                                    <option value="agendado">Agendado</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="realizado">Realizado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Observações
                                </label>
                                <textarea v-model="eventForm.observacoes" rows="3"
                                          class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"></textarea>
                            </div>
                            
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input v-model="eventForm.sync_with_google" type="checkbox" class="mr-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        Sincronizar com Google Calendar
                                    </span>
                                </label>
                            </div>
                            
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="closeModal" class="secondary-button">
                                    Cancelar
                                </button>
                                <button type="submit" class="primary-button" :disabled="saving">
                                    <span v-if="saving">Salvando...</span>
                                    <span v-else>Salvar</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            // Aguardar que o Vue app esteja pronto
            function registerComponent() {
                if (typeof app !== 'undefined' && app.component) {
                        app.component('quarkions-agenda-calendar', {
                            template: '#quarkions-agenda-calendar-template',
                            
                            data() {
                    return {
                        calendar: null,
                        calendarTitle: '',
                        currentView: 'dayGridMonth',
                        showModal: false,
                        editingEvent: null,
                        saving: false,
                        syncing: false,
                        eventForm: {
                            title: '',
                            date: '',
                            time: '',
                            status: 'agendado',
                            observacoes: '',
                            sync_with_google: false
                        }
                    };
                },

                mounted() {
                    this.initCalendar();
                },

                methods: {
                    initCalendar() {
                        const calendarEl = document.getElementById('calendar');
                        
                        this.calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            locale: 'pt-br',
                            headerToolbar: false, // Usamos nosso próprio toolbar
                            height: 'auto',
                            editable: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            dayMaxEvents: true,
                            weekends: true,
                            
                            // Eventos
                            events: {
                                url: "{{ route('admin.quarkions.agenda.events') }}",
                                failure: (error) => {
                                    console.error('Erro ao carregar eventos:', error);
                                }
                            },
                            
                            // Callbacks
                            select: (info) => {
                                this.openCreateModal(info.startStr);
                            },
                            
                            eventClick: (info) => {
                                this.editEvent(info.event);
                            },
                            
                            eventDrop: (info) => {
                                this.updateEventDate(info.event);
                            },
                            
                            eventResize: (info) => {
                                this.updateEventDate(info.event);
                            },
                            
                            datesSet: (info) => {
                                this.calendarTitle = info.view.title;
                                this.currentView = info.view.type;
                            },
                            
                            // Estilização dos eventos
                            eventClassNames: (info) => {
                                const status = info.event.extendedProps.status;
                                return [`fc-event-${status}`];
                            }
                        });
                        
                        this.calendar.render();
                        this.calendarTitle = this.calendar.view.title;
                    },
                    
                    changeView(viewName) {
                        this.calendar.changeView(viewName);
                        this.currentView = viewName;
                    },
                    
                    openCreateModal(date = null) {
                        this.editingEvent = null;
                        this.eventForm = {
                            title: '',
                            date: date || new Date().toISOString().split('T')[0],
                            time: '09:00',
                            status: 'agendado',
                            observacoes: '',
                            sync_with_google: false
                        };
                        this.showModal = true;
                    },
                    
                    editEvent(event) {
                        this.editingEvent = event;
                        const startDate = new Date(event.start);
                        
                        this.eventForm = {
                            title: event.title,
                            date: startDate.toISOString().split('T')[0],
                            time: startDate.toTimeString().slice(0, 5),
                            status: event.extendedProps.status || 'agendado',
                            observacoes: event.extendedProps.observacoes || '',
                            sync_with_google: event.extendedProps.sync_with_google || false
                        };
                        this.showModal = true;
                    },
                    
                    closeModal() {
                        this.showModal = false;
                        this.editingEvent = null;
                    },
                    
                    saveEvent() {
                        this.saving = true;
                        
                        const data = {
                            titulo: this.eventForm.title,
                            data: this.eventForm.date,
                            horario: this.eventForm.time,
                            status: this.eventForm.status,
                            observacoes: this.eventForm.observacoes,
                            sync_with_google: this.eventForm.sync_with_google
                        };
                        
                        const url = this.editingEvent 
                            ? `{{ route('admin.quarkions.agenda.index') }}/${this.editingEvent.id}`
                            : "{{ route('admin.quarkions.agenda.store') }}";
                            
                        const method = this.editingEvent ? 'PUT' : 'POST';
                        
                        this.$http({
                            method: method,
                            url: url,
                            data: data
                        })
                        .then(response => {
                            this.calendar.refetchEvents();
                            this.closeModal();
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                        })
                        .catch(error => {
                            console.error('Erro ao salvar evento:', error);
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Erro ao salvar agendamento' });
                        })
                        .finally(() => {
                            this.saving = false;
                        });
                    },
                    
                    updateEventDate(event) {
                        const data = {
                            titulo: event.title,
                            data: event.start.toISOString().split('T')[0],
                            horario: event.start.toTimeString().slice(0, 5),
                            status: event.extendedProps.status,
                            observacoes: event.extendedProps.observacoes,
                            sync_with_google: event.extendedProps.sync_with_google
                        };
                        
                        this.$http.put(`{{ route('admin.quarkions.agenda.index') }}/${event.id}`, data)
                            .then(response => {
                                this.$emitter.emit('add-flash', { type: 'success', message: 'Agendamento atualizado!' });
                            })
                            .catch(error => {
                                console.error('Erro ao atualizar evento:', error);
                                this.calendar.refetchEvents(); // Reverter mudança
                                this.$emitter.emit('add-flash', { type: 'error', message: 'Erro ao atualizar agendamento' });
                            });
                    },
                    
                    syncWithGoogle() {
                        this.syncing = true;
                        
                        this.$http.post("{{ route('admin.quarkions.agenda.sync-google') }}")
                            .then(response => {
                                this.calendar.refetchEvents();
                                this.$emitter.emit('add-flash', { 
                                    type: 'success', 
                                    message: `Sincronização concluída! ${response.data.synced} eventos sincronizados.` 
                                });
                            })
                            .catch(error => {
                                console.error('Erro na sincronização:', error);
                                this.$emitter.emit('add-flash', { type: 'error', message: 'Erro na sincronização com Google Calendar' });
                            })
                            .finally(() => {
                                this.syncing = false;
                            });
                    }
                    }
                }
                });
                    } else {
                        // Retry após 100ms se o app ainda não estiver disponível
                        setTimeout(registerComponent, 100);
                    }
                }
                
                registerComponent();
            });
        </script>
        
        <style>
            /* Estilos personalizados para os eventos */
            .fc-event-agendado {
                background-color: #3b82f6 !important;
                border-color: #2563eb !important;
            }
            
            .fc-event-confirmado {
                background-color: #10b981 !important;
                border-color: #059669 !important;
            }
            
            .fc-event-realizado {
                background-color: #6b7280 !important;
                border-color: #4b5563 !important;
            }
            
            .fc-event-cancelado {
                background-color: #ef4444 !important;
                border-color: #dc2626 !important;
            }
            
            /* Tema escuro */
            .dark .fc-theme-standard td,
            .dark .fc-theme-standard th {
                border-color: #374151;
            }
            
            .dark .fc-theme-standard .fc-scrollgrid {
                border-color: #374151;
            }
            
            .dark .fc-col-header-cell {
                background-color: #1f2937;
                color: #f9fafb;
            }
            
            .dark .fc-daygrid-day {
                background-color: #111827;
                color: #f9fafb;
            }
            
            .dark .fc-day-today {
                background-color: #1e40af !important;
            }
        </style>
    @endPushOnce
</x-admin::layouts>

