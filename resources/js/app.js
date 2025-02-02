import './bootstrap';
import '../css/app.css';
import $ from 'jquery';
import Alpine from 'alpinejs';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import itLocale from '@fullcalendar/core/locales/it';

window.$ = window.jQuery = $;
window.Alpine = Alpine;

// Definisci la funzione globale generateGPT
window.generateGPT = function(pubId, note) {
    $.ajax({
        url: `/pubblicazioni/${pubId}/generate-gpt`,
        method: "POST",
        data: {
            note: note,
            // Lettura corretta del token CSRF
            _token: document.querySelector("meta[name='csrf-token']").getAttribute("content")
        },
        success: (response) => {
            if (response.generatedText) {
                document.getElementById("gpt-output").textContent = response.generatedText;
            } else {
                document.getElementById("gpt-output").textContent = "Nessun testo generato.";
            }
        },
        error: () => {
            document.getElementById("gpt-output").textContent = "Errore nella generazione del testo.";
        }
    });
};

Alpine.start();

// Array globale per mantenere i file selezionati
let selectedFiles = [];

// Funzione globale per caricare i file da Nextcloud
function loadFiles(path = '/') {
    const modal = document.getElementById('modal');
    const modalContent = document.getElementById('modal-content');

    $.ajax({
        url: '/files', 
        type: 'GET',
        data: { path },
        success: function (response) {
            if (modalContent && modal) {
                modalContent.innerHTML = response;
                modal.classList.remove('hidden');
                reapplySelection(); 

                // Subito dopo aver iniettato la partial:
                initFileUploadListeners(path);
            } else {
                console.error('Elemento del modale non trovato.');
            }
        },
        error: function () {
            if (modalContent) {
                modalContent.innerHTML = '<p class="text-red-500">Errore durante il caricamento dei file.</p>';
            }
        },
    });
}

// Funzione globale per riapplicare la selezione
function reapplySelection() {
    const modal = document.getElementById('modal');
    const modalContent = document.getElementById('modal-content');
    if (!modalContent) return;

    const allFiles = modalContent.querySelectorAll('.select-file');
    allFiles.forEach(el => {
        const filePath = el.getAttribute('data-path');
        const liElement = el.closest('li');
        if (selectedFiles.includes(filePath)) {
            liElement.classList.add('selected-file');
        } else {
            liElement.classList.remove('selected-file');
        }
    });
}

function initNextcloudButtonForDetails() {
    const browseNextcloudButton = document.querySelector('#publication-details-container #browse-nextcloud');
    if (browseNextcloudButton) {
        browseNextcloudButton.addEventListener('click', function () {
            loadFiles();
        });
    } else {
        console.log('browse-nextcloud non trovato nella vista dettagli');
    }
}

/** Riassegna i listener per l’upload file + bottone Carica */
function initFileUploadListeners(currentPath) {
    const uploadButton = document.getElementById('uploadFileButton');
    const fileInput = document.getElementById('fileToUpload');

    if (uploadButton && fileInput) {
        uploadButton.addEventListener('click', function() {
            const files = fileInput.files;
            if (!files || files.length === 0) {
                alert('Seleziona almeno un file da caricare');
                return;
            }

            let formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            // Carichiamo nella cartella `currentPath` (passato da loadFiles)
            formData.append('path', currentPath);
            
            // **Aggiungi il token**
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            fetch('/files/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('File caricati con successo!');
                    loadFiles(currentPath); 
                } else {
                    alert('Errore durante il caricamento: ' + data.message);
                }
            })
            .catch(err => alert('Errore di rete: ' + err));
        });
    }
}

// FUNZIONI DASHBOARD
document.addEventListener('DOMContentLoaded', function () {
    const calendarEvents = window.calendarEvents || [];
    const clientSelect = document.getElementById(window.clientSelectId);
    const pubContainer = document.getElementById(window.pubContainerId);
    const pubDetailsContainer = document.getElementById(window.pubDetailsContainerId);
    const chatContainer = document.getElementById('pubblicazione-chat');
    const newPublicationButton = document.getElementById(window.newPublicationButtonId);
    const buttonsContainer = document.getElementById(window.buttonsContainerId);
    const manageAssetsButton = document.getElementById(window.manageAssetsButtonId);
    const viewMediaButton = document.getElementById(window.viewMediaButtonId);
    const prevSlideButton = document.getElementById(window.prevSlideButtonId);
    const nextSlideButton = document.getElementById(window.nextSlideButtonId);
    let currentIndex = 0;

    let isEditMode = false;
    let editButton, saveEditButton, cancelEditButton, editForm, publicationText, publicationDate, publicationDetailsContainer;

    const calendarEl = document.getElementById('calendar');
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        locale: itLocale,
        initialView: 'dayGridMonth',
        eventSources: [{ events: calendarEvents, id: 'initialEvents' }],
        eventContent: function (arg) {
            let dotEl = document.createElement('div');
            dotEl.classList.add('status-dot', arg.event.classNames[0]);

            let timeText = '';
            if (arg.event.start) {
                timeText = new Date(arg.event.start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            let titleEl = document.createElement('div');
            titleEl.textContent = `${timeText}`;

            return { domNodes: [dotEl, titleEl] };
        },
    });
    calendar.render();
      
    function updateCalendarEvents(newEvents) {
        const formattedEvents = newEvents.map(event => ({
            title: event.testo || 'Senza titolo',
            start: event.data_pubblicazione,
            classNames: ['stato-' + event.stato_id],
            url: event.url || '#',
        }));

        const eventSource = calendar.getEventSourceById('initialEvents');
        if (eventSource) eventSource.remove();

        calendar.addEventSource({ events: formattedEvents, id: 'initialEvents' });
    }

    function updateCarousel() {
        const totalSlides = pubContainer.children.length;
        prevSlideButton.disabled = currentIndex === 0;
        nextSlideButton.disabled = currentIndex >= totalSlides - 1;
        pubContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    function resetCarousel() {
        currentIndex = 0;
        updateCarousel();
    }

    function refreshCarousel(publicationsHtml) {
        $('#pubblicazioni-ul').html(publicationsHtml);
        resetCarousel();
    }

    function resetPublicationDetails() {
        pubDetailsContainer.innerHTML = '';
        chatContainer.classList.add('hidden'); 
    }

    clientSelect.addEventListener('change', function () {
        const clienteId = this.value;
        resetPublicationDetails(); 

        if (clienteId) {
            buttonsContainer.classList.remove('hidden');
            newPublicationButton.classList.remove('hidden');
            newPublicationButton.href = `/pubblicazioni/clienti/${clienteId}/create`;
            if(manageAssetsButton){
                manageAssetsButton.href = `/clienti/${clienteId}/assets`;
            }
            if(viewMediaButton){
                viewMediaButton.href = `/clienti/${clienteId}/media_pubblicazioni`;
            }
        } else {
            buttonsContainer.classList.add('hidden');
            newPublicationButton.classList.add('hidden');
        }

        const url = clienteId ? `/filter-publications/${clienteId}` : `/filter-publications/all`;

        $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
                refreshCarousel(data.pubblicazioniLista);
                updateCalendarEvents(data.pubblicazioniCalendario);
            },
            error: function () {
                console.error('Errore durante il caricamento delle pubblicazioni.');
            },
        });        
    });

    nextSlideButton.addEventListener('click', function () {
        const totalSlides = pubContainer.children.length;
        if (currentIndex < totalSlides - 1) {
            currentIndex++;
            updateCarousel();
        }
    });

    prevSlideButton.addEventListener('click', function () {
        if (currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        }
    });

    window.loadPublicationDetails = function (id) {
        $.ajax({
            url: `/get-publication-details/${id}`,
            method: 'GET',
            success: function (html) {
                // Inseriamo l'HTML nella sezione "dettaglio pubblicazione"
                $('#pubblicazione-dettaglio-contenuto').html(html);
    
                Alpine.nextTick(() => {
                    // Se usi un carosello con x-ref="carouselComponent", lo gestisci come facevi prima
                    const carouselComponent = document.querySelector('[x-ref="carouselComponent"]');
                    if (carouselComponent) {
                        const componentData = Alpine.$data(carouselComponent);
                        selectedFiles = componentData.media.map(m => m.nome);
                    } else {
                        console.log('carouselComponent non trovato dopo il caricamento dei dettagli');
                    }
    
                    chatContainer.classList.remove('hidden'); 
                    loadComments(id); 
                    initNextcloudButtonForDetails();
                    initializeEditMode(id);
                    $('#gpt-output').html('');
                });
    
                // === ECCO LA PARTE IMPORTANTE PER RECUPERARE I DATA-ATTRIBUTE ===
                // Selezioniamo l'elemento "publication-details-container" appena iniettato
                const detailEl = document.querySelector('#pubblicazione-dettaglio-contenuto #publication-details-container');
                if (detailEl) {
                    // Prendiamo gli attributi data
                    const pubId = detailEl.getAttribute('data-pub-id');
                    const pubNote = detailEl.getAttribute('data-pub-note') || '';
    
                    // Dispatch dell'evento con ID e NOTE
                    document.dispatchEvent(
                        new CustomEvent('publication-selected', {
                            detail: {
                                id: pubId,    // l'ID recuperato dai data-attribute
                                note: pubNote // le note recuperate dai data-attribute
                            }
                        })
                    );
                } else {
                    console.warn('publication-details-container non trovato. Non è possibile recuperare ID/note.');
                }
            },
            error: function () {
                console.error('Errore durante il caricamento del dettaglio della pubblicazione.');
            },
        });
    }; 

    function loadComments(pubblicazioneId) {
        $.ajax({
            url: `/pubblicazioni/${pubblicazioneId}/commenti`,
            method: 'GET',
            success: function (data) {
                $('#publication-id').val(pubblicazioneId);
                $('#comment-section').html(data.commentiHtml);
            },
            error: function () {
                console.error('Errore durante il caricamento dei commenti.');
            },
        });
    }

    $('#comment-form').on('submit', function (e) {
        e.preventDefault();
        const pubblicazioneId = $('#publication-id').val(); 
        const commentText = $('#comment-text').val();

        $.ajax({
            url: `/pubblicazioni/${pubblicazioneId}/commenti`,
            method: 'POST',
            data: {
                commento: commentText,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function () {
                $('#comment-text').val('');
                loadComments(pubblicazioneId);
            },
            error: function () {
                console.error('Errore durante l\'invio del commento.');
            },
        });
    });

    function initializeEditMode(publicationId) {
        editButton = document.getElementById('edit-button');
        saveEditButton = document.getElementById('save-edit-button');
        cancelEditButton = document.getElementById('cancel-edit-button');
        editForm = document.getElementById('publication-edit-form');
        publicationText = document.getElementById('publication-text');
        publicationDate = document.getElementById('publication-date');

        if (editButton && saveEditButton && cancelEditButton) {
            editButton.addEventListener('click', function () {
                toggleEditMode(true);
            });

            cancelEditButton.addEventListener('click', function () {
                toggleEditMode(false);
            });

            saveEditButton.addEventListener('click', function () {
                savePublicationChanges(publicationId);
            });
        }
    }

    function toggleEditMode(enable) {
        isEditMode = enable;

        if (isEditMode) {
            publicationText.classList.add('hidden');
            publicationDate.classList.add('hidden');
            editForm.classList.remove('hidden');

            const browseNextcloudButton = editForm.querySelector('#browse-nextcloud');
            if (browseNextcloudButton) {
                browseNextcloudButton.classList.remove('hidden');
            }

            const editContainer = document.getElementById('selected-files-container-edit');
            if (editContainer) {
                editContainer.innerHTML = '';
                selectedFiles.forEach(filePath => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_files[]';
                    input.value = filePath;
                    editContainer.appendChild(input);
                });
            }

            editButton.classList.add('hidden');
            saveEditButton.classList.remove('hidden');
            cancelEditButton.classList.remove('hidden');
        } else {
            publicationText.classList.remove('hidden');
            publicationDate.classList.remove('hidden');
            editForm.classList.add('hidden');

            editButton.classList.remove('hidden');
            saveEditButton.classList.add('hidden');
            cancelEditButton.classList.add('hidden');
        }
    }

    function savePublicationChanges(publicationId) {
        const formData = {
            testo: document.getElementById('publication-edit-text').value,
            data_pubblicazione: document.getElementById('publication-edit-date').value,
            azione: 'invia_al_cliente',
            _method: 'PUT',
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            selected_files: Array.from(
                document.querySelectorAll('#selected-files-container-edit input[name="selected_files[]"]')
            ).map(input => input.value),
        };
    
        const originalDate = document.getElementById('publication-date').textContent.trim();
    
        $.ajax({
            url: `/pubblicazioni/${publicationId}`,
            method: 'POST',
            data: formData,
            success: function (response) {
                alert('Modifiche salvate con successo!');
                toggleEditMode(false);
                const formattedDate = formData.data_pubblicazione.replace('T', ' ');
                document.getElementById('publication-text').textContent = formData.testo;
                document.getElementById('publication-date').textContent = formattedDate;
    
                if (originalDate !== formattedDate) {
                    $.ajax({
                        url: '/filter-publications/all',
                        method: 'GET',
                        success: function (data) {
                            updateCalendarEvents(data.pubblicazioniCalendario);
                            refreshCarousel(data.pubblicazioniLista);
                        },
                        error: function () {
                            console.error('Errore durante l\'aggiornamento del calendario e del carosello.');
                        },
                    });
                }
    
                if (response.stato_id !== 1 && response.stato_id !== 2) {
                    document.getElementById('edit-button').classList.add('hidden');
                }
            },
            error: function () {
                alert('Errore durante il salvataggio delle modifiche.');
            },
        });
    }

    resetCarousel();
    resetPublicationDetails(); 
});

// Gestione click globali e selezione file Nextcloud
document.addEventListener('DOMContentLoaded', function () {
    const browseNextcloudButton = document.getElementById('browse-nextcloud');
    const modal = document.getElementById('modal');
    const modalContent = document.getElementById('modal-content');
    const closeModalButton = document.getElementById('close-modal');
    const confirmSelectionButton = document.getElementById('confirm-selection');

    if (browseNextcloudButton) {
        browseNextcloudButton.addEventListener('click', function () {
            loadFiles(); 
        });
    }

    if (closeModalButton) {
        closeModalButton.addEventListener('click', function () {
            if (modal) {
                modal.classList.add('hidden');
            }
        });
    }

    if (confirmSelectionButton) {
        confirmSelectionButton.addEventListener('click', function () {
    
            const carouselComponent = document.querySelector('[x-ref="carouselComponent"]');
            if (carouselComponent) {
                const componentData = Alpine.$data(carouselComponent);
                componentData.setFiles(selectedFiles);
            } else {
                console.log('carouselComponent non trovato');
            }
    
            let container = document.getElementById('selected-files-container-edit');
            if (!container) {
                container = document.getElementById('selected-files-container');
            }
            
    
            if (container) {
                container.innerHTML = '';
                selectedFiles.forEach(filePath => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_files[]';
                    input.value = filePath;
                    container.appendChild(input);
                });
            }
    
            if (modal) {
                modal.classList.add('hidden');
            }
        });
    }   

    document.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'go-back') {
            const parentPath = event.target.getAttribute('data-path');
            if (parentPath) {
                loadFiles(parentPath);
            }
            return; 
        }

        const selectFileElement = event.target.closest('.select-file');
        if (selectFileElement) {
            const filePath = selectFileElement.getAttribute('data-path');
            const isFolder = selectFileElement.classList.contains('folder');
            const liElement = selectFileElement.closest('li');

            if (isFolder) {
                loadFiles(filePath);
            } else {
                if (selectedFiles.includes(filePath)) {
                    selectedFiles = selectedFiles.filter(f => f !== filePath);
                    liElement.classList.remove('selected-file');
                } else {
                    selectedFiles.push(filePath);
                    liElement.classList.add('selected-file');
                }
            }
        }
    });
});
